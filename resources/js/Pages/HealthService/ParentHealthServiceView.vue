<script setup>
import { ref } from 'vue';
import { AuthLayout } from '@/Layouts'
import { useRoute } from 'vue-router';
import {
    ChartPieIcon,
    CubeIcon,
    MegaphoneIcon,
    Cog6ToothIcon,
    PlusIcon,
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
        <div class="m-3 flex flex-col">
            <div class="bg-white p-5 shadow-lg rounded-lg w-1/4">
                <h1 class="uppercase font-bold">Health Service Manager</h1>
                <p class="text-gray-600 text-sm">
                    Oversee announcement and health data.
                </p>
            </div>
            <div
                class="flex bg-white shadow-lg rounded-lg p-5 mt-5 transform -translate-y-10 items-center justify-between">
                <div class="flex items-center space-x-2">
                    <router-link :to="{ path: 'dashboard' }"
                        class="flex items-center px-4 py-2 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                        active-class="bg-green-700 text-white">
                        <ChartPieIcon class="w-5 h-5 mr-2" />
                        Dashboard
                    </router-link>

                    <router-link :to="{ name: 'Clinic Visits' }"
                        class="flex items-center px-4 py-2 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                        active-class="bg-green-700 text-white">
                        <ClipboardDocumentListIcon class="w-5 h-5 mr-2" />
                        Clinic Visits
                    </router-link>

                    <router-link :to="{ name: 'Immunizations' }"
                        class="flex items-center px-4 py-2 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                        active-class="bg-green-700 text-white">
                        <ShieldCheckIcon class="w-5 h-5 mr-2" />
                        Immunizations
                    </router-link>

                    <router-link :to="{ name: 'Maternal & Child Health' }"
                        class="flex items-center px-4 py-2 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                        active-class="bg-green-700 text-white">
                        <UserGroupIcon class="w-5 h-5 mr-2" />
                        MCH
                    </router-link>
                    <router-link :to="{ name: 'Community Programs' }"
                        class="flex items-center px-4 py-2 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                        active-class="bg-green-700 text-white">
                        <BuildingLibraryIcon class="w-5 h-5 mr-2" />
                        Programs
                    </router-link>
                    <router-link :to="{ name: 'Health Reports' }"
                        class="flex items-center px-4 py-2 rounded-lg text-gray-700 font-semibold transition-colors duration-200 hover:bg-green-800 hover:text-white"
                        active-class="bg-green-700 text-white">
                        <DocumentChartBarIcon class="w-5 h-5 mr-2" />
                        Reports
                    </router-link>
                </div>
            </div>
        </div>
        <router-view></router-view>
    </AuthLayout>
</template>