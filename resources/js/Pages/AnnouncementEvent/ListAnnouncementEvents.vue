<script setup>
import { Table, Paginate } from '@/Components';
import Modal from '../../Components/Modal.vue';
import { useAnnouncementEventStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref, nextTick } from "vue";
import useToast from '@/Utils/useToast';
import { useRoute, useRouter } from 'vue-router';

// Store
const announcementEventStore = useAnnouncementEventStore();
const { events, isLoading, paginate } = storeToRefs(announcementEventStore);

const { showToast } = useToast();
const route = useRoute();
const router = useRouter();

// State
const currentPage = ref(route.query.page || 1);
const menuPosition = ref({ top: 0, left: 0 });
const teleportMenuRowId = ref(null);
const showModal = ref(false);
const selectedEvent = ref(null);

// Helpers
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};

// Actions
const handlePageChange = (page) => {
    currentPage.value = page;
    announcementEventStore.getEvents(page);
    router.replace({ query: { page } });
};

const toggleMenu = async (event, eventId) => {
    if (teleportMenuRowId.value === eventId) {
        teleportMenuRowId.value = null;
        return;
    }
    if (teleportMenuRowId.value !== null) {
        teleportMenuRowId.value = null;
        await nextTick();
    }

    const rect = event.currentTarget.getBoundingClientRect();
    const dropdownWidth = 192;
    const viewportWidth = window.innerWidth;
    const scrollX = window.scrollX;

    let leftPosition = rect.left + scrollX;
    if (rect.left + dropdownWidth > viewportWidth) {
        leftPosition = rect.left + scrollX - dropdownWidth + rect.width;
    }
    leftPosition = Math.max(leftPosition, 0);

    menuPosition.value = {
        top: rect.bottom + window.scrollY,
        left: leftPosition,
    };

    await nextTick();
    teleportMenuRowId.value = eventId;
};

const closeMenu = () => {
    teleportMenuRowId.value = null;
};

const handleClickOutside = (event) => {
    const target = event.target;
    if (!target.closest('.burger-menu-container') &&
        !target.closest('[data-teleport-menu]') &&
        teleportMenuRowId.value !== null) {
        teleportMenuRowId.value = null;
    }
};

const openModal = (eventData) => {
    selectedEvent.value = eventData;
    showModal.value = true;
    closeMenu();
};

const closeModal = () => {
    showModal.value = false;
    selectedEvent.value = null;
};

const editEvent = (eventId) => {
    router.push(`/announcement-events/edit-announcement-event/${eventId}`);
    closeMenu();
};

const deleteEvent = async (eventId) => {
    if (confirm('Are you sure you want to delete this announcement/event?')) {
        try {
            await announcementEventStore.deleteEvent(eventId);
            showToast({ icon: 'success', title: 'Event deleted successfully' });
            announcementEventStore.getEvents(currentPage.value);
            closeMenu();
        } catch (error) {
            showToast({ icon: 'error', title: error.message });
        }
    }
};

// Table Columns
const columns = [
    { key: "type", label: "Type" },
    { key: "title", label: "Title" },
    { key: "author", label: "Author" },
    { key: "location", label: "Location" },
    { key: "formatted_start_date", label: "Start Date" },
    { key: "formatted_end_date", label: "End Date" },
    { key: "status", label: "Status" }
];

