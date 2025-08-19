<script setup>
import { useResidentStore } from '@/Stores'
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';
import { storeToRefs } from 'pinia';

const router = useRouter();
const { showToast } = useToast();

const residentStore = useResidentStore();

const { resident, isLoading } = storeToRefs(residentStore);
const residentId = router.currentRoute.value.params.id;

// Reactive formErrors object to hold any validation messages
const formErrors = ref({});

// Watch resident to reset formErrors on data load
watch(resident, () => {
    formErrors.value = {};
}, { immediate: true });

// Restrict number inputs for contact fields
const restrictPhoneInput = (field) => {
    let value = resident.value[field]

    // Remove any non-digit characters
    value = value.replace(/\D/g, '')

    // Ensure it starts with 09
    if (value.length > 0 && !value.startsWith('09')) {
        value = '09' + value.replace(/^0+/, '').replace(/^9?/, '')
    }

    // Limit to 11 digits only
    if (value.length > 11) {
        value = value.slice(0, 11)
    }

    resident.value[field] = value
}

const validateForm = () => {
    formErrors.value = {};
    let isValid = true;

    // Define required fields and their labels
    const requiredFields = {
        first_name: 'First Name',
        last_name: 'Last Name',
        birthday: 'Birthday',
        age: 'Age',
        gender: 'Gender',
        address: 'Address',
        contact_number: 'Contact Number',
    };

    for (const [field, label] of Object.entries(requiredFields)) {
        const value = resident.value[field];
        if (!value || (typeof value === 'string' && value.trim() === '')) {
            formErrors.value[field] = `${label} is required.`;
            isValid = false;
        }
    }

    // ✅ Extra validation for Contact Number
    const contact = resident.value.contact_number
    if (contact && !/^09\d{9}$/.test(contact)) {
        formErrors.value.contact_number = 'Contact Number must start with 09 and be exactly 11 digits.'
        isValid = false
    }

    // ✅ Extra validation for Emergency Contact (if provided)
    const emergencyContact = resident.value.emergency_contact
    if (emergencyContact && emergencyContact.trim() !== '' && !/^09\d{9}$/.test(emergencyContact)) {
        formErrors.value.emergency_contact = 'Emergency Contact must start with 09 and be exactly 11 digits.'
        isValid = false
    }

    return isValid;
};

const updateResidentData = async () => {
    if (!validateForm()) {
        showToast({ icon: 'error', title: 'Please fill in all required fields.' });
        return;
    }

    try {
        // Pass the updated resident data object to update method
        await residentStore.updateResident(resident.value);
        showToast({ icon: 'success', title: 'Resident updated successfully' });
        router.push('/residents');
    } catch (error) {
        // Handle validation errors from backend (HTTP 422)
        if (error.response && error.response.status === 422 && error.response.data.errors) {
            const errors = error.response.data.errors;
            formErrors.value = {};
            for (const [field, messages] of Object.entries(errors)) {
                formErrors.value[field] = messages.join(' ');
            }
            const message = Object.values(errors).flat().join(' ');
            showToast({ icon: 'error', title: message });
        } else {
            showToast({ icon: 'error', title: error.message || 'Failed to update resident.' });
        }
    }
};

onMounted(() => {
    residentStore.getResidentById(residentId);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <template v-if="isLoading || !resident">
            <div class="flex justify-center items-center">
                <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
            </div>
        </template>
        <template v-else>
            <form @submit.prevent="updateResidentData" class="w-full max-w-6xl">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
                    <!-- Left Column -->
                    <div
                        class="bg-gradient-to-b from-blue-50 to-white p-8 md:w-1/3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
                        <h1 class="text-2xl font-bold mb-4 text-center">Edit Resident</h1>
                        <h2 class="text-base font-medium mb-6 text-center text-gray-600">
                            Resident Profile
                        </h2>

                        <!-- Image Placeholder -->
                        <div
                            class="w-40 h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:bg-gray-50 transition">
                            <div class="text-center">
                                <div class="text-4xl text-gray-400 mb-2">📸</div>
                                <p class="text-sm text-gray-500">Upload Photo</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Form) -->
                    <div class="p-8 md:w-2/3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- First Name -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">First Name</label>
                                <input type="text" placeholder="First Name" v-model="resident.first_name"
                                    :class="{ 'border-red-500': formErrors.first_name }"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p v-if="formErrors.first_name" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.first_name }}
                                </p>
                            </div>

                            <!-- Middle Name -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                                <input type="text" placeholder="Middle Name" v-model="resident.middle_name"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Last Name</label>
                                <input type="text" placeholder="Last Name" v-model="resident.last_name"
                                    :class="{ 'border-red-500': formErrors.last_name }"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p v-if="formErrors.last_name" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.last_name }}
                                </p>
                            </div>

                            <!-- Birthday -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Birthday</label>
                                <input type="date" v-model="resident.birthday"
                                    :class="{ 'border-red-500': formErrors.birthday }"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p v-if="formErrors.birthday" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.birthday }}
                                </p>
                            </div>

                            <!-- Age -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Age</label>
                                <input type="number" placeholder="Age" v-model="resident.age"
                                    :class="{ 'border-red-500': formErrors.age }"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p v-if="formErrors.age" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.age }}
                                </p>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Gender</label>
                                <select v-model="resident.gender" :class="{ 'border-red-500': formErrors.gender }"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <p v-if="formErrors.gender" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.gender }}
                                </p>
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold text-gray-700">Address</label>
                                <input type="text"
                                    placeholder="Block & Lot no. / Street / Subdivision / Barangay / City / Province"
                                    v-model="resident.address" :class="{ 'border-red-500': formErrors.address }"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p v-if="formErrors.address" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.address }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Email</label>
                                <input type="text" placeholder="Email" v-model="resident.email"
                                    :class="{ 'border-red-500': formErrors.email }"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p v-if="formErrors.email" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.email }}
                                </p>
                            </div>

                            <!-- Contact Number -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Contact Number</label>
                                <input type="text" placeholder="Contact No." v-model="resident.contact_number"
                                    @input="restrictPhoneInput('contact_number')"
                                    :class="{ 'border-red-500': formErrors.contact_number }"
                                    class="border rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p v-if="formErrors.contact_number" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.contact_number }}
                                </p>
                            </div>

                            <!-- Family Member -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Family Member</label>
                                <input type="text" placeholder="Family Member" v-model="resident.family_member"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>

                            <!-- Emergency Contact -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Emergency Contact</label>
                                <input type="text" placeholder="Emergency Contact" v-model="resident.emergency_contact"
                                    @input="restrictPhoneInput('emergency_contact')"
                                    :class="{ 'border-red-500': formErrors.emergency_contact }"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p v-if="formErrors.emergency_contact" class="text-red-500 text-sm mt-1">
                                    {{ formErrors.emergency_contact }}
                                </p>
                            </div>

                            <!-- Contact Person -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Contact Person</label>
                                <input type="text" placeholder="Contact Person" v-model="resident.contact_person"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-center mt-8 gap-4">
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all transform hover:scale-105">
                                Save
                            </button>
                            <router-link to="/residents"
                                class="bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105">
                                Cancel
                            </router-link>
                        </div>
                    </div>
                </div>
            </form>
        </template>
    </div>
</template>