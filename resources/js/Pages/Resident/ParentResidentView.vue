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
        <div class="m-4 flex flex-col items-start space-y-6">
            <!-- Resident Manager Info Card -->
            <div class="bg-white p-6 shadow-md rounded-lg w-full max-w-xs">
                <h1 class="uppercase font-bold text-xl mb-1">Resident Manager</h1>
                <p class="text-gray-600">Manage and View Resident Information</p>
            </div>

            <!-- Action Button (Add Resident or Cancel) -->
            <div class="w-full max-w-xs">
                <template v-if="router.currentRoute.value.path === '/residents/list-residents'">
                    <router-link to="/residents/add-resident" class="block">
                        <button
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg shadow transition-colors flex items-center justify-center gap-2">
                            <PlusIcon class="w-6 h-6" />
                            Add Resident
                        </button>
                    </router-link>
                </template>
                <template v-else>
                    <router-link to="/residents" class="block">
                        <button
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 rounded-lg shadow transition-colors flex items-center justify-center gap-2">
                            <XMarkIcon class="w-6 h-6" />
                            Cancel
                        </button>
                    </router-link>
                </template>
            </div>
        </div>

        <router-view></router-view>
    </AuthLayout>
</template>
