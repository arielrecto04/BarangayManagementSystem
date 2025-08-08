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
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <form @submit.prevent="createResident" class="w-full max-w-6xl">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">

                <!-- Left Column -->
                <div
                    class="bg-gradient-to-b from-blue-50 to-white p-8 md:w-1/3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
                    <h1 class="text-2xl font-bold mb-4 text-center">Add New Resident</h1>
                    <h2 class="text-base font-medium mb-6 text-center text-gray-600">Resident Profile</h2>

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
                            <input type="text" placeholder="First Name" v-model="residentDataForm.first_name"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" placeholder="Middle Name" v-model="residentDataForm.middle_name"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Last Name</label>
                            <input type="text" placeholder="Last Name" v-model="residentDataForm.last_name"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Birthday -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Birthday</label>
                            <input type="date" v-model="residentDataForm.birthday"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Age -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Age</label>
                            <input type="number" placeholder="Age" v-model="residentDataForm.age"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Gender</label>
                            <select v-model="residentDataForm.gender"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold text-gray-700">Address</label>
                            <input type="text"
                                placeholder="Block & Lot no. / Street / Subdivision / Barangay / City / Province"
                                v-model="residentDataForm.address"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Contact No.</label>
                            <input type="text" placeholder="Contact No." v-model="residentDataForm.contact_number"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Family Member -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Family Member</label>
                            <input type="text" placeholder="Family Member" v-model="residentDataForm.family_member"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Emergency Contact -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Emergency Contact</label>
                            <input type="text" placeholder="Emergency Contact"
                                v-model="residentDataForm.emergency_contact"
                                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Contact Person -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Contact Person</label>
                            <input type="text" placeholder="Contact Person" v-model="residentDataForm.contact_person"
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
    </div>
</template>
