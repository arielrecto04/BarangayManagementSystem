<script setup>
import { useResidentStore } from '@/Stores'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';
import { storeToRefs } from 'pinia';

const router = useRouter();
const { showToast } = useToast();

const residentStore = useResidentStore();

const { resident, isLoading } = storeToRefs(residentStore);

const residentId = router.currentRoute.value.params.id;



const updateResidentData = async () => {
    try {
        await residentStore.updateResident();
        showToast({ icon: 'success', title: 'Resident updated successfully' });
        router.push('/residents');
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}


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

                    <!-- Right Column (Form Fields) -->
                    <div class="p-8 md:w-2/3">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- First Name -->
                            <div class="flex flex-col gap-2">
                                <label for="first_name" class="text-sm font-semibold text-gray-600">First Name</label>
                                <input type="text" placeholder="First Name" v-model="resident.first_name"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Middle Name -->
                            <div class="flex flex-col gap-2">
                                <label for="middle_name" class="text-sm font-semibold text-gray-600">Middle Name</label>
                                <input type="text" placeholder="Middle Name" v-model="resident.middle_name"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Last Name -->
                            <div class="flex flex-col gap-2">
                                <label for="last_name" class="text-sm font-semibold text-gray-600">Last Name</label>
                                <input type="text" placeholder="Last Name" v-model="resident.last_name"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Birthday -->
                            <div class="flex flex-col gap-2">
                                <label for="birthday" class="text-sm font-semibold text-gray-600">Birthday</label>
                                <input type="date" placeholder="Birthday" v-model="resident.birthday"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Age -->
                            <div class="flex flex-col gap-2">
                                <label for="age" class="text-sm font-semibold text-gray-600">Age</label>
                                <input type="number" placeholder="Age" v-model="resident.age"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Gender -->
                            <div class="flex flex-col gap-2">
                                <label for="gender" class="text-sm font-semibold text-gray-600">Gender</label>
                                <select v-model="resident.gender"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>

                            <!-- Address -->
                            <div class="flex flex-col gap-2 md:col-span-2">
                                <label for="address" class="text-sm font-semibold text-gray-600">Address</label>
                                <input type="text" placeholder="Lot no. / Street / Subdivision / Barangay"
                                    v-model="resident.address"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Contact Number -->
                            <div class="flex flex-col gap-2">
                                <label for="contact_number" class="text-sm font-semibold text-gray-600">Contact
                                    No.</label>
                                <input type="text" placeholder="Contact No." v-model="resident.contact_number"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Family Member -->
                            <div class="flex flex-col gap-2">
                                <label for="family_member" class="text-sm font-semibold text-gray-600">Family
                                    Member</label>
                                <input type="text" placeholder="Family Member" v-model="resident.family_member"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Emergency Contact -->
                            <div class="flex flex-col gap-2">
                                <label for="emergency_contact" class="text-sm font-semibold text-gray-600">Emergency
                                    Contact</label>
                                <input type="text" placeholder="Emergency Contact" v-model="resident.emergency_contact"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>

                            <!-- Contact Person -->
                            <div class="flex flex-col gap-2">
                                <label for="contact_person" class="text-sm font-semibold text-gray-600">Contact
                                    Person</label>
                                <input type="text" placeholder="Contact Person" v-model="resident.contact_person"
                                    class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-center mt-10 gap-4">
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all transform hover:scale-105 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save
                            </button>
                            <router-link to="/residents"
                                class="bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </router-link>
                        </div>
                    </div>
                </div>
            </form>
        </template>
    </div>
</template>
