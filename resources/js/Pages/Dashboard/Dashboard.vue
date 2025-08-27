<script setup>
import { AuthLayout } from '@/Layouts'
import { ref, onMounted } from 'vue'
import { useResidentStore } from '@/Stores/useResidentStore'
import { useComplaintStore } from '@/Stores/useComplaintStore'
import { useBlotterStore } from '@/Stores/useBlotterStore'
import { useAnnouncementEventStore } from '@/Stores/useAnnouncementEventStore'

const residentStore = useResidentStore();
const complaintStore = useComplaintStore();
const blotterStore = useBlotterStore();
const announcementStore = useAnnouncementEventStore();

const totalResidents = ref(0);
const documentsProcessed = ref(890);
const activeComplaints = ref(0);
const ongoingProjects = ref(12);
const blottersCount = ref(0);
const announcementsCount = ref(0);

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

// Fetch data when component mounts
onMounted(async () => {
    try {
        console.log('Starting to fetch dashboard data...');

        // Residents
        await residentStore.getResidents(1);
        totalResidents.value = residentStore.paginate.total;
        console.log('Residents count:', totalResidents.value);

        // Complaints
        await complaintStore.getComplaints(1);
        activeComplaints.value = complaintStore.paginate?.total || 0;
        console.log('Complaints count:', activeComplaints.value);

        // Blotters
        await blotterStore.getBlotters(1);
        blottersCount.value = blotterStore.paginate?.total || 0;
        console.log('Blotters count:', blottersCount.value);

        // Announcements & Events - Try multiple approaches
        console.log('Fetching announcements count...');

        try {
            // Method 1: Try the dedicated count method
            const count = await announcementStore.getEventsCount();
            announcementsCount.value = count;
            console.log('Announcements count (method 1):', count);
        } catch (error) {
            console.error('Method 1 failed, trying method 2:', error);

            try {
                // Method 2: Fetch first page and get total from pagination
                await announcementStore.getEvents(1, 10);
                announcementsCount.value = announcementStore.paginate?.total || announcementStore.events?.length || 0;
                console.log('Announcements count (method 2):', announcementsCount.value);
                console.log('Pagination data:', announcementStore.paginate);
                console.log('Events data:', announcementStore.events);
            } catch (error2) {
                console.error('Method 2 also failed:', error2);

                // Method 3: Fallback to 0
                announcementsCount.value = 0;
                console.log('Using fallback count: 0');
            }
        }

    } catch (error) {
        console.error('Failed to fetch dashboard data:', error);
    }
});
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-6 w-6 sm:h-10 sm:w-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
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

                <!-- Card 4: Blotters -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center bg-white rounded-lg shadow-sm sm:shadow-md p-3 sm:p-5 sm:space-x-4 hover:shadow-md transition-shadow cursor-pointer">
                    <div class="text-yellow-600 mb-2 sm:mb-0 self-center sm:self-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-6 w-6 sm:h-10 sm:w-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Blotters</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ blottersCount }}</p>
                    </div>
                </div>

                <!-- Card 5: Ongoing Projects -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center bg-white rounded-lg shadow-sm sm:shadow-md p-3 sm:p-5 sm:space-x-4 hover:shadow-md transition-shadow cursor-pointer">
                    <div class="text-purple-600 mb-2 sm:mb-0 self-center sm:self-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-6 w-6 sm:h-10 sm:w-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Projects</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ ongoingProjects }}</p>
                    </div>
                </div>

                <!-- Card 6: Announcements & Events -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center bg-white rounded-lg shadow-sm sm:shadow-md p-3 sm:p-5 sm:space-x-4 hover:shadow-md transition-shadow cursor-pointer">
                    <div class="text-teal-600 mb-2 sm:mb-0 self-center sm:self-auto">
                        <!-- Heroicons Megaphone / Announcement Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-6 w-6 sm:h-10 sm:w-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Announcements & Events</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ announcementsCount }}</p>
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