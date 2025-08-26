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
    <div class="min-h-screen bg-gray-100 flex justify-center items-start sm:items-center p-2 sm:p-4 md:p-10">
        <template v-if="isLoading || !resident">
            <div class="flex justify-center items-center w-full">
                <div class="animate-spin rounded-full h-24 w-24 sm:h-32 sm:w-32 border-b-2 border-gray-900"></div>
            </div>
        </template>

        <template v-else>
            <form @submit.prevent="updateResidentData" class="w-full max-w-6xl">
                <div class="bg-white rounded-lg sm:rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">

                    <!-- Left Column (Avatar) -->
                    <div
                        class="bg-gradient-to-b from-blue-50 to-white p-4 sm:p-6 md:p-8 w-full md:w-1/3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
                        <h1 class="text-lg sm:text-xl md:text-2xl font-bold mb-2 text-center leading-tight">Edit
                            Resident</h1>
                        <h2 class="text-sm sm:text-base font-medium mb-4 text-center text-gray-600">Resident Profile
                        </h2>
                        <div
                            class="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg sm:rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:bg-gray-50 transition">
                            <div class="text-center">
                                <div class="text-2xl sm:text-3xl md:text-4xl text-gray-400 mb-1">📸</div>
                                <p class="text-xs sm:text-sm text-gray-500">Upload Photo</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Form) -->
                    <div class="p-4 sm:p-6 md:p-8 w-full md:w-2/3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">

                            <!-- First Name -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">First
                                    Name</label>
                                <input type="text" v-model="resident.first_name"
                                    :class="{ 'border-red-500': formErrors.first_name }"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.first_name" class="text-red-500 text-xs mt-1">{{
                                    formErrors.first_name }}</p>
                            </div>

                            <!-- Middle Name -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Middle
                                    Name</label>
                                <input type="text" v-model="resident.middle_name"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Last
                                    Name</label>
                                <input type="text" v-model="resident.last_name"
                                    :class="{ 'border-red-500': formErrors.last_name }"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.last_name" class="text-red-500 text-xs mt-1">{{ formErrors.last_name
                                    }}</p>
                            </div>

                            <!-- Birthday -->
                            <div>
                                <label
                                    class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Birthday</label>
                                <input type="date" v-model="resident.birthday"
                                    :class="{ 'border-red-500': formErrors.birthday }"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.birthday" class="text-red-500 text-xs mt-1">{{ formErrors.birthday
                                    }}</p>
                            </div>

                            <!-- Age -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Age</label>
                                <input type="number" v-model="resident.age"
                                    :class="{ 'border-red-500': formErrors.age }"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.age" class="text-red-500 text-xs mt-1">{{ formErrors.age }}</p>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Gender</label>
                                <select v-model="resident.gender" :class="{ 'border-red-500': formErrors.gender }"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <p v-if="formErrors.gender" class="text-red-500 text-xs mt-1">{{ formErrors.gender }}
                                </p>
                            </div>

                            <!-- Address (full width) -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Address</label>
                                <input type="text" v-model="resident.address"
                                    :class="{ 'border-red-500': formErrors.address }"
                                    placeholder="Block & Lot / Street / Subdivision / Barangay / City / Province"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.address" class="text-red-500 text-xs mt-1">{{ formErrors.address }}
                                </p>
                            </div>

                            <!-- Contact Number -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Contact
                                    Number</label>
                                <input type="text" v-model="resident.contact_number"
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
                                <input type="text" v-model="resident.emergency_contact"
                                    @input="restrictPhoneInput('emergency_contact')"
                                    :class="{ 'border-red-500': formErrors.emergency_contact }"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.emergency_contact" class="text-red-500 text-xs mt-1">{{
                                    formErrors.emergency_contact }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Email</label>
                                <input type="text" v-model="resident.email"
                                    :class="{ 'border-red-500': formErrors.email }"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                                <p v-if="formErrors.email" class="text-red-500 text-xs mt-1">{{ formErrors.email }}</p>
                            </div>

                            <!-- Contact Person -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Contact
                                    Person</label>
                                <input type="text" v-model="resident.contact_person"
                                    class="border rounded-md sm:rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 w-full text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" />
                            </div>

                            <!-- Family Member (full width) -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Family
                                    Member</label>
                                <input type="text" v-model="resident.family_member"
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
        </template>
    </div>
</template>
