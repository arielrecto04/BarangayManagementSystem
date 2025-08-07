<script setup>
import { useResidentStore } from '@/Stores'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';

const router = useRouter();
const { showToast } = useToast();

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
});



const createResident = async () => {

    console.log(residentDataForm.value);
    try {
        await residentStore.addResident(residentDataForm.value);
        showToast({ icon: 'success', title: 'Resident created successfully' });
        router.push('/residents');

    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}


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
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label for="middle_name" class="block text-sm font-semibold mb-2 text-gray-700">Middle
                                Name</label>
                            <input id="middle_name" type="text" placeholder="Middle Name"
                                v-model="residentDataForm.middle_name"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-semibold mb-2 text-gray-700">Last
                                Name</label>
                            <input id="last_name" type="text" placeholder="Last Name"
                                v-model="residentDataForm.last_name"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Birthday -->
                        <div>
                            <label for="birthday"
                                class="block text-sm font-semibold mb-2 text-gray-700">Birthday</label>
                            <input id="birthday" type="date" placeholder="Birthday" v-model="residentDataForm.birthday"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Age -->
                        <div>
                            <label for="age" class="block text-sm font-semibold mb-2 text-gray-700">Age</label>
                            <input id="age" type="number" placeholder="Age" v-model="residentDataForm.age"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-semibold mb-2 text-gray-700">Gender</label>
                            <select id="gender" v-model="residentDataForm.gender"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <!-- Address (span 3 columns) -->
                        <div class="sm:col-span-3">
                            <label for="address" class="block text-sm font-semibold mb-2 text-gray-700">Address</label>
                            <input id="address" type="text"
                                placeholder="Block & Lot no. / Street / Subdivision / Barangay / City / Province"
                                v-model="residentDataForm.address"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label for="contact_number" class="block text-sm font-semibold mb-2 text-gray-700">Contact
                                No.</label>
                            <input id="contact_number" type="text" placeholder="Contact No."
                                v-model="residentDataForm.contact_number"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Family Member -->
                        <div>
                            <label for="family_member" class="block text-sm font-semibold mb-2 text-gray-700">Family
                                Member</label>
                            <input id="family_member" type="text" placeholder="Family Member"
                                v-model="residentDataForm.family_member"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Emergency Contact -->
                        <div>
                            <label for="emergency_contact"
                                class="block text-sm font-semibold mb-2 text-gray-700">Emergency Contact</label>
                            <input id="emergency_contact" type="text" placeholder="Emergency Contact"
                                v-model="residentDataForm.emergency_contact"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
                        </div>

                        <!-- Contact Person -->
                        <div>
                            <label for="contact_person" class="block text-sm font-semibold mb-2 text-gray-700">Contact
                                Person</label>
                            <input id="contact_person" type="text" placeholder="Contact Person"
                                v-model="residentDataForm.contact_person"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
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
