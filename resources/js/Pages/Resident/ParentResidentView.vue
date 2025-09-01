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
        <div class="p-2 sm:p-5 lg:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-6 gap-2 sm:gap-0">
                <div>
                    <h1 class="text-base sm:text-xl lg:text-2xl font-semibold text-gray-900">
                        Resident Manager
                    </h1>
                    <p class="text-gray-600 text-xs sm:text-sm lg:text-base">
                        Manage and View Resident Information
                    </p>
                </div>

                <!-- Add Resident Button -->
                <template v-if="router.currentRoute.value.path === '/residents/list-residents'">
                    <router-link to="/residents/add-resident"
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 sm:py-2 rounded-md shadow transition-colors flex items-center justify-center gap-1.5 text-xs sm:text-sm font-medium">
                        <PlusIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                        <span>Add</span>
                    </router-link>
                </template>
            </div>

            <!-- Nested List Resident View -->
            <router-view />
        </div>
    </AuthLayout>
</template>
