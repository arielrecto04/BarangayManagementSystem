<script setup>
import { useOfficialStore } from '@/Stores'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';
import { storeToRefs } from 'pinia';

const router = useRouter();
const { showToast } = useToast();

const officialStore = useOfficialStore();

const { official, isLoading } = storeToRefs(officialStore);

const officialId = router.currentRoute.value.params.id;



const updateOfficialData  = async () => {
    try {
        await officialStore.updateOfficial();
        showToast({ icon: 'success', title: 'Official updated successfully' });
        router.push('/officials');
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}


onMounted(() => {
    officialStore.getOfficialById(officialId);
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
            <form @submit.prevent="updateResidentData">
                <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
                    <h1 class="text-2xl font-bold mb-6">Edit Resident</h1>
                    <h2 class="text-lg font-semibold mb-4">Resident Profile</h2>

                    <div class="grid grid-cols-3 gap-4">
                        <!-- First Row -->

                        <div class="flex flex-col gap-2">
                            <label for="first_name" class="text-sm font-semibold text-gray-600">First Name</label>
                            <input type="text" placeholder="First Name" v-model="resident.first_name"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />

                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="last_name" class="text-sm font-semibold text-gray-600">Last Name</label>
                            <input type="text" placeholder="Last Name" v-model="resident.last_name"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="row-span-3 flex justify-center items-center">
                            <div class="w-50 h-50 bg-gray-200 rounded-md"></div>
                        </div>

                        <!-- Second Row -->
                        <div class="flex flex-col gap-2">
                            <label for="birthday" class="text-sm font-semibold text-gray-600">Birthday</label>
                            <input type="date" placeholder="Birthday" v-model="resident.birthday"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="age" class="text-sm font-semibold text-gray-600">Age</label>
                            <input type="number" placeholder="Age" v-model="resident.age"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="gender" class="text-sm font-semibold text-gray-600">Gender</label>
                            <select name="" id="" v-model="resident.gender"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2">
                                <option value="" selected disabled>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <!-- Third Row -->
                        <div class="flex flex-col gap-2">
                            <label for="address" class="text-sm font-semibold text-gray-600">Address</label>
                            <input type="text" placeholder="Lot no. / Street / Subdivision / Barangay"
                                v-model="resident.address"
                                class="input-style col-span-2 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <!-- <div class="flex flex-col gap-2">
                        <label for="contact_number" class="text-sm font-semibold text-gray-600">Image</label>
                        <button class="input-style bg-white  col-span-1 border border-gray-200 rounded-md px-4 py-2">Add
                        Image</button>
                    </div> -->

                        <!-- Fourth Row -->
                        <div class="flex flex-col gap-2">
                            <label for="contact_number" class="text-sm font-semibold text-gray-600">Contact No.</label>
                            <input type="text" placeholder="Contact No." v-model="resident.contact_number"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="family_member" class="text-sm font-semibold text-gray-600">Family Member</label>
                            <input type="text" placeholder="Family Member" v-model="resident.family_member"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="emergency_contact" class="text-sm font-semibold text-gray-600">Emergency
                                Contact</label>
                            <input type="text" placeholder="Emergency Contact" v-model="resident.emergency_contact"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                    </div>


                    <div class="flex justify-center mt-10 gap-4">

                        <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md">Save</button>
                        <router-link to="/residents"
                            class="bg-white px-6 py-2 rounded-xl shadow-xl ml-4 font-bold hover:bg-gray-200">Cancel</router-link>

                    </div>
                </div>
            </form>
        </template>
    </div>
</template>
