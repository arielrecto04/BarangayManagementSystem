<script setup>
import { onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useOfficialStore } from '@/Stores';
import useToast from '@/Utils/useToast';

const router = useRouter();
const route = useRoute();
const { showToast } = useToast();

const officialStore = useOfficialStore();
const { official, isLoading } = storeToRefs(officialStore);

const officialId = route.params.id;

const updateOfficialData = async () => {
    try {
        await officialStore.updateOfficial(official.value); // pass the data if your store requires it
        showToast({ icon: 'success', title: 'Official updated successfully' });
        router.push('/officials');
    } catch (error) {
        showToast({ icon: 'error', title: error.message || 'Failed to update official' });
    }
};

onMounted(() => {
    officialStore.getOfficialById(officialId);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <template v-if="isLoading || !official">
            <div class="flex justify-center items-center">
                <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
            </div>
        </template>

        <template v-else>
            <form @submit.prevent="updateOfficialData">
                <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
                    <h1 class="text-2xl font-bold mb-6">Edit Official</h1>
                    <h2 class="text-lg font-semibold mb-4">Official Profile</h2>

                    <div class="grid grid-cols-3 gap-4">
                        <!-- First Row -->
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-gray-600">First Name</label>
                            <input type="text" placeholder="First Name" v-model="official.first_name"
                                class="border border-gray-200 rounded-md px-4 py-2" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-gray-600">Last Name</label>
                            <input type="text" placeholder="Last Name" v-model="official.last_name"
                                class="border border-gray-200 rounded-md px-4 py-2" />
                        </div>

                        <div class="row-span-3 flex justify-center items-center">
                            <!-- Placeholder or future image preview -->
                            <div class="w-32 h-32 bg-gray-200 rounded-md flex justify-center items-center text-gray-400">
                                Image
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-gray-600">Position</label>
                            <input type="text" placeholder="Position" v-model="official.position"
                                class="border border-gray-200 rounded-md px-4 py-2" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-gray-600">Contact No.</label>
                            <input type="text" placeholder="Contact No." v-model="official.contact_number"
                                class="border border-gray-200 rounded-md px-4 py-2" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-gray-600">Gender</label>
                            <select v-model="official.gender" class="border border-gray-200 rounded-md px-4 py-2">
                                <option value="" disabled>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-center mt-10 gap-4">
                        <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md">
                            Save
                        </button>
                        <router-link to="/officials"
                            class="bg-white px-6 py-2 rounded-xl shadow-xl font-bold hover:bg-gray-200">
                            Cancel
                        </router-link>
                    </div>
                </div>
            </form>
        </template>
    </div>
</template>
