<script setup>
import { AuthLayout } from "@/Layouts";
import { useAnnouncementEventStore } from "@/Stores";
import { storeToRefs } from "pinia";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import {
    CalendarDaysIcon,
    MegaphoneIcon,
    ClockIcon,
    CheckCircleIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const announcementEventStore = useAnnouncementEventStore();
const { events } = storeToRefs(announcementEventStore);

// Total events
const totalEvents = computed(() => events.value.length);

// Navigate to list view with status filter
const filterByStatus = async (status) => {
    await announcementEventStore.getEvents();
    router.push({ path: "/announcement-events/list", query: { status } });
};

// Statistics
const upcoming = computed(() =>
    events.value.filter((e) => e.status === "Upcoming").length
);
const ongoing = computed(() =>
    events.value.filter((e) => e.status === "Ongoing").length
);
const past = computed(() =>
    events.value.filter((e) => e.status === "Past").length
);

// Auto-refresh events
onMounted(async () => {
    await announcementEventStore.getEvents();
    setInterval(async () => {
        await announcementEventStore.refreshStatuses();
    }, 300000); // 5 minutes
});

// Optional: handler to manually refresh stats
function handleStatusUpdated() {
    announcementEventStore.getEvents();
}
</script>

<template>
    <AuthLayout>
        <div class="p-2 sm:p-5 lg:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-6 gap-2 sm:gap-0">
                <div>
                    <h1 class="text-base sm:text-xl lg:text-2xl font-semibold text-gray-900">Announcement and Event</h1>
                    <p class="text-gray-600">Create, Manage, and Share Community Announcements and Events</p>
                </div>
                <router-link to="/announcement-events/add-announcement-event"
                    class="bg-black text-white px-3 py-1.5 sm:py-2 rounded-md hover:bg-gray-800 transition-colors flex items-center justify-center gap-1.5 text-xs sm:text-sm font-medium">
                    <PlusIcon class="w-3 h-3 sm:w-4 sm:h-4" />
                    <span>New</span>
                </router-link>
            </div>

            <!-- Cards Grid - Mobile-First 2x2 Layout -->
            <div class="grid grid-cols-2 gap-2 sm:gap-4 lg:gap-6 mb-4 sm:mb-8">
                <!-- Total -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Total</div>
                        <MegaphoneIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-blue-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ totalEvents }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">All events</div>
                </div>

                <!-- Upcoming -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Upcoming</div>
                        <CalendarDaysIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-orange-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ upcoming }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Scheduled events</div>
                </div>

                <!-- Ongoing -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Ongoing</div>
                        <ClockIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-yellow-500 animate-spin" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ ongoing }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Currently active</div>
                </div>

                <!-- Past -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Past</div>
                        <CheckCircleIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-green-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ past }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Completed events</div>
                </div>
            </div>

            <!-- Nested List View -->
            <router-view />
        </div>
    </AuthLayout>
</template>
