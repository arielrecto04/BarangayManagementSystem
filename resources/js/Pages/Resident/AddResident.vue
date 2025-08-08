<script setup>
import { useResidentStore } from '@/Stores'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';

const router = useRouter();
const { showToast } = useToast();
const fieldErrors = ref({});
const fieldErrors = ref({});

const residentStore = useResidentStore();


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
    resident_number: '',
    email: '',
});


function validatePhoneInput(fieldName) {
    let value = residentDataForm.value[fieldName] || '';

    // Remove any non-digit characters
    value = value.replace(/\D/g, '');

    // Limit length to max 11 digits
    if (value.length > 11) value = value.slice(0, 11);

    residentDataForm.value[fieldName] = value;
}

function clearFieldError(fieldName) {
    if (fieldErrors.value[fieldName]) {
        delete fieldErrors.value[fieldName];
    }
}


const validateForm = () => {
    const requiredFields = [
        residentDataForm.value.first_name,
        residentDataForm.value.last_name,
        residentDataForm.value.birthday,
        residentDataForm.value.age,
        residentDataForm.value.gender,
        residentDataForm.value.address,
        residentDataForm.value.contact_number,
        residentDataForm.value.resident_number,
    ];

    if (!requiredFields.every(f => f && f.toString().trim() !== '')) {
        showToast({ icon: 'error', title: 'Please fill all required fields' });
        return false;
    }

    // ✅ Validate contact_number has exactly 11 digits
    if (residentDataForm.value.contact_number.length !== 11) {
        showToast({ icon: 'error', title: 'Contact Number must be exactly 11 digits' });
        return false;
    }

    // ✅ Validate emergency_contact has exactly 11 digits if provided
    if (
        residentDataForm.value.emergency_contact &&
        residentDataForm.value.emergency_contact.length !== 11
    ) {
        showToast({ icon: 'error', title: 'Emergency Contact must be exactly 11 digits' });
        return false;
    }

    // ✅ Ensure contact_number and emergency_contact contain only digits
    const elevenDigitPhoneRegex = /^\d{11}$/;

    if (!elevenDigitPhoneRegex.test(residentDataForm.value.contact_number)) {
        showToast({ icon: 'error', title: 'Contact Number must contain only digits' });
        return false;
    }

    if (
        residentDataForm.value.emergency_contact &&
        !elevenDigitPhoneRegex.test(residentDataForm.value.emergency_contact)
    ) {
        showToast({ icon: 'error', title: 'Emergency Contact must contain only digits' });
        return false;
    }

    // ✅ Validate age is a number
    if (isNaN(Number(residentDataForm.value.age))) {
        showToast({ icon: 'error', title: 'Age must be a number' });
        return false;
    }

    // ✅ Validate email format
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (
        residentDataForm.value.email &&
        !emailPattern.test(residentDataForm.value.email)
    ) {
        showToast({ icon: 'error', title: 'Please enter a valid email address' });
        return false;
    }

    return true;
};


resident_number: '',
    email: '',
});


function validatePhoneInput(fieldName) {
    let value = residentDataForm.value[fieldName] || '';

    // Remove any non-digit characters
    value = value.replace(/\D/g, '');

    // Limit length to max 11 digits
    if (value.length > 11) value = value.slice(0, 11);

    residentDataForm.value[fieldName] = value;
}

function clearFieldError(fieldName) {
    if (fieldErrors.value[fieldName]) {
        delete fieldErrors.value[fieldName];
    }
}


const validateForm = () => {
    const requiredFields = [
        residentDataForm.value.first_name,
        residentDataForm.value.last_name,
        residentDataForm.value.birthday,
        residentDataForm.value.age,
        residentDataForm.value.gender,
        residentDataForm.value.address,
        residentDataForm.value.contact_number,
        residentDataForm.value.resident_number,
    ];

    if (!requiredFields.every(f => f && f.toString().trim() !== '')) {
        showToast({ icon: 'error', title: 'Please fill all required fields' });
        return false;
    }

    // ✅ Validate contact_number has exactly 11 digits
    if (residentDataForm.value.contact_number.length !== 11) {
        showToast({ icon: 'error', title: 'Contact Number must be exactly 11 digits' });
        return false;
    }

    // ✅ Validate emergency_contact has exactly 11 digits if provided
    if (
        residentDataForm.value.emergency_contact &&
        residentDataForm.value.emergency_contact.length !== 11
    ) {
        showToast({ icon: 'error', title: 'Emergency Contact must be exactly 11 digits' });
        return false;
    }

    // ✅ Ensure contact_number and emergency_contact contain only digits
    const elevenDigitPhoneRegex = /^\d{11}$/;

    if (!elevenDigitPhoneRegex.test(residentDataForm.value.contact_number)) {
        showToast({ icon: 'error', title: 'Contact Number must contain only digits' });
        return false;
    }

    if (
        residentDataForm.value.emergency_contact &&
        !elevenDigitPhoneRegex.test(residentDataForm.value.emergency_contact)
    ) {
        showToast({ icon: 'error', title: 'Emergency Contact must contain only digits' });
        return false;
    }

    // ✅ Validate age is a number
    if (isNaN(Number(residentDataForm.value.age))) {
        showToast({ icon: 'error', title: 'Age must be a number' });
        return false;
    }

    // ✅ Validate email format
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (
        residentDataForm.value.email &&
        !emailPattern.test(residentDataForm.value.email)
    ) {
        showToast({ icon: 'error', title: 'Please enter a valid email address' });
        return false;
    }

    return true;
};





