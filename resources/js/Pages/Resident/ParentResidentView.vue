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
        <!-- Header Row -->
        <div
            class="m-4 flex flex-col md:flex-row items-start md:items-center justify-between bg-white p-4 md:p-6 shadow-md rounded-lg gap-4">
            <!-- Resident Manager Info (LEFT) -->
            <div class="flex-1">
                <h1 class="uppercase font-bold text-xl md:text-2xl mb-1">Resident Manager</h1>
                <p class="text-gray-600 text-sm md:text-base">Manage and View Resident Information</p>
            </div>

            <!-- Add Resident Button (RIGHT) -->
            <div class="flex-shrink-0">
                <template v-if="router.currentRoute.value.path === '/residents/list-residents'">
                    <router-link to="/residents/add-resident">
                        <button
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 md:py-3 px-3 md:px-5 rounded-lg shadow transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                            <PlusIcon class="w-5 h-5 md:w-6 md:h-6" />
                            <span class="hidden md:inline">Add Resident</span>
                        </button>
                    </router-link>
                </template>
            </div>
        </div>

        <!-- Page Content -->
        <div class="m-4">
            <router-view></router-view>
        </div>
    </AuthLayout>
</template>
