<script setup>
import { AuthLayout } from '@/Layouts'
import { ref } from 'vue'

const totalResidents = ref(1250);
const documentsProcessed = ref(890);
const activeComplaints = ref(34);
const ongoingProjects = ref(12);

const recentActivities = ref([
    { description: 'John Doe updated profile', time: '10 mins ago' },
    { description: 'New project "Water System" created', time: '1 hour ago' },
    { description: 'Complaint #123 resolved', time: 'Yesterday' }
]);

const latestProjects = ref([
    { name: 'Community Park Renovation', startDate: '2024-07-01' },
    { name: 'Road Improvement Phase 2', startDate: '2024-06-15' },
    { name: 'Health Clinic Expansion', startDate: '2024-05-20' }
]);

</script>

<template>
    <AuthLayout>
        <div class="px-3 sm:px-6 mt-3 sm:mt-5">
            <div class="mb-4 sm:mb-6">
                <h1 class="text-lg sm:text-2xl uppercase font-bold tracking-wide text-gray-900">Dashboard</h1>
                <h2 class="mt-1 sm:mt-2 text-gray-600 text-sm sm:text-lg">Welcome back, Admin!</h2>
            </div>

            <!-- Summary Cards - Mobile-first grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
                <!-- Card 1: Total Residents -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center bg-white rounded-lg shadow-sm sm:shadow-md p-3 sm:p-5 sm:space-x-4 hover:shadow-md transition-shadow cursor-pointer">
                    <div class="text-green-700 mb-2 sm:mb-0 self-center sm:self-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-10 sm:w-10" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a4 4 0 00-3-3.87m-4 5H7v-2a4 4 0 013-3.87M6 8a4 4 0 100-8 4 4 0 000 8zm12 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Residents</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ totalResidents }}</p>
                    </div>
                </div>

                <!-- Card 2: Documents Processed -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center bg-white rounded-lg shadow-sm sm:shadow-md p-3 sm:p-5 sm:space-x-4 hover:shadow-md transition-shadow cursor-pointer">
                    <div class="text-blue-600 mb-2 sm:mb-0 self-center sm:self-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-10 sm:w-10" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v9a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Documents</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ documentsProcessed }}</p>
                    </div>
                </div>

                <!-- Card 3: Active Complaints -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center bg-white rounded-lg shadow-sm sm:shadow-md p-3 sm:p-5 sm:space-x-4 hover:shadow-md transition-shadow cursor-pointer">
                    <div class="text-red-600 mb-2 sm:mb-0 self-center sm:self-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-10 sm:w-10" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 00-3.41 0zM12 9v4m0 4h.01" />
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Complaints</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ activeComplaints }}</p>
                    </div>
                </div>

                <!-- Card 4: Ongoing Projects -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center bg-white rounded-lg shadow-sm sm:shadow-md p-3 sm:p-5 sm:space-x-4 hover:shadow-md transition-shadow cursor-pointer">
                    <div class="text-purple-600 mb-2 sm:mb-0 self-center sm:self-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-10 sm:w-10" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9v6M12 3v18M7 9v6" />
                            <circle cx="7" cy="9" r="2" />
                            <circle cx="12" cy="12" r="2" />
                            <circle cx="17" cy="15" r="2" />
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Projects</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ ongoingProjects }}</p>
                    </div>
                </div>
            </div>

            <!-- Lower Panels - Stack on mobile -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Recent Activities -->
                <section class="bg-white rounded-lg shadow-sm sm:shadow-md p-4 sm:p-6 flex flex-col">
                    <div class="flex justify-between items-center mb-4 sm:mb-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-800">Recent Activities</h2>
                        <a href="/activities" class="text-blue-600 text-xs sm:text-sm hover:underline font-medium">View
                            All</a>
                    </div>
                    <ul class="overflow-y-auto max-h-48 sm:max-h-64 divide-y divide-gray-100">
                        <li v-for="(activity, index) in recentActivities" :key="index" class="py-2 sm:py-3">
                            <p class="text-gray-700 text-xs sm:text-sm">{{ activity.description }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ activity.time }}</p>
                        </li>
                        <li v-if="recentActivities.length === 0"
                            class="text-gray-400 text-center py-4 sm:py-6 text-xs sm:text-sm">
                            No recent activities
                        </li>
                    </ul>
                </section>

                <!-- Latest Projects -->
                <section class="bg-white rounded-lg shadow-sm sm:shadow-md p-4 sm:p-6 flex flex-col">
                    <div class="flex justify-between items-center mb-4 sm:mb-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-800">Latest Projects</h2>
                        <a href="/projects" class="text-blue-600 text-xs sm:text-sm hover:underline font-medium">View
                            All</a>
                    </div>
                    <ul class="overflow-y-auto max-h-48 sm:max-h-64 divide-y divide-gray-100">
                        <li v-for="(project, index) in latestProjects" :key="index" class="py-2 sm:py-3">
                            <p class="text-gray-700 font-medium text-xs sm:text-sm">{{ project.name }}</p>
                            <p class="text-xs text-gray-400 mt-1">Started: {{ project.startDate }}</p>
                        </li>
                        <li v-if="latestProjects.length === 0"
                            class="text-gray-400 text-center py-4 sm:py-6 text-xs sm:text-sm">
                            No recent projects
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </AuthLayout>
</template>