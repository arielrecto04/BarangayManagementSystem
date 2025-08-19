<script setup>
import { AuthLayout } from "@/Layouts";
import { Table, Paginate } from '@/Components'
import { useResidentStore } from '@/Stores'
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref, watch, nextTick } from "vue";
import useToast from '@/Utils/useToast';
import { useRouter, useRoute } from 'vue-router';
import Modal from '../../Components/Modal.vue'

const { showToast } = useToast();
const router = useRouter();
const route = useRoute();
const residentStore = useResidentStore();
const { residents, isLoading, paginate } = storeToRefs(residentStore);

// For Viewing of Resident Information in Modal
const showModal = ref(false);
const selectedResident = ref(null);
const viewResident = (resident) => {
    selectedResident.value = resident;
    showModal.value = true;
};
const closeModal = () => {
    showModal.value = false;
    selectedResident.value = null;
};



const currentPage = ref(parseInt(route.query.page) || 1);


// Burger menu state (track which row’s menu is open)
const teleportMenuRowId = ref(null);
const menuPosition = ref({ top: 0, left: 0 });


const toggleMenu = async (event, residentId) => {
    if (teleportMenuRowId.value === residentId) {
        teleportMenuRowId.value = null;
        return;
    }

    if (teleportMenuRowId.value !== null) {
        teleportMenuRowId.value = null;
        await nextTick();
    }

    const rect = event.currentTarget.getBoundingClientRect();
    const dropdownWidth = 192; // Approx width (w-48)
    const viewportWidth = window.innerWidth;
    const scrollX = window.scrollX;

    let leftPosition = rect.left + scrollX;

    if (rect.left + dropdownWidth > viewportWidth) {
        leftPosition = rect.left + scrollX - dropdownWidth + rect.width;
    }

    leftPosition = Math.max(leftPosition, 0);

    menuPosition.value = {
        top: rect.bottom + window.scrollY,
        left: leftPosition
    };

    await nextTick();
    teleportMenuRowId.value = residentId;
};

const closeMenu = () => {
    teleportMenuRowId.value = null;
};


// Delete resident handler
const deleteResident = async (residentId) => {
    if (confirm('Are you sure you want to delete this resident?')) {
        try {
            await residentStore.deleteResident(residentId);
            showToast({ icon: 'success', title: 'Resident deleted successfully' });

            if (residents.value.length === 0 && currentPage.value > 1) {
                handlePageChange(currentPage.value - 1);
            }
            closeMenu();
        } catch (error) {
            showToast({ icon: 'error', title: error.message });
        }
    }
};

// Navigate to edit resident page
const editResident = (residentId) => {
    router.push(`/residents/edit-resident/${residentId}`);
    closeMenu();
};

const handlePageChange = (page) => {
    currentPage.value = page;

    const filters = {
        search: route.query.search,
        age_range: route.query.age_range,
        gender: route.query.gender
    };

    residentStore.getResidents(currentPage.value, filters);

    router.replace({
        query: {
            ...route.query,
            page: currentPage.value
        }
    });
};

watch(() => route.query, (newQuery) => {
    const page = parseInt(newQuery.page) || 1;
    const filters = {
        search: newQuery.search,
        age_range: newQuery.age_range,
        gender: newQuery.gender
    };

    if (page !== currentPage.value) {
        currentPage.value = page;
    }

    residentStore.getResidents(currentPage.value, filters);
}, { deep: true });

onMounted(() => {
    const filters = {
        search: route.query.search,
        age_range: route.query.age_range,
        gender: route.query.gender
    };
    residentStore.getResidents(currentPage.value, filters);

    // Close menu on click outside
    const handleClickOutside = (event) => {
        if (!event.target.closest('.burger-menu-container') &&
            !event.target.closest('[data-teleport-menu]') &&
            teleportMenuRowId.value !== null) {
            teleportMenuRowId.value = null;
        }
    };
    document.addEventListener('click', handleClickOutside);

    onUnmounted(() => {
        document.removeEventListener('click', handleClickOutside);
    });
});


const columns = [
    { key: "avatar", label: "Image" },
    { key: "resident_number", label: "Resident Number" },
    { key: "last_name", label: "Last Name" },
    { key: "first_name", label: "First Name" },
    { key: "middle_name", label: "Middle Name" },
];
</script>


<template>
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold text-gray-600">List of Residents</h1>

        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>

        <Table :columns="columns" :rows="residents" :searchable="false" :selectable="false" v-else>
            <template #cell(avatar)="{ row }">
                <img :src="row.avatar" alt="image" class="w-10 h-10 rounded-full" />
            </template>
            <template #cell(middle_name)="{ row }">
                {{ row.middle_name }}
            </template>
            <template #cell(contact_person)="{ row }">
                {{ row.contact_person }}
            </template>
            <template #actions="{ row }">
                <div class="relative burger-menu-container">
                    <!-- Burger Menu Button -->
                    <button @click="(e) => toggleMenu(e, row.id)"
                        class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'bg-gray-100': teleportMenuRowId === row.id }" aria-label="More actions">
                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path d="M10 4a2 2 0 100-4 2 2 0 000 4z" />
                            <path d="M10 20a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                </div>
            </template>
        </Table>

        <!-- Teleport menu for burger dropdown -->
        <Teleport to="body">
            <div v-if="teleportMenuRowId !== null" data-teleport-menu :style="{
                position: 'absolute',
                top: menuPosition.top + 'px',
                left: menuPosition.left + 'px',
                zIndex: 9999
            }" class="bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-48">
                <button @click="editResident(teleportMenuRowId)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </button>

                <button @click="deleteResident(teleportMenuRowId)"
                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>

                <button
                    @click="() => { viewResident(residentStore.residents.find(r => r.id === teleportMenuRowId)); closeMenu(); }"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" />
                        <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    View
                </button>

            </div>
        </Teleport>

        <Paginate @page-changed="handlePageChange" :maxVisibleButtons="5" :totalPages="paginate.last_page"
            :totalItems="paginate.total" :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />
    </div>

    <!-- Modal Template -->
    <Modal :show="showModal" title="Resident Profile" maxWidth="6xl" @close="closeModal">
        <div class="flex flex-col md:flex-row max-h-[90vh] overflow-auto">
            <!-- Left Column -->
            <div
                class="bg-gradient-to-b from-blue-50 to-white p-8 md:w-1/3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
                <!-- Avatar or Placeholder -->
                <div class="w-40 h-40 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center cursor-default"
                    :class="{ 'border-gray-300': !selectedResident?.avatar, 'border-transparent': selectedResident?.avatar }">
                    <img v-if="selectedResident?.avatar" :src="selectedResident.avatar" alt="Resident Avatar"
                        class="w-40 h-40 rounded-xl object-cover" />
                    <div v-else class="text-center text-4xl text-gray-400 select-none">📸</div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="p-8 md:w-2/3 overflow-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Resident Number</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.resident_number || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Full Name</label>
                        <p class="mt-1 text-gray-900">
                            {{ selectedResident?.first_name || '-' }}
                            {{ selectedResident?.middle_name || '' }}
                            {{ selectedResident?.last_name || '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Birthday</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.birthday || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Age</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.age || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Gender</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.gender || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Address</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.address || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Contact Number</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.contact_number || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Contact Person</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.contact_person || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Family Member</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.family_member || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Emergency Contact</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.emergency_contact || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Email</label>
                        <p class="mt-1 text-gray-900">{{ selectedResident?.email || '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </Modal>

</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
