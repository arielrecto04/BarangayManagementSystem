<script setup>
import { useResidentStore } from '@/Stores'
import { ref, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import useToast from '@/Utils/useToast'

const router = useRouter()
const route = useRoute()
const { showToast } = useToast()
const residentStore = useResidentStore()

const residentDataForm = ref({
    first_name: '',
    middle_name: '',
    last_name: '',
    birthday: '',
    age: '',
    gender: '',
    address: '',
    contact_number: '',
    contact_person: '',
    family_member: '',
    emergency_contact: '',
    email: '',
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


const formErrors = ref({})

const restrictPhoneInput = (field) => {
    let value = residentDataForm.value[field]
    value = value.replace(/\D/g, '')
    if (value.length > 0 && !value.startsWith('09')) {
        value = '09' + value.replace(/^0+/, '').replace(/^9?/, '')
    }
    if (value.length > 11) value = value.slice(0, 11)
    residentDataForm.value[field] = value
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
        address: 'Address',
        contact_number: 'Contact Number',
    }

    for (const [field, label] of Object.entries(requiredFields)) {
        if (!residentDataForm.value[field] || residentDataForm.value[field].toString().trim() === '') {
            formErrors.value[field] = `${label} is required.`
            isValid = false
        }
    }

    const contact = residentDataForm.value.contact_number
    if (contact && !/^09\d{9}$/.test(contact)) {
        formErrors.value.contact_number = 'Contact Number must start with 09 and be exactly 11 digits.'
        isValid = false
    }

    const emergencyContact = residentDataForm.value.emergency_contact
    if (emergencyContact && emergencyContact.trim() !== '' && !/^09\d{9}$/.test(emergencyContact)) {
        formErrors.value.emergency_contact = 'Emergency Contact must start with 09 and be exactly 11 digits.'
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

    try {
        await residentStore.addResident(residentDataForm.value)
        showToast({ icon: 'success', title: 'Resident created successfully' })
        router.push('/residents')
    } catch (error) {
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

onBeforeUnmount(() => {
    residentStore.clearState()
})
</script>

<template>
    <div :key="route.fullPath"
        class="min-h-screen bg-gray-100 flex justify-center items-start sm:items-center p-2 sm:p-4 md:p-10">
        <form @submit.prevent="createResident" class="w-full max-w-5xl">
            <div class="bg-white rounded-lg sm:rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
                <!-- Left Column (Avatar) - Mobile First -->
                <div
                    class="bg-gradient-to-b from-blue-50 to-white p-4 sm:p-6 md:p-8 w-full md:w-1/3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold mb-2 text-center leading-tight">Add New Resident
                    </h1>
                    <h2 class="text-sm sm:text-base font-medium mb-4 text-center text-gray-600">Resident Profile</h2>
                    <div
                        class="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg sm:rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:bg-gray-50 transition">
                        <div class="text-center">
                            <div class="text-2xl sm:text-3xl md:text-4xl text-gray-400 mb-1">📸</div>
                            <p class="text-xs sm:text-sm text-gray-500">Upload Photo</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Form) - Mobile First -->
                <div class="p-4 sm:p-6 md:p-8 w-full md:w-2/3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <!-- First Name -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">First Name</label>
                            <input type="text" placeholder="First Name" v-model="residentDataForm.first_name"
                                :class="{ 'border-red-500': formErrors.first_name }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            <p v-if="formErrors.first_name" class="text-red-500 text-xs mt-1">{{ formErrors.first_name
                            }}</p>
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Middle Name</label>
                            <input type="text" placeholder="Middle Name" v-model="residentDataForm.middle_name"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Last Name</label>
                            <input type="text" placeholder="Last Name" v-model="residentDataForm.last_name"
                                :class="{ 'border-red-500': formErrors.last_name }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            <p v-if="formErrors.last_name" class="text-red-500 text-xs mt-1">{{ formErrors.last_name }}
                            </p>
                        </div>

                        <!-- Birthday -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Birthday</label>
                            <input type="date" v-model="residentDataForm.birthday"
                                :class="{ 'border-red-500': formErrors.birthday }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            <p v-if="formErrors.birthday" class="text-red-500 text-xs mt-1">{{ formErrors.birthday }}
                            </p>
                        </div>

                        <!-- Age -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Age</label>
                            <input type="number" placeholder="Age" v-model="residentDataForm.age"
                                :class="{ 'border-red-500': formErrors.age }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            <p v-if="formErrors.age" class="text-red-500 text-xs mt-1">{{ formErrors.age }}</p>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Gender</label>
                            <select v-model="residentDataForm.gender" :class="{ 'border-red-500': formErrors.gender }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                <option value="" disabled>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <p v-if="formErrors.gender" class="text-red-500 text-xs mt-1">{{ formErrors.gender }}</p>
                        </div>

                        <!-- Address (full width) -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Address</label>
                            <input type="text" placeholder="Complete Address" v-model="residentDataForm.address"
                                :class="{ 'border-red-500': formErrors.address }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            <p v-if="formErrors.address" class="text-red-500 text-xs mt-1">{{ formErrors.address }}</p>
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Contact
                                Number</label>
                            <input type="text" placeholder="Contact No." v-model="residentDataForm.contact_number"
                                @input="restrictPhoneInput('contact_number')"
                                :class="{ 'border-red-500': formErrors.contact_number }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            <p v-if="formErrors.contact_number" class="text-red-500 text-xs mt-1">{{
                                formErrors.contact_number }}</p>
                        </div>

                        <!-- Emergency Contact -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Emergency
                                Contact</label>
                            <input type="text" placeholder="Emergency Contact"
                                v-model="residentDataForm.emergency_contact"
                                @input="restrictPhoneInput('emergency_contact')"
                                :class="{ 'border-red-500': formErrors.emergency_contact }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            <p v-if="formErrors.emergency_contact" class="text-red-500 text-xs mt-1">{{
                                formErrors.emergency_contact }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" placeholder="Email" v-model="residentDataForm.email"
                                :class="{ 'border-red-500': formErrors.email }"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            <p v-if="formErrors.email" class="text-red-500 text-xs mt-1">{{ formErrors.email }}</p>
                        </div>

                        <!-- Contact Person -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Contact
                                Person</label>
                            <input type="text" placeholder="Contact Person" v-model="residentDataForm.contact_person"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                        </div>

                        <!-- Family Member -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Family
                                Member</label>
                            <input type="text" placeholder="Family Member" v-model="residentDataForm.family_member"
                                class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row justify-center mt-6 gap-3 sm:gap-4">
                        <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 sm:py-2.5 rounded-lg sm:rounded-xl shadow-lg font-semibold text-sm sm:text-base transition-all transform hover:scale-105 w-full sm:w-auto order-2 sm:order-1 active:scale-95">
                            Save
                        </button>
                        <router-link to="/residents"
                            class="bg-white border-2 border-gray-300 hover:border-gray-400 px-6 py-3 sm:py-2.5 rounded-lg sm:rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 text-sm sm:text-base transition-all transform hover:scale-105 w-full sm:w-auto text-center order-1 sm:order-2 active:scale-95">
                            Cancel
                        </router-link>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>