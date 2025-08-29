<script setup>
import { ref } from 'vue';
import { AuthLayout } from '@/Layouts'
import { useRoute } from 'vue-router';
import {
    ChartPieIcon,
    ClipboardDocumentListIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    BuildingLibraryIcon,
    DocumentChartBarIcon,
} from "@heroicons/vue/24/outline";

const route = useRoute();
</script>

<template>
    <AuthLayout>
        <div class="m-3 flex flex-col space-y-4">
            <!-- Header Card -->
            <div class="bg-white p-4 sm:p-5 shadow-lg rounded-lg w-full md:w-1/4">
                <h1 class="uppercase font-bold text-lg sm:text-xl">Health Service Manager</h1>
                <p class="text-gray-600 text-sm sm:text-base">
                    Manage Health Records and Announcements
                </p>
            </div>

            <!-- Navigation Grid: 2 columns on mobile -->
            <div
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2 bg-white shadow-lg rounded-lg p-3 sm:p-5 mt-0 md:mt-5 -translate-y-0 md:-translate-y-10">
                <router-link :to="{ path: 'dashboard' }"
                    class="flex flex-col items-center justify-center px-3 py-4 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                    active-class="bg-green-700 text-white">
                    <ChartPieIcon class="w-6 h-6 mb-1" />
                    <span class="text-xs text-center">Dashboard</span>
                </router-link>

                <router-link :to="{ name: 'Clinic Visits' }"
                    class="flex flex-col items-center justify-center px-3 py-4 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                    active-class="bg-green-700 text-white">
                    <ClipboardDocumentListIcon class="w-6 h-6 mb-1" />
                    <span class="text-xs text-center">Clinic Visits</span>
                </router-link>

                <router-link :to="{ name: 'Immunizations' }"
                    class="flex flex-col items-center justify-center px-3 py-4 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                    active-class="bg-green-700 text-white">
                    <ShieldCheckIcon class="w-6 h-6 mb-1" />
                    <span class="text-xs text-center">Immunizations</span>
                </router-link>

                <router-link :to="{ name: 'Maternal & Child Health' }"
                    class="flex flex-col items-center justify-center px-3 py-4 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                    active-class="bg-green-700 text-white">
                    <UserGroupIcon class="w-6 h-6 mb-1" />
                    <span class="text-xs text-center">MCH</span>
                </router-link>

                <router-link :to="{ name: 'Community Programs' }"
                    class="flex flex-col items-center justify-center px-3 py-4 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                    active-class="bg-green-700 text-white">
                    <BuildingLibraryIcon class="w-6 h-6 mb-1" />
                    <span class="text-xs text-center">Programs</span>
                </router-link>

                <router-link :to="{ name: 'Health Reports' }"
                    class="flex flex-col items-center justify-center px-3 py-4 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                    active-class="bg-green-700 text-white">
                    <DocumentChartBarIcon class="w-6 h-6 mb-1" />
                    <span class="text-xs text-center">Reports</span>
                </router-link>
            </div>
        </div>

        <router-view></router-view>
    </AuthLayout>
</template>