onMounted(() => {
    const page = currentPage.value;
    announcementEventStore.getEvents(page);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-6 lg:py-8 overflow-x-hidden">

            <!-- Loader -->
            <div v-if="isLoading" class="flex justify-center items-center py-20">
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-indigo-200 rounded-full animate-spin"></div>
                    <div
                        class="w-16 h-16 border-4 border-indigo-600 rounded-full animate-spin absolute top-0 left-0 border-t-transparent border-r-transparent">
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="events.length === 0" class="bg-white rounded-2xl p-8 text-center shadow-sm">
                <div class="mx-auto h-24 w-24 bg-indigo-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-xl font-medium text-gray-900">No announcements or events yet</h3>
                <p class="mt-2 text-gray-500">Get started by creating your first announcement or event</p>
                <div class="mt-6">
                    <router-link to="/announcements-events/add"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create New
                    </router-link>
                </div>
            </div>

            <!-- Card Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                <div v-for="event in events" :key="event.id"
                    class="group relative bg-white/80 backdrop-blur-sm border border-white/50 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col min-h-[400px]">

                    <!-- Image Section -->
                    <div class="relative h-40 sm:h-44 md:h-48 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent z-10"></div>
                        <img v-if="event.image" :src="`/${event.image}`" :alt="event.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div v-else
                            class="w-full h-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center">
                            <div class="text-white text-6xl opacity-30">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <!-- Action Menu Button -->
                        <div class="absolute top-3 right-3 burger-menu-container z-20">
                            <button @click="(e) => toggleMenu(e, event.id)"
                                class="w-9 h-9 bg-white/90 hover:bg-white border border-white/50 shadow rounded-full flex items-center justify-center transition-all duration-300 group-hover:shadow-md"
                                :class="{ 'bg-white shadow-md': teleportMenuRowId === event.id }">
                                <svg class="w-4.5 h-4.5 text-slate-700" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="4" cy="10" r="2" />
                                    <circle cx="10" cy="10" r="2" />
                                    <circle cx="16" cy="10" r="2" />
                                </svg>
                            </button>
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-3 left-3 z-20">
                            <span :class="{
                                'bg-emerald-500/90 text-white': event.status === 'Ongoing',
                                'bg-amber-500/90 text-white': event.status === 'Upcoming',
                                'bg-slate-500/90 text-white': event.status === 'Past'
                            }"
                                class="backdrop-blur-sm px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow">
                                {{ event.status }}
                            </span>
                        </div>

                        <!-- Type Badge -->
                        <div class="absolute bottom-3 left-3 z-20">
                            <span :class="[
                                event.type === 'announcement' ? 'bg-blue-500/90 text-white' : 'bg-purple-500/90 text-white',
                                'backdrop-blur-sm px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow'
                            ]">
                                {{ event.type }}
                            </span>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="p-4 sm:p-5 space-y-3 flex-grow flex flex-col">
                        <!-- Title -->
                        <h3
                            class="font-bold text-lg md:text-xl text-slate-900 group-hover:text-indigo-700 transition-colors duration-300 line-clamp-2">
                            {{ event.title }}
                        </h3>

                        <!-- Author & Location -->
                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <div v-if="event.author" class="flex items-center gap-2 min-w-0">
                                <div
                                    class="shrink-0 w-7 h-7 md:w-8 md:h-8 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs">
                                    {{ event.author.charAt(0).toUpperCase() }}
                                </div>
                                <span class="font-semibold text-slate-700 truncate min-w-0">{{ event.author }}</span>
                            </div>
                            <div v-if="event.location" class="flex items-center gap-1 text-slate-500 min-w-0">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1113.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="truncate min-w-0">{{ event.location }}</span>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="shrink-0 w-3 h-3 rounded-full bg-emerald-400"></div>
                                <span class="font-medium text-slate-600 shrink-0">Start:</span>
                                <span class="text-slate-900 truncate">{{ formatDate(event.start_date) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="shrink-0 w-3 h-3 rounded-full bg-rose-400"></div>
                                <span class="font-medium text-slate-600 shrink-0">End:</span>
                                <span class="text-slate-900 truncate">{{ formatDate(event.end_date) }}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="text-slate-600 text-sm leading-relaxed line-clamp-3 mt-auto pt-2">
                            {{ event.description && event.description.length > 120
                                ? event.description.slice(0, 120) + '…'
                                : event.description || 'No description available' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Dropdown Menu (Teleport) -->
            <Teleport to="body">
                <div v-if="teleportMenuRowId !== null" data-teleport-menu :style="{
                    position: 'absolute',
                    top: menuPosition.top + 'px',
                    left: menuPosition.left + 'px',
                    zIndex: 9999
                }"
                    class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-white/50 py-2 w-48 animate-in fade-in duration-200">
                    <button @click="editEvent(teleportMenuRowId)"
                        class="w-full text-left px-4 py-3 text-sm text-slate-700 hover:bg-indigo-50 flex items-center gap-3 font-medium transition-colors duration-200 rounded-xl mx-2">
                        <span class="text-lg">✏️</span>
                        Edit Event
                    </button>
                    <hr class="my-2 border-slate-200">
                    <button @click="deleteEvent(teleportMenuRowId)"
                        class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center gap-3 font-medium transition-colors duration-200 rounded-xl mx-2">
                        <span class="text-lg">🗑️</span>
                        Delete Event
                    </button>
                </div>
            </Teleport>

            <!-- Pagination -->
            <div v-if="paginate && !isLoading" class="mt-8 sm:mt-10 flex justify-center">
                <div class="bg-white/70 backdrop-blur-sm border border-white/50 rounded-2xl p-2">
                    <Paginate @page-changed="handlePageChange" :maxVisibleButtons="5" :totalPages="paginate.last_page"
                        :totalItems="paginate.total" :currentPage="paginate.current_page"
                        :itemsPerPage="paginate.per_page" />
                </div>
            </div>

            <!-- Modal -->
            <Modal :show="showModal" title="Announcement / Event Details" max-width="3xl" @close="closeModal">
                <!-- Your detailed modal content goes here -->
            </Modal>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in.fade-in {
    animation: fade-in 0.2s ease-out;
}

.min-w-0 {
    min-width: 0;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>