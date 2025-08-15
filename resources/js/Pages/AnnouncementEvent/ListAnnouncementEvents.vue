<script setup>
import { Table, Paginate } from "@/Components";
import { useAnnouncementEventStore } from "@/Stores";
import { storeToRefs } from "pinia";
import { onMounted, ref, computed, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const announcementEventStore = useAnnouncementEventStore();
const { announcementEvents, paginate } = storeToRefs(announcementEventStore);

const currentPage = ref(1);
const selectedType = ref(route.query.type || ""); // "", "Announcement", "Event"

// Fetch data
const getData = async () => {
    await announcementEventStore.getAnnouncementEvents(currentPage.value, selectedType.value);
};

watch(
    () => route.query,
    (newQuery) => {
        if (newQuery.newItem) {
            // Scroll to top to see the new item
            window.scrollTo({ top: 0, behavior: 'smooth' });
            // Remove the query param
            router.replace({ query: {} });
        }
    },
    { immediate: true }
);

onMounted(() => {
    getData();
});

// Filter change
const filterByType = (type) => {
    selectedType.value = type;
    router.push({ query: { type } });
    getData();
};

// Pagination change
const onPageChange = (page) => {
    currentPage.value = page;
    getData();
};

// Formatted date
const formatDate = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
};

// Empty state message based on selected filter
const emptyStateMessage = computed(() => {
    if (selectedType.value === "Announcement") {
        return {
            title: "No Announcements Found",
            message: "There are currently no announcements available.",
        };
    } else if (selectedType.value === "Event") {
        return {
            title: "No Events Found",
            message: "There are currently no events available.",
        };
    } else {
        return {
            title: "No Announcements or Events Found",
            message: "There are currently no announcements or events available.",
        };
    }
});
</script>

<template>
    <div class="mt-6">
        <!-- Filter buttons -->
        <div class="flex gap-2 mb-4">
            <button @click="filterByType('')"
                :class="['px-4 py-2 rounded', selectedType === '' ? 'bg-black text-white' : 'bg-gray-200 hover:bg-gray-300']">
                All
            </button>
            <button @click="filterByType('Announcement')"
                :class="['px-4 py-2 rounded', selectedType === 'Announcement' ? 'bg-black text-white' : 'bg-gray-200 hover:bg-gray-300']">
                Announcements
            </button>
            <button @click="filterByType('Event')"
                :class="['px-4 py-2 rounded', selectedType === 'Event' ? 'bg-black text-white' : 'bg-gray-200 hover:bg-gray-300']">
                Events
            </button>
        </div>

        <!-- Content Area -->
        <div v-if="!announcementEvents || announcementEvents.length === 0" class="text-center py-12">
            <div class="max-w-md mx-auto">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    {{ emptyStateMessage.title }}
                </h3>

                <p class="text-gray-500 mb-6">
                    {{ emptyStateMessage.message }}
                </p>
            </div>
        </div>

        <!-- Card Grid -->
        <div v-else>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div v-for="item in announcementEvents || []" :key="item?.id"
                    class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">

                    <!-- Card Header with Image -->
                    <div class="relative h-48 bg-gradient-to-r from-blue-500 to-purple-600 overflow-hidden">
                        <div v-if="!item?.image" class="w-full h-full flex items-center justify-center">
                            <div class="text-center text-white">
                                <svg v-if="item?.type === 'Announcement'" class="w-16 h-16 mx-auto mb-2 opacity-80"
                                    fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                </svg>
                                <svg v-else class="w-16 h-16 mx-auto mb-2 opacity-80" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zM19 19H5V8h14v11z" />
                                </svg>
                                <span class="text-sm font-medium opacity-90">{{ item?.type }}</span>
                            </div>
                        </div>
                        <img v-else :src="item.image" :alt="item.title || ''" class="w-full h-full object-cover">

                        <!-- Type Badge -->
                        <div class="absolute top-4 left-4">
                            <span :class="[
                                'px-3 py-1 rounded-full text-xs font-semibold text-white',
                                item?.type === 'Announcement' ? 'bg-blue-600 bg-opacity-90' : 'bg-green-600 bg-opacity-90'
                            ]">
                                {{ item?.type }}
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="absolute top-4 right-4 flex space-x-2">
                            <button
                                class="p-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full text-white transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <router-link :to="`/announcement-events/edit-announcement-event/${item?.id}`"
                                class="p-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full text-white transition-all duration-200 inline-block">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </router-link>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                            {{ item?.title || 'No Title' }}
                        </h3>

                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                            {{ item?.description || 'No description available.' }}
                        </p>
                    </div>

                    <!-- Card Footer -->
                    <div class="border-t border-gray-100">
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex items-center text-gray-500 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ formatDate(item?.date) }}</span>
                            </div>

                            <div v-if="item?.status" class="text-right">
                                <span :class="[
                                    'px-2 py-1 rounded text-xs font-medium',
                                    item.status === 'active' ? 'bg-green-100 text-green-800' :
                                        item.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-gray-100 text-gray-800'
                                ]">
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Paginate v-if="paginate?.last_page > 1" :pagination="paginate" @page-change="onPageChange" />
        </div>
    </div>
</template>
