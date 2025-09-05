<script setup>
import { useResidentStore } from '@/Stores'
import { ref, onBeforeUnmount, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import useToast from '@/Utils/useToast'
// ✅ Import WebcamCapture component
import WebcamCapture from '@/Components/WebcamCapture.vue'

const router = useRouter()
const route = useRoute()
const { showToast } = useToast()
const residentStore = useResidentStore()

// ✅ Reference to WebcamCapture component
const webcamRef = ref(null)

// Current active tab
const activeTab = ref('basic')

const residentDataForm = ref({
    // Basic Information
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    birthday: '',
    age: '',
    gender: '',
    avatar: null,

    // Address Information
    house_no: '',
    street: '',
    barangay: '',
    city: '',
    province: '',
    zipcode: '',

    // Contact Information
    contact_number: '',
    contact_person: '',
    family_member: '',
    emergency_contact: '',
    email: '',

    // Demographic & Residency Details
    place_of_birth: '',
    civil_status: 'Single',
    citizenship: 'Filipino',
    religion: '',
    years_of_residency: 0,
    voter_status: 'Not Registered',
    voter_precinct_number: '',

    // Government & ID References
    valid_id_type: '',
    valid_id_number: '',
    sss_number: '',
    philhealth_number: '',
    tin_number: '',
    pagibig_number: '',

    // Employment & Education
    occupation: 'N/A',
    educational_attainment: 'High School Graduate',
    monthly_income: 0,
    employer_name: '',
    employer_address: '',

    // Barangay-Specific Flags
    is_pwd: false,
    pwd_id_number: '',
    disability_type: '',
    is_senior_citizen: false,
    senior_citizen_id_number: '',
    is_4ps_beneficiary: false,
    '4ps_household_id': '',
    is_registered_voter: false,
    is_solo_parent: false,
    solo_parent_id_number: '',
    is_indigenous: false,
    indigenous_group: '',

    // Additional Barangay Flags
    is_ofw: false,
    ofw_country: '',
    is_teen_parent: false,
    is_lactating_mother: false,
    is_pregnant: false,

    // Status and Notes
    resident_status: 'Active',
    medical_conditions: '',
    allergies: '',
    notes: '',
    date_registered: new Date().toISOString().split('T')[0],
    registered_by: '',
})

const formErrors = ref({})

// Computed property for full address
const fullAddress = computed(() => {
    const parts = [
        residentDataForm.value.house_no,
        residentDataForm.value.street,
        residentDataForm.value.barangay,
        residentDataForm.value.city,
        residentDataForm.value.province,
        residentDataForm.value.zipcode
    ].filter(part => part && part.trim())

    return parts.join(', ')
})

const isDuplicateResident = () => {
    const allResidents = residentStore.residents || []
    return allResidents.some(r => {
        const first = (r.first_name || '').trim().toLowerCase()
        const middle = (r.middle_name || '').trim().toLowerCase()
        const last = (r.last_name || '').trim().toLowerCase()
        return (
            first === residentDataForm.value.first_name.trim().toLowerCase() &&
            middle === residentDataForm.value.middle_name.trim().toLowerCase() &&
            last === residentDataForm.value.last_name.trim().toLowerCase()
        )
    })
}

const restrictPhoneInput = (field) => {
    let value = residentDataForm.value[field]
    value = value.replace(/\D/g, '')
    if (value.length > 0 && !value.startsWith('09')) {
        value = '09' + value.replace(/^0+/, '').replace(/^9?/, '')
    }
    if (value.length > 11) value = value.slice(0, 11)
    residentDataForm.value[field] = value
}

const calculateAge = () => {
    if (residentDataForm.value.birthday) {
        const today = new Date()
        const birthDate = new Date(residentDataForm.value.birthday)
        let age = today.getFullYear() - birthDate.getFullYear()
        const monthDiff = today.getMonth() - birthDate.getMonth()

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--
        }

        residentDataForm.value.age = age

        // Auto-check senior citizen if 60 or older
        residentDataForm.value.is_senior_citizen = age >= 60
    }
}

