<script setup>
import { AuthLayout } from "@/Layouts";
import { useAnnouncementEventStore } from "@/Stores";
import { storeToRefs } from "pinia";
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import {
    CalendarDaysIcon,
    MegaphoneIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const announcementEventStore = useAnnouncementEventStore();
const { announcementEvents } = storeToRefs(announcementEventStore);

const totalItems = computed(() => announcementEvents.value?.length || 0);

const upcoming = computed(() =>
    (announcementEvents.value || []).filter(e => e && e.status === "Upcoming").length
);
const ongoing = computed(() =>
    (announcementEvents.value || []).filter(e => e && e.status === "Ongoing").length
);
const past = computed(() =>
    (announcementEvents.value || []).filter(e => e && e.status === "Past").length
);


// Navigate to list view with filter
const filterByStatus = async (status) => {
    await announcementEventStore.getAnnouncementEvents();
    router.push({ path: "/announcement-events/list-announcements-events", query: { status } });
};

// Initial load
onMounted(() => {
    announcementEventStore.getAnnouncementEvents();
});
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

            <!-- Stats Cards -->
            <div class="bg-gray-100 p-5 rounded-lg grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Total -->
                <div class="bg-white rounded-xl p-5 shadow flex flex-col gap-2 cursor-pointer"
                    @click="filterByStatus('')">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Total
                        <MegaphoneIcon class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ totalItems }}</div>
                </div>

                <!-- Upcoming -->
                <div class="bg-white rounded-xl p-5 shadow flex flex-col gap-2 cursor-pointer"
                    @click="filterByStatus('Upcoming')">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Upcoming
                        <CalendarDaysIcon class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ upcoming }}</div>
                </div>

                <!-- Ongoing -->
                <div class="bg-white rounded-xl p-5 shadow flex flex-col gap-2 cursor-pointer"
                    @click="filterByStatus('Ongoing')">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Ongoing
                        <ArrowPathIcon class="w-5 h-5 text-gray-500 animate-spin" />
                    </div>
                    <div class="text-3xl font-bold">{{ ongoing }}</div>
                </div>

                <!-- Past -->
                <div class="bg-white rounded-xl p-5 shadow flex flex-col gap-2 cursor-pointer"
                    @click="filterByStatus('Past')">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Past
                        <CheckCircleIcon class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ past }}</div>
                </div>
            </div>

            <!-- Nested View -->
            <router-view />
        </div>
    </AuthLayout>
</template>
