<script setup>
import { Table, Paginate } from '@/Components';
import Modal from '@/Components/Modal.vue';
import { useResidentStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, ref, watch, computed } from "vue";
import useToast from '@/Utils/useToast';
import { useRoute, useRouter } from 'vue-router';


const { showToast } = useToast();
const route = useRoute();
const router = useRouter();

const residentStore = useResidentStore();
const { residents, isLoading, paginate } = storeToRefs(residentStore);

const currentPage = ref(parseInt(route.query.page) || 1);
const showModal = ref(false);
const selectedResident = ref(null);

const handlePageChange = async (page) => {
    try {
        currentPage.value = page;
        await residentStore.getResidents(page);
        router.replace({ query: { page: page } });
    } catch (error) {
        console.error('Error changing page:', error);
        showToast({ icon: 'error', title: 'Error loading page' });
    }
};

const openModal = (resident) => {
    selectedResident.value = { ...resident };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedResident.value = null;
};

const deleteResident = async (residentId) => {
    if (!confirm('Are you sure you want to delete this resident?')) return;

    try {
        await residentStore.deleteResident(residentId);
        showToast({ icon: 'success', title: 'Resident deleted successfully' });
        closeModal();
        await residentStore.getResidents(currentPage.value);
    } catch (error) {
        console.error('Error deleting resident:', error);
        showToast({ icon: 'error', title: error.message || 'Error deleting resident' });
    }
};

const columns = [
    { key: "resident_number", label: "Resident Number", class: "hidden sm:table-cell" },
    { key: "household_number", label: "Household ID", class: "hidden sm:table-cell" },
    { key: "first_name", label: "First Name", class: "hidden sm:table-cell" },
    { key: "middle_name", label: "Middle Name", class: "hidden sm:table-cell" },
    { key: "last_name", label: "Last Name", class: "hidden sm:table-cell" },
];

onMounted(async () => {
    try {
        await residentStore.getResidents(currentPage.value);
    } catch (error) {
        console.error('Error loading residents:', error);
        showToast({ icon: 'error', title: 'Error loading residents' });
    }
});

watch(() => route.query.page, (newPage) => {
    if (newPage && parseInt(newPage) !== currentPage.value) {
        currentPage.value = parseInt(newPage);
    }
});

const searchQuery = ref("");

