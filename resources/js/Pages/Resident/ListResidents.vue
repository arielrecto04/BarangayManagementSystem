<script setup>
import { Table, Paginate } from '@/Components';
import { useResidentStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, ref, nextTick, watch } from "vue";
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

const openModal = async (resident) => {
    try {
        selectedResident.value = { ...resident }; // Create a copy to avoid reactivity issues
        await nextTick(); // Wait for next tick before showing modal
        showModal.value = true;
    } catch (error) {
        console.error('Error opening modal:', error);
    }
};

const closeModal = async () => {
    try {
        showModal.value = false;
        await nextTick(); // Wait for DOM to update
        selectedResident.value = null;
    } catch (error) {
        console.error('Error closing modal:', error);
    }
};

const deleteResident = async (residentId) => {
    if (!confirm('Are you sure you want to delete this resident?')) {
        return;
    }

    try {
        await residentStore.deleteResident(residentId);
        showToast({ icon: 'success', title: 'Resident deleted successfully' });

        // Close modal after deletion
        closeModal();

        // Reload current page
        await residentStore.getResidents(currentPage.value);
    } catch (error) {
        console.error('Error deleting resident:', error);
        showToast({ icon: 'error', title: error.message || 'Error deleting resident' });
    }
};

const columns = [
    { key: "resident_number", label: "Resident Number" },
    { key: "household_number", label: "Household ID" },
    { key: "first_name", label: "First Name" },
    { key: "middle_name", label: "Middle Name" },
    { key: "last_name", label: "Last Name" }
];
onMounted(async () => {
    try {
        const page = currentPage.value;
        await residentStore.getResidents(page);
    } catch (error) {
        console.error('Error loading residents:', error);
        showToast({ icon: 'error', title: 'Error loading residents' });
    }
});

// Watch for route changes
watch(() => route.query.page, (newPage) => {
    if (newPage && parseInt(newPage) !== currentPage.value) {
        currentPage.value = parseInt(newPage);
    }
});
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-600">List of Residents</h1>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex justify-center items-center min-h-[400px]">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500"></div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!residents || residents.length === 0" class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">👥</div>
            <h3 class="text-lg font-medium text-gray-600 mb-2">No residents found</h3>
            <p class="text-gray-500 mb-4">Get started by adding your first resident.</p>
            <router-link to="/residents/add-resident"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-block">
                Add First Resident
            </router-link>
        </div>

        <!-- Table Content -->
        <div v-else class="bg-white rounded-lg shadow">
            <Table :columns="columns" :rows="residents">
                <template #actions="{ row }">
                    <div class="flex flex-wrap gap-2">
                        <button @click="openModal(row)"
                            class="text-gray-600 p-2 rounded text-sm transition-transform flex items-center justify-center hover:scale-125"
                            title="View">
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

            <!-- Pagination -->
            <div v-if="paginate" class="px-6 py-4 border-t">
                <Paginate @page-changed="handlePageChange" :maxVisibleButtons="5" :totalPages="paginate.last_page"
                    :totalItems="paginate.total" :currentPage="paginate.current_page"
                    :itemsPerPage="paginate.per_page" />
            </div>
        </div>

        <!-- Modal using Teleport to prevent DOM conflicts -->
        <Teleport to="body">
            <div v-if="showModal && selectedResident"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                @click.self="closeModal">
                <div class="relative bg-white rounded-xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto m-4"
                    @click.stop>
                    <!-- Close Button -->
                    <button @click="closeModal"
                        class="absolute top-4 right-4 text-gray-600 hover:text-black text-2xl font-bold z-10 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
                        ×
                    </button>

                    <!-- Modal Title -->
                    <h2 class="text-xl font-bold mb-6 text-gray-800 pr-10">Resident Details</h2>

                    <!-- Resident Information Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Profile Image Placeholder -->
                        <div class="md:col-span-2 flex justify-center mb-4">
                            <div class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-4xl text-gray-400">👤</span>
                            </div>
                        </div>

                        <!-- Resident ID & Resident Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Resident ID</label>
                            <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                {{ selectedResident.id || 'N/A' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Resident Number</label>
                            <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                {{ selectedResident.resident_number || 'N/A' }}
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">First Name</label>
                                <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                    {{ selectedResident.first_name || 'N/A' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Last Name</label>
                                <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                    {{ selectedResident.last_name || 'N/A' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Age</label>
                                <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                    {{ selectedResident.age || 'N/A' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Gender</label>
                                <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                    {{ selectedResident.gender || 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <!-- Contact & Additional Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Birthday</label>
                                <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                    {{ selectedResident.birthday || 'N/A' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Contact Number</label>
                                <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                    {{ selectedResident.contact_number || 'N/A' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Family Member</label>
                                <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                    {{ selectedResident.family_member || 'N/A' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Emergency Contact</label>
                                <div class="mt-1 p-2 bg-gray-50 border rounded-md">
                                    {{ selectedResident.emergency_contact || 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <!-- Address (Full Width) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600">Address</label>
                            <div class="mt-1 p-2 bg-gray-50 border rounded-md min-h-[60px]">
                                {{ selectedResident.address || 'N/A' }}
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div v-if="selectedResident.created_at">
                            <label class="block text-sm font-medium text-gray-600">Created At</label>
                            <div class="mt-1 p-2 bg-gray-50 border rounded-md text-sm">
                                {{ new Date(selectedResident.created_at).toLocaleString() }}
                            </div>
                        </div>

                        <div v-if="selectedResident.updated_at">
                            <label class="block text-sm font-medium text-gray-600">Updated At</label>
                            <div class="mt-1 p-2 bg-gray-50 border rounded-md text-sm">
                                {{ new Date(selectedResident.updated_at).toLocaleString() }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                        <!-- Delete Button -->
                        <button @click="deleteResident(selectedResident.id)"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                            Delete
                        </button>
                        <router-link :to="`/residents/edit-resident/${selectedResident.id}`"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                            @click="closeModal">
                            Edit Resident
                        </router-link>
                        <button @click="closeModal"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/* Custom styles for better visual presentation */
.table-container {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

/* Button hover effects */
.btn-hover:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>