const createResident = async () => {
    fieldErrors.value = {}; // clear old errors

    if (!validateForm()) return;

    try {
        residentDataForm.value.age = Number(residentDataForm.value.age);

        await residentStore.addResident(residentDataForm.value);
        showToast({ icon: 'success', title: 'Resident created successfully' });
        router.push('/residents');
    } catch (error) {
        console.error('Add resident error response:', error.response?.data);
        if (error.response?.data?.errors) {
            fieldErrors.value = { ...error.response.data.errors };
            const messages = Object.values(error.response.data.errors).flat().join(' ');
            showToast({ icon: 'error', title: 'Validation error', text: messages });
        } else {
            showToast({ icon: 'error', title: error.message });
        }
    }
};




</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-5">
        <form @submit.prevent="createResident"
            class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl p-0 overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <!-- Left Panel: Photo upload and Section Title -->
                <div
                    class="md:w-1/3 bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center p-8 border-b md:border-b-0 md:border-r border-gray-200">
                    <div
                        class="w-40 h-40 mb-8 grid place-items-center bg-white rounded-xl border-2 border-dashed border-gray-300 shadow">
                        <div class="text-center">
                            <div class="text-4xl text-gray-400 mb-2">👤</div>
                            <p class="text-sm text-gray-500">Upload Photo</p>
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-1 text-center">Add New Resident</h1>
                    <h2 class="text-md font-medium text-gray-600 text-center">Resident Profile</h2>
                </div>

                <!-- Right Panel: Form Fields -->
                <div class="md:w-2/3 p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-semibold mb-2 text-gray-700">First
                                Name</label>
                            <input id="first_name" type="text" placeholder="First Name"
                                v-model="residentDataForm.first_name"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('first_name')" />
                            <p v-if="fieldErrors.first_name" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.first_name[0] }}
                            </p>
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent"
                            @input="clearFieldError('first_name')" />
                            <p v-if="fieldErrors.first_name" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.first_name[0] }}
                            </p>
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label for="middle_name" class="block text-sm font-semibold mb-2 text-gray-700">Middle
                                Name</label>
                            <input id="middle_name" type="text" placeholder="Middle Name"
                                v-model="residentDataForm.middle_name"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('middle_name')" />
                            <p v-if="fieldErrors.middle_name" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.middle_name[0] }}
                            </p>
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent"
                            @input="clearFieldError('middle_name')" />
                            <p v-if="fieldErrors.middle_name" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.middle_name[0] }}
                            </p>
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-semibold mb-2 text-gray-700">Last
                                Name</label>
                            <input id="last_name" type="text" placeholder="Last Name"
                                v-model="residentDataForm.last_name"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('last_name')" />
                            <p v-if="fieldErrors.last_name" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.last_name[0] }}
                            </p>
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent"
                            @input="clearFieldError('last_name')" />
                            <p v-if="fieldErrors.last_name" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.last_name[0] }}
                            </p>
                        </div>

                        <!-- Birthday -->
                        <div>
                            <label for="birthday"
                                class="block text-sm font-semibold mb-2 text-gray-700">Birthday</label>
                            <input id="birthday" type="date" placeholder="Birthday" v-model="residentDataForm.birthday"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('birthday')" />
                            <p v-if="fieldErrors.birthday" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.birthday[0] }}
                            </p>
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent"
                            @input="clearFieldError('birthday')" />
                            <p v-if="fieldErrors.birthday" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.birthday[0] }}
                            </p>
                        </div>

                        <!-- Age -->
                        <div>
                            <label for="age" class="block text-sm font-semibold mb-2 text-gray-700">Age</label>
                            <input id="age" type="number" placeholder="Age" v-model="residentDataForm.age"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('age')" />
                            <p v-if="fieldErrors.age" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.age[0] }}
                            </p>
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent"
                            @input="clearFieldError('age')" />
                            <p v-if="fieldErrors.age" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.age[0] }}
                            </p>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-semibold mb-2 text-gray-700">Gender</label>
                            <select id="gender" v-model="residentDataForm.gender"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('gender')">
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                                focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('gender')">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <p v-if="fieldErrors.gender" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.gender[0] }}
                            </p>
                            <p v-if="fieldErrors.gender" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.gender[0] }}
                            </p>
                        </div>

                        <!-- Address (span 3 columns) -->
                        <div class="sm:col-span-3">
                            <label for="address" class="block text-sm font-semibold mb-2 text-gray-700">Address</label>
                            <input id="address" type="text"
                                placeholder="Block & Lot no. / Street / Subdivision / Barangay / City / Province"
                                v-model="residentDataForm.address"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('address')" />
                            <p v-if="fieldErrors.address" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.address[0] }}
                            </p>
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent"
                            @input="clearFieldError('address')" />
                            <p v-if="fieldErrors.address" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.address[0] }}
                            </p>
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label for="contact_number" class="block text-sm font-semibold mb-2 text-gray-700">Contact
                                No.</label>
                            <input id="contact_number" type="text" maxlength="11" placeholder="09123456789"
                                v-model="residentDataForm.contact_number" @input="validatePhoneInput('contact_number')"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent text-sm" />
                            <p v-if="fieldErrors.contact_number" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.contact_number[0] }}
                            </p>
                            <input id="contact_number" type="text" maxlength="11" placeholder="09123456789"
                                v-model="residentDataForm.contact_number" @input="validatePhoneInput('contact_number')"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent text-sm" />
                            <p v-if="fieldErrors.contact_number" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.contact_number[0] }}
                            </p>
                        </div>



                        <!-- Family Member -->
                        <div>
                            <label for="family_member" class="block text-sm font-semibold mb-2 text-gray-700">Family
                                Member</label>
                            <input id="family_member" type="text" placeholder="Family Member"
                                v-model="residentDataForm.family_member"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('family_member')" />
                            <p v-if="fieldErrors.family_member" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.family_member[0] }}
                            </p>
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent"
                            @input="clearFieldError('family_member')" />
                            <p v-if="fieldErrors.family_member" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.family_member[0] }}
                            </p>
                        </div>

                        <!-- Emergency Contact -->
                        <div>
                            <label for="emergency_contact"
                                class="block text-sm font-semibold mb-2 text-gray-700">Emergency Contact</label>
                            <input id="emergency_contact" type="text" maxlength="11" placeholder="09123456789" <input
                                id="emergency_contact" type="text" maxlength="11" placeholder="09123456789"
                                v-model="residentDataForm.emergency_contact"
                                @input="validatePhoneInput('emergency_contact')"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent text-sm" />
                            <p v-if="fieldErrors.emergency_contact" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.emergency_contact[0] }}
                            </p>
                            @input="validatePhoneInput('emergency_contact')"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent text-sm" />
                            <p v-if="fieldErrors.emergency_contact" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.emergency_contact[0] }}
                            </p>
                        </div>



                        <!-- Contact Person -->
                        <div>
                            <label for="contact_person" class="block text-sm font-semibold mb-2 text-gray-700">Contact
                                Person</label>
                            <input id="contact_person" type="text" placeholder="Contact Person"
                                v-model="residentDataForm.contact_person"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('contact_person')" />
                            <p v-if="fieldErrors.contact_person" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.contact_person[0] }}
                            </p>
                        </div>

                        <!-- Resident Number -->
                        <div>
                            <label for="resident_number" class="block text-sm font-semibold mb-2 text-gray-700">Resident
                                Number</label>
                            <input id="resident_number" type="text" placeholder="Resident Number"
                                v-model="residentDataForm.resident_number"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('resident_number')" />
                            <p v-if="fieldErrors.resident_number" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.resident_number[0] }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-2 text-gray-700">Email</label>
                            <input id="email" type="email" placeholder="Email address" v-model="residentDataForm.email"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('email')" />
                            <p v-if="fieldErrors.email" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.email[0] }}
                            </p>
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2
                            focus:ring-green-400 focus:border-transparent"
                            @input="clearFieldError('contact_person')" />
                            <p v-if="fieldErrors.contact_person" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.contact_person[0] }}
                            </p>
                        </div>

                        <!-- Resident Number -->
                        <div>
                            <label for="resident_number" class="block text-sm font-semibold mb-2 text-gray-700">Resident
                                Number</label>
                            <input id="resident_number" type="text" placeholder="Resident Number"
                                v-model="residentDataForm.resident_number"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('resident_number')" />
                            <p v-if="fieldErrors.resident_number" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.resident_number[0] }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-2 text-gray-700">Email</label>
                            <input id="email" type="email" placeholder="Email address" v-model="residentDataForm.email"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                                @input="clearFieldError('email')" />
                            <p v-if="fieldErrors.email" class="mt-1 text-red-600 text-xs">
                                {{ fieldErrors.email[0] }}
                            </p>
                        </div>



                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-10">
                        <button type="submit"
                            class="w-full sm:w-auto bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                            Save
                        </button>
                        <router-link to="/residents"
                            class="w-full sm:w-auto bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                            Cancel
                        </router-link>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