const filteredResidents = computed(() => {
    if (!searchQuery.value) return residents.value;
    return residents.value.filter(r =>
        `${r.first_name} ${r.last_name}`
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <div class="flex flex-col gap-4 px-3 sm:px-4 lg:px-0">

        <!-- Header -->
        <div
            class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-4 md:p-6 rounded-lg shadow gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-600">List of Residents</h1>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex justify-center items-center min-h-[200px] md:min-h-[400px]">
            <div class="animate-spin rounded-full h-12 w-12 md:h-16 md:w-16 border-b-2 border-blue-500"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!residents || residents.length === 0" class="text-center py-12 bg-white rounded-lg shadow-sm">
            <div class="text-gray-400 text-6xl mb-4">👥</div>
            <h3 class="text-lg md:text-xl font-medium text-gray-600 mb-2">No residents found</h3>
            <p class="text-gray-500 mb-4">Get started by adding your first resident.</p>
            <router-link to="/residents/add-resident"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-block">
                Add First Resident
            </router-link>
        </div>

        <!-- Residents List -->
        <template v-else>
            <!-- Mobile View -->
            <div class="bg-white rounded-lg shadow-sm md:hidden">
                <div class="divide-y divide-gray-100">
                    <!-- Search -->
                    <div class="w-full flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 p-4">
                        <input type="text" v-model="searchQuery" placeholder="Search residents..."
                            class="w-full px-3 py-2 border rounded-lg text-sm" />
                    </div>

                    <!-- Resident List -->
                    <div v-for="resident in filteredResidents" :key="resident.id"
                        class="flex items-center justify-between p-4 hover:bg-gray-50">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 text-base truncate">
                                {{ resident.resident_number }}
                            </div>
                            <div class="text-gray-600 text-sm mt-1 truncate">
                                {{ resident.first_name }} {{ resident.last_name }}
                            </div>
                            <div class="text-gray-500 text-xs mt-1">
                                {{ resident.gender }} • {{ resident.age }} yrs
                            </div>
                        </div>
                        <button @click="openModal(resident)"
                            class="ml-3 w-10 h-10 flex items-center justify-center text-blue-600 rounded-full hover:bg-blue-50"
                            title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>


            <!-- Desktop Table -->
            <div class="hidden md:block bg-white rounded-lg shadow overflow-x-auto">
                <Table :columns="columns" :rows="residents">
                    <template #cell="{ column, row }">
                        <div :class="column.class">{{ row[column.key] }}</div>
                    </template>
                    <template #actions="{ row }">
                        <div class="flex justify-center">
                            <button @click="openModal(row)" class="text-blue-600 p-2 rounded-full hover:bg-blue-50"
                                title="View Details">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </Table>
            </div>
        </template>

        <!-- Pagination -->
        <div v-if="paginate && residents?.length > 0" class="bg-white rounded-lg shadow-sm px-4 py-4 sm:px-6">
            <Paginate @page-changed="handlePageChange" :maxVisibleButtons="3" :totalPages="paginate.last_page"
                :totalItems="paginate.total" :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />
        </div>

        <!-- Modal -->
        <Modal :show="showModal" title="Resident Details" max-width="2xl" @close="closeModal">
            <div class="grid grid-cols-2 gap-4">
                <!-- Avatar (full width) -->
                <div class="col-span-2 flex justify-center mb-4">
                    <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center">
                        <span class="text-4xl text-gray-400">👤</span>
                    </div>
                </div>

                <!-- Info fields (always 2 columns) -->
                <div>
                    <label class="block text-xs font-medium text-gray-600">Resident ID</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md">{{ selectedResident?.id || 'N/A' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Resident Number</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md">{{ selectedResident?.resident_number || 'N/A' }}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">First Name</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md">{{ selectedResident?.first_name || 'N/A' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Last Name</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md">{{ selectedResident?.last_name || 'N/A' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Age</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md">{{ selectedResident?.age || 'N/A' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Gender</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md">{{ selectedResident?.gender || 'N/A' }}</div>
                </div>

                <!-- Address (full width) -->
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600">Address</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md min-h-[60px]">
                        {{ selectedResident?.address || 'N/A' }}
                    </div>
                </div>

                <!-- Dates side by side -->
                <div v-if="selectedResident?.created_at">
                    <label class="block text-xs font-medium text-gray-600">Created At</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md text-sm">
                        {{ new Date(selectedResident.created_at).toLocaleString() }}
                    </div>
                </div>
                <div v-if="selectedResident?.updated_at">
                    <label class="block text-xs font-medium text-gray-600">Updated At</label>
                    <div class="mt-1 p-2 bg-gray-50 border rounded-md text-sm">
                        {{ new Date(selectedResident.updated_at).toLocaleString() }}
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <template #footer>
                <div class="flex flex-row justify-end gap-3 mt-4 w-full">
                    <button @click="deleteResident(selectedResident?.id)"
                        class="flex-1 sm:flex-none bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        Delete
                    </button>

                    <router-link :to="`/residents/edit-resident/${selectedResident?.id}`" @click="closeModal"
                        class="flex-1 sm:flex-none flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Edit
                    </router-link>

                    <button @click="closeModal"
                        class="flex-1 sm:flex-none bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                        Close
                    </button>
                </div>
            </template>


        </Modal>



    </div>
</template>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Mobile-friendly tap targets */
@media (max-width: 767px) {

    button,
    a {
        min-height: 44px;
        min-width: 44px;
    }
}
</style>