const validateForm = () => {
    formErrors.value = {}
    let isValid = true

    const requiredFields = {
        first_name: 'First Name',
        last_name: 'Last Name',
        birthday: 'Birthday',
        age: 'Age',
        gender: 'Gender',
    }

    for (const [field, label] of Object.entries(requiredFields)) {
        if (!residentDataForm.value[field] || residentDataForm.value[field].toString().trim() === '') {
            formErrors.value[field] = `${label} is required.`
            isValid = false
        }
    }

    const contact = residentDataForm.value.contact_number
    if (contact && contact.trim() !== '' && !/^09\d{9}$/.test(contact)) {
        formErrors.value.contact_number = 'Contact Number must start with 09 and be exactly 11 digits.'
        isValid = false
    }

    const emergencyContact = residentDataForm.value.emergency_contact
    if (emergencyContact && emergencyContact.trim() !== '' && !/^09\d{9}$/.test(emergencyContact)) {
        formErrors.value.emergency_contact = 'Emergency Contact must start with 09 and be exactly 11 digits.'
        isValid = false
    }

    const email = residentDataForm.value.email
    if (email && email.trim() !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        formErrors.value.email = 'Please enter a valid email address.'
        isValid = false
    }

    return isValid
}

const createResident = async () => {
    if (!validateForm()) {
        showToast({ icon: 'error', title: 'Please fill in all required fields.' })
        return
    }
    if (isDuplicateResident()) {
        showToast({ icon: 'error', title: 'Resident with the same full name already exists.' })
        return
    }

    console.log('Form data being sent:', residentDataForm.value)
    console.log('Avatar value:', residentDataForm.value.avatar)

    try {
        // ✅ Stop webcam before making API call
        if (webcamRef.value) {
            webcamRef.value.stopWebcam();
        }

        await residentStore.addResident(residentDataForm.value)
        showToast({ icon: 'success', title: 'Resident created successfully' })
        router.push('/residents')
    } catch (error) {
        console.error('Create resident error:', error)
        if (error.response && error.response.status === 422 && error.response.data.errors) {
            const errors = error.response.data.errors
            formErrors.value = {}
            for (const [field, messages] of Object.entries(errors)) {
                formErrors.value[field] = messages.join(' ')
            }
            const message = Object.values(errors).flat().join(' ')
            showToast({ icon: 'error', title: message })
        } else {
            showToast({ icon: 'error', title: error.message || 'Failed to create resident.' })
        }
    }
}

