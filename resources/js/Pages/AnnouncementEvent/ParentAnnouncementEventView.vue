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
        <div class="m-5">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-semibold">Announcements & Events</h1>
                <router-link to="/announcement-events/add-announcement-event"
                    class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 flex items-center gap-2">
                    <PlusIcon class="w-5 h-5" />
                    <span>New</span>
                </router-link>
            </div>

            <!-- Cards -->
            <div class="grid grid-cols-2 gap-2 sm:gap-4 lg:gap-6 mb-4 sm:mb-8">
                <!-- Total -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm group flex flex-col gap-2">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Total
                        <MegaphoneIcon class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ totalEvents }}</div>
                </div>

                <!-- Upcoming -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm group flex flex-col gap-2">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Upcoming
                        <CalendarDaysIcon class="w-5 h-5 text-blue-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ upcoming }}</div>
                </div>

                <!-- Ongoing -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm group flex flex-col gap-2">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Ongoing
                        <ClockIcon class="w-5 h-5 text-yellow-500 animate-spin" />
                    </div>
                    <div class="text-3xl font-bold">{{ ongoing }}</div>
                </div>

                <!-- Past -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm group flex flex-col gap-2">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Past
                        <CheckCircleIcon class="w-5 h-5 text-green-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ past }}</div>
                </div>
            </div>


            <!-- Nested List View -->
            <router-view />
        </div>
    </AuthLayout>
</template>
