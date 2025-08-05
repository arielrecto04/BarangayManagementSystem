<script setup>
import { AuthLayout } from "@/Layouts";
import { useRouter, useRoute } from "vue-router";
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { ref, watch } from 'vue';
import { useResidentStore } from '@/Stores';

const router = useRouter();
const route = useRoute();
const residentStore = useResidentStore();

// Search and filter states
const searchQuery = ref('');
const selectedAgeRange = ref('');
const selectedGender = ref('');

// Debounced search function
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500); // 500ms delay
};

// Apply filters and search
const applyFilters = () => {
    const filters = {};

    if (searchQuery.value.trim()) {
        filters.search = searchQuery.value.trim();
    }

    if (selectedAgeRange.value) {
        filters.age_range = selectedAgeRange.value;
    }

    if (selectedGender.value) {
        filters.gender = selectedGender.value;
    }

    // Update the route with filters
    router.replace({
        query: {
            ...route.query,
            page: 1, // Reset to first page when filtering
            ...filters
        }
    });
};

// Clear all filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedAgeRange.value = '';
    selectedGender.value = '';

    router.replace({
        query: {
            page: 1
        }
    });
};

// Watch for route changes to update local filter states
watch(() => route.query, (newQuery) => {
    searchQuery.value = newQuery.search || '';
    selectedAgeRange.value = newQuery.age_range || '';
    selectedGender.value = newQuery.gender || '';
}, { immediate: true });
</script>

<template>
    <AuthLayout>
        <div class="m-3 flex flex-col">
            <!-- Resident Manager -->
            <div class="bg-white p-5 shadow-lg rounded-lg w-1/4">
                <h1 class="uppercase font-bold">Resident Manager</h1>
                <p>Manage and View Resident information</p>
            </div>

            <!-- Search Residents -->
            <div class="flex bg-white shadow-lg rounded-lg p-7 mt-0 transform -translate-y-10">
                <div class="shadow-lg p-3 w-1/2">
                    <form @submit.prevent="applyFilters">
                        <input type="text" placeholder="Search Residents..." name="Search Residents"
                            v-model="searchQuery" @input="handleSearch"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-blue-500">
                    </form>
                </div>

                <select v-model="selectedAgeRange" @change="applyFilters"
                    class="ml-2 bg-white shadow-lg rounded-lg w-1/8 p-3 text-center border border-gray-300 focus:outline-none focus:border-blue-500">
                    <option value="">All Ages</option>
                    <option value="18-25">18 - 25</option>
                    <option value="26-35">26 - 35</option>
                    <option value="36-45">36 - 45</option>
                    <option value="46-60">46 - 60</option>
                    <option value="60+">60+</option>
                </select>

                <select v-model="selectedGender" @change="applyFilters"
                    class="flex items-center ml-2 bg-white shadow-lg rounded-lg w-1/8 p-3 text-center border border-gray-300 focus:outline-none focus:border-blue-500">
                    <option value="">All Genders</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Not Specified">Not Specified</option>
                </select>

                <!-- Clear Filters Button -->
                <button v-if="searchQuery || selectedAgeRange || selectedGender" @click="clearFilters"
                    class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
                    type="button">
                    Clear
                </button>

                <div class="w-1/4 max-w-sm mx-auto ml-2">
                    <!-- Route of Add Resident -->
                    <template v-if="router.currentRoute.value.path == '/residents/list-residents'">
                        <router-link to="/residents/add-resident" class="block">
                            <div
                                class="bg-green-700 shadow-md rounded-lg p-3 cursor-pointer flex justify-center items-center hover:bg-green-800 transition-colors">
                                <div class="flex items-center gap-2 text-lg font-bold text-center text-white">
                                    <PlusIcon class="w-6 h-6" /> Add Resident
                                </div>
                            </div>
                        </router-link>
                    </template>
                    <template v-else>
                        <router-link to="/residents" class="block">
                            <div
                                class="shadow-md rounded-lg p-3 cursor-pointer flex justify-center items-center hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-2 text-lg font-bold text-center">
                                    <XMarkIcon class="w-6 h-6" /> Cancel
                                </div>
                            </div>
                        </router-link>
                    </template>
                </div>
            </div>
        </div>

        <router-view></router-view>
    </AuthLayout>
</template>