// ✅ Handle image upload from WebcamCapture
const handleImageUploaded = (imageUrl) => {
    console.log('Image uploaded:', imageUrl)
    console.log('Image URL type:', typeof imageUrl)
    console.log('Is valid URL?', /^https?:\/\//.test(imageUrl))
    console.log('Is data URL?', /^data:image\//.test(imageUrl))

    residentDataForm.value.avatar = imageUrl
    showToast({ icon: 'success', title: 'Profile photo captured! Remember to save to complete registration.' })
}

// ✅ Handle navigation cleanup
const handleCancel = () => {
    if (webcamRef.value) {
        webcamRef.value.stopWebcam();
    }
    router.push('/residents');
};

// Tab navigation
const tabs = [
    { key: 'basic', label: 'Basic Info', icon: '👤' },
    { key: 'address', label: 'Address', icon: '🏠' },
    { key: 'demographic', label: 'Demographics', icon: '📊' },
    { key: 'employment', label: 'Employment', icon: '💼' },
    { key: 'government', label: 'Government IDs', icon: '🆔' },
    { key: 'special', label: 'Special Categories', icon: '⭐' },
    { key: 'additional', label: 'Additional Info', icon: '📝' }
]

const setActiveTab = (tabKey) => {
    activeTab.value = tabKey
}

onBeforeUnmount(() => {
    console.log('AddResident unmounting, cleaning up webcam...')
    if (webcamRef.value) {
        webcamRef.value.stopWebcam();
    }
    residentStore.clearState()
})
</script>

<template>
    <div :key="route.fullPath" class="min-h-screen bg-gray-100 p-2 sm:p-4 md:p-6">
        <form @submit.prevent="createResident" class="max-w-7xl mx-auto">
            <div class="bg-white rounded-lg sm:rounded-2xl shadow-xl overflow-hidden">

                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4 sm:p-6">
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white text-center">
                        Add New Resident
                    </h1>
                    <p class="text-blue-100 text-center mt-2">Complete resident registration form</p>
                </div>

                <!-- Avatar Section -->
                <div class="bg-gradient-to-b from-blue-50 to-white p-4 sm:p-6 border-b border-gray-200">
                    <div class="max-w-md mx-auto">
                        <h2 class="text-lg font-semibold text-center mb-4">Profile Photo</h2>
                        <WebcamCapture ref="webcamRef" @image-uploaded="handleImageUploaded" />
                        <input type="hidden" v-model="residentDataForm.avatar" />
                        <p v-if="formErrors.avatar" class="text-red-500 text-xs mt-1 text-center">{{ formErrors.avatar
                            }}</p>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="flex overflow-x-auto">
                        <button v-for="tab in tabs" :key="tab.key" type="button" @click="setActiveTab(tab.key)" :class="[
                            'flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors',
                            activeTab === tab.key
                                ? 'border-blue-500 text-blue-600 bg-blue-50'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]">
                            <span class="text-lg">{{ tab.icon }}</span>
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Form Content -->
                <div class="p-4 sm:p-6 md:p-8">

                    <!-- Basic Information Tab -->
                    <div v-show="activeTab === 'basic'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- First Name -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">First Name *</label>
                                <input type="text" placeholder="First Name" v-model="residentDataForm.first_name"
                                    :class="{ 'border-red-500': formErrors.first_name }"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.first_name" class="text-red-500 text-xs mt-1">{{
                                    formErrors.first_name }}</p>
                            </div>

                            <!-- Middle Name -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Middle Name</label>
                                <input type="text" placeholder="Middle Name" v-model="residentDataForm.middle_name"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Last Name *</label>
                                <input type="text" placeholder="Last Name" v-model="residentDataForm.last_name"
                                    :class="{ 'border-red-500': formErrors.last_name }"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.last_name" class="text-red-500 text-xs mt-1">{{ formErrors.last_name
                                    }}</p>
                            </div>

                            <!-- Suffix -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Suffix</label>
                                <select v-model="residentDataForm.suffix"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                    <option value="">Select Suffix</option>
                                    <option value="Jr">Jr</option>
                                    <option value="Sr">Sr</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                    <option value="V">V</option>
                                </select>
                            </div>

                            <!-- Birthday -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Birthday *</label>
                                <input type="date" v-model="residentDataForm.birthday" @change="calculateAge"
                                    :class="{ 'border-red-500': formErrors.birthday }"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.birthday" class="text-red-500 text-xs mt-1">{{ formErrors.birthday
                                    }}</p>
                            </div>

                            <!-- Age -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Age *</label>
                                <input type="number" placeholder="Age" v-model="residentDataForm.age"
                                    :class="{ 'border-red-500': formErrors.age }"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                    readonly />
                                <p v-if="formErrors.age" class="text-red-500 text-xs mt-1">{{ formErrors.age }}</p>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Gender *</label>
                                <select v-model="residentDataForm.gender"
                                    :class="{ 'border-red-500': formErrors.gender }"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <p v-if="formErrors.gender" class="text-red-500 text-xs mt-1">{{ formErrors.gender }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                                <input type="email" placeholder="Email" v-model="residentDataForm.email"
                                    :class="{ 'border-red-500': formErrors.email }"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.email" class="text-red-500 text-xs mt-1">{{ formErrors.email }}</p>
                            </div>

                            <!-- Contact Number -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Contact Number</label>
                                <input type="text" placeholder="Contact No." v-model="residentDataForm.contact_number"
                                    @input="restrictPhoneInput('contact_number')"
                                    :class="{ 'border-red-500': formErrors.contact_number }"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.contact_number" class="text-red-500 text-xs mt-1">{{
                                    formErrors.contact_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information Tab -->
                    <div v-show="activeTab === 'address'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">House No.</label>
                                <input type="text" placeholder="House No." v-model="residentDataForm.house_no"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Street</label>
                                <input type="text" placeholder="Street" v-model="residentDataForm.street"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Barangay</label>
                                <input type="text" placeholder="Barangay" v-model="residentDataForm.barangay"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">City</label>
                                <input type="text" placeholder="City" v-model="residentDataForm.city"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Province</label>
                                <input type="text" placeholder="Province" v-model="residentDataForm.province"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Zip Code</label>
                                <input type="text" placeholder="Zip Code" v-model="residentDataForm.zipcode"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>

                        <!-- Full Address Preview -->
                        <div v-if="fullAddress" class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Complete Address
                                Preview:</label>
                            <p class="text-sm text-gray-600">{{ fullAddress }}</p>
                        </div>

                        <!-- Contact Information -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Contact Information</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Contact Person</label>
                                    <input type="text" placeholder="Contact Person"
                                        v-model="residentDataForm.contact_person"
                                        class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Emergency
                                        Contact</label>
                                    <input type="text" placeholder="Emergency Contact"
                                        v-model="residentDataForm.emergency_contact"
                                        @input="restrictPhoneInput('emergency_contact')"
                                        :class="{ 'border-red-500': formErrors.emergency_contact }"
                                        class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                    <p v-if="formErrors.emergency_contact" class="text-red-500 text-xs mt-1">{{
                                        formErrors.emergency_contact }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Family Member</label>
                                    <input type="text" placeholder="Family Member"
                                        v-model="residentDataForm.family_member"
                                        class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Demographic Tab -->
                    <div v-show="activeTab === 'demographic'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Demographic Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Place of Birth</label>
                                <input type="text" placeholder="Place of Birth"
                                    v-model="residentDataForm.place_of_birth"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Civil Status</label>
                                <select v-model="residentDataForm.civil_status"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Separated">Separated</option>
                                    <option value="Common Law">Common Law</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Citizenship</label>
                                <input type="text" placeholder="Citizenship" v-model="residentDataForm.citizenship"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Religion</label>
                                <input type="text" placeholder="Religion" v-model="residentDataForm.religion"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Years of Residency</label>
                                <input type="number" placeholder="Years of Residency"
                                    v-model="residentDataForm.years_of_residency" min="0"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Voter Status</label>
                                <select v-model="residentDataForm.voter_status"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                    <option value="Not Registered">Not Registered</option>
                                    <option value="Registered">Registered</option>
                                    <option value="Suspended">Suspended</option>
                                    <option value="Transferred">Transferred</option>
                                </select>
                            </div>
                            <div v-if="residentDataForm.voter_status === 'Registered'" class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Voter Precinct
                                    Number</label>
                                <input type="text" placeholder="Voter Precinct Number"
                                    v-model="residentDataForm.voter_precinct_number"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>
                    </div>

                    <!-- Employment Tab -->
                    <div v-show="activeTab === 'employment'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Employment & Education</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Occupation</label>
                                <input type="text" placeholder="Occupation" v-model="residentDataForm.occupation"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Educational
                                    Attainment</label>
                                <select v-model="residentDataForm.educational_attainment"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                    <option value="No Formal Education">No Formal Education</option>
                                    <option value="Elementary Level">Elementary Level</option>
                                    <option value="Elementary Graduate">Elementary Graduate</option>
                                    <option value="High School Level">High School Level</option>
                                    <option value="High School Graduate">High School Graduate</option>
                                    <option value="Senior High School Graduate">Senior High School Graduate</option>
                                    <option value="Vocational Graduate">Vocational Graduate</option>
                                    <option value="College Level">College Level</option>
                                    <option value="College Graduate">College Graduate</option>
                                    <option value="Post Graduate">Post Graduate</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Monthly Income</label>
                                <input type="number" placeholder="Monthly Income"
                                    v-model="residentDataForm.monthly_income" min="0" step="0.01"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Employer Name</label>
                                <input type="text" placeholder="Employer Name" v-model="residentDataForm.employer_name"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Employer Address</label>
                                <input type="text" placeholder="Employer Address"
                                    v-model="residentDataForm.employer_address"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>
                    </div>

                    <!-- Government IDs Tab -->
                    <div v-show="activeTab === 'government'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Government & ID References</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Valid ID Type</label>
                                <select v-model="residentDataForm.valid_id_type"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                    <option value="">Select ID Type</option>
                                    <option value="PhilHealth ID">PhilHealth ID</option>
                                    <option value="UMID">UMID</option>
                                    <option value="Driver's License">Driver's License</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Postal ID">Postal ID</option>
                                    <option value="Voter's ID">Voter's ID</option>
                                    <option value="TIN ID">TIN ID</option>
                                    <option value="Senior Citizen ID">Senior Citizen ID</option>
                                    <option value="PWD ID">PWD ID</option>
                                    <option value="Barangay ID">Barangay ID</option>
                                    <option value="Student ID">Student ID</option>
                                    <option value="Company ID">Company ID</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Valid ID Number</label>
                                <input type="text" placeholder="Valid ID Number"
                                    v-model="residentDataForm.valid_id_number"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">SSS Number</label>
                                <input type="text" placeholder="SSS Number" v-model="residentDataForm.sss_number"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">PhilHealth Number</label>
                                <input type="text" placeholder="PhilHealth Number"
                                    v-model="residentDataForm.philhealth_number"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">TIN Number</label>
                                <input type="text" placeholder="TIN Number" v-model="residentDataForm.tin_number"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Pag-IBIG Number</label>
                                <input type="text" placeholder="Pag-IBIG Number"
                                    v-model="residentDataForm.pagibig_number"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>
                    </div>

                    <!-- Special Categories Tab -->
                    <div v-show="activeTab === 'special'" class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Special Categories</h3>

                        <!-- PWD Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" v-model="residentDataForm.is_pwd"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">Person with Disability
                                    (PWD)</label>
                            </div>
                            <div v-if="residentDataForm.is_pwd" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">PWD ID Number</label>
                                    <input type="text" placeholder="PWD ID Number"
                                        v-model="residentDataForm.pwd_id_number"
                                        class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Disability
                                        Type</label>
                                    <input type="text" placeholder="Disability Type"
                                        v-model="residentDataForm.disability_type"
                                        class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                </div>
                            </div>
                        </div>

                        <!-- Senior Citizen Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" v-model="residentDataForm.is_senior_citizen"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">Senior Citizen (60+ years
                                    old)</label>
                            </div>
                            <div v-if="residentDataForm.is_senior_citizen" class="mt-3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Senior Citizen ID
                                    Number</label>
                                <input type="text" placeholder="Senior Citizen ID Number"
                                    v-model="residentDataForm.senior_citizen_id_number"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>

                        <!-- 4Ps Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" v-model="residentDataForm.is_4ps_beneficiary"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">4Ps Beneficiary</label>
                            </div>
                            <div v-if="residentDataForm.is_4ps_beneficiary" class="mt-3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">4Ps Household ID</label>
                                <input type="text" placeholder="4Ps Household ID"
                                    v-model="residentDataForm['4ps_household_id']"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>

                        <!-- Solo Parent Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" v-model="residentDataForm.is_solo_parent"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">Solo Parent</label>
                            </div>
                            <div v-if="residentDataForm.is_solo_parent" class="mt-3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Solo Parent ID
                                    Number</label>
                                <input type="text" placeholder="Solo Parent ID Number"
                                    v-model="residentDataForm.solo_parent_id_number"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>

                        <!-- Indigenous Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" v-model="residentDataForm.is_indigenous"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">Indigenous People</label>
                            </div>
                            <div v-if="residentDataForm.is_indigenous" class="mt-3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Indigenous Group</label>
                                <input type="text" placeholder="Indigenous Group"
                                    v-model="residentDataForm.indigenous_group"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>

                        <!-- Other Categories -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" v-model="residentDataForm.is_registered_voter"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">Registered Voter</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" v-model="residentDataForm.is_ofw"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">OFW (Overseas Filipino
                                    Worker)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" v-model="residentDataForm.is_teen_parent"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">Teen Parent</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" v-model="residentDataForm.is_lactating_mother"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">Lactating Mother</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" v-model="residentDataForm.is_pregnant"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                <label class="ml-2 text-sm font-semibold text-gray-700">Pregnant</label>
                            </div>
                        </div>

                        <!-- OFW Country -->
                        <div v-if="residentDataForm.is_ofw">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">OFW Country</label>
                            <input type="text" placeholder="Country of Work" v-model="residentDataForm.ofw_country"
                                class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                        </div>
                    </div>

                    <!-- Additional Information Tab -->
                    <div v-show="activeTab === 'additional'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Resident Status</label>
                                <select v-model="residentDataForm.resident_status"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Deceased">Deceased</option>
                                    <option value="Transferred">Transferred</option>
                                    <option value="Temporary">Temporary</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Date Registered</label>
                                <input type="date" v-model="residentDataForm.date_registered"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Registered By</label>
                                <input type="text" placeholder="Registered By" v-model="residentDataForm.registered_by"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Medical Conditions</label>
                                <textarea placeholder="List any medical conditions..."
                                    v-model="residentDataForm.medical_conditions" rows="3"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-vertical"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Allergies</label>
                                <textarea placeholder="List any allergies..." v-model="residentDataForm.allergies"
                                    rows="3"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-vertical"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Additional Notes</label>
                                <textarea placeholder="Any additional notes or remarks..."
                                    v-model="residentDataForm.notes" rows="4"
                                    class="border rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-vertical"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="bg-gray-50 px-4 sm:px-6 md:px-8 py-4 flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all transform hover:scale-105 active:scale-95 order-2 sm:order-1">
                        Save Resident
                    </button>
                    <button type="button" @click="handleCancel"
                        class="bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105 active:scale-95 order-1 sm:order-2">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>