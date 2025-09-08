<script setup>
import { onMounted, ref, computed } from 'vue';
import { useClinicVisitStore } from '@/Stores';
import { useResidentStore } from '@/Stores';
import { Table, Paginate } from '@/Components';
import Modal from '@/Components/Modal.vue';
import { storeToRefs } from 'pinia';
import useToast from '@/Utils/useToast';
import { useRouter, useRoute } from 'vue-router';
import { PlusIcon, UserIcon, CalendarIcon, ClipboardDocumentListIcon, DocumentTextIcon, BeakerIcon, TrashIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const { showToast, showConfirm } = useToast();
const router = useRouter();
const route = useRoute();

const clinicVisitStore = useClinicVisitStore();
const residentStore = useResidentStore();
const { visits, isLoading, paginate } = storeToRefs(clinicVisitStore);

const currentPage = ref(route.query.page || 1);
const searchQuery = ref('');

// Modal state
const showModal = ref(false);
const showDeleteConfirm = ref(false);
const selectedVisit = ref(null);
const selectedResident = ref(null);
const isDeleting = ref(false);

const handlePageChange = (page) => {
    currentPage.value = page;
    clinicVisitStore.getVisits(currentPage.value, searchQuery.value);
    router.replace({ query: { page: currentPage.value } });
};

const handleSearch = () => handlePageChange(1);

// Handle row click to show modal
const handleRowClick = async (visit) => {
    selectedVisit.value = visit;
    showModal.value = true;

    // Fetch complete resident details if resident_id exists
    if (visit.resident_id) {
        try {
            const resident = await residentStore.getResidentById(visit.resident_id);
            selectedResident.value = resident;
        } catch (error) {
            console.error('Error fetching resident details:', error);
            // Fallback to basic resident info from visit
            selectedResident.value = visit.resident || null;
        }
    } else {
        selectedResident.value = visit.resident || null;
    }
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    selectedVisit.value = null;
    selectedResident.value = null;
};


const deleteVisit = async () => {
    if (!selectedVisit.value) return;

    // ✅ Add SweetAlert2 confirmation dialog like in ListComplaints.vue
    const result = await showConfirm(
        'Are you sure you want to delete this clinic visit?',
        'Delete Confirmation'
    );

    if (!result.isConfirmed) return;

    try {
        isDeleting.value = true;
        await clinicVisitStore.deleteClinicVisit(selectedVisit.value.id);
        showToast({
            icon: 'success',
            title: 'Clinic visit deleted successfully!'
        });
        showDeleteConfirm.value = false;
        closeModal();
        // Refresh the current page
        clinicVisitStore.getVisits(currentPage.value, searchQuery.value);
    } catch (error) {
        showToast({
            icon: 'error',
            title: 'Failed to delete clinic visit. Please try again.'
        });
        console.error('Error deleting clinic visit:', error);
    } finally {
        isDeleting.value = false;
    }
};

// Format date for display
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Format datetime for display
const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Computed property to get resident display name
const residentDisplayName = computed(() => {
    if (selectedResident.value) {
        return `${selectedResident.value.first_name} ${selectedResident.value.middle_name || ''} ${selectedResident.value.last_name}`.trim();
    }
    return 'N/A';
});

onMounted(() => {
    clinicVisitStore.getVisits(currentPage.value);
});
</script>

<template>
    <div class="space-y-4 bg-white p-4 sm:p-6 shadow-lg rounded-lg">

        <!-- Header & Actions -->
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-4 mb-4 space-y-3 sm:space-y-0">
            <h2 class="text-2xl font-bold text-gray-800">Clinic Visits Log</h2>
            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2 space-y-2 sm:space-y-0 w-full sm:w-auto">
                <form @submit.prevent="handleSearch" class="flex w-full sm:w-auto">
                    <input v-model="searchQuery" type="text" placeholder="Search by resident..."
                        class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200 w-full sm:w-48" />
                </form>
                <router-link to="/health/clinic-visits/add"
                    class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center w-full sm:w-auto">
                    <PlusIcon class="w-5 h-5" />
                    Log New Visit
                </router-link>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div v-if="isLoading" class="flex justify-center items-center py-10">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-green-700"></div>
        </div>

        <!-- Mobile Cards -->
        <div v-else class="space-y-4 sm:hidden">
            <div v-for="visit in visits" :key="visit.id"
                class="bg-gray-50 p-4 rounded-lg shadow hover:shadow-md cursor-pointer transition-shadow"
                @click="handleRowClick(visit)">
                <p class="text-sm font-semibold text-gray-700">Date: <span class="font-normal">{{
                    formatDate(visit.visit_date) }}</span></p>
                <p class="text-sm font-semibold text-gray-700">Resident: <span class="font-normal">{{ visit.resident ?
                    `${visit.resident.last_name}, ${visit.resident.first_name}` : 'N/A' }}</span></p>
                <p class="text-sm font-semibold text-gray-700">Reason: <span class="font-normal">{{
                    visit.reason_for_visit }}</span></p>
                <p class="text-sm font-semibold text-gray-700">Diagnosis: <span class="font-normal">{{ visit.diagnosis
                    || 'N/A'
                        }}</span></p>
                <p class="text-xs text-gray-500 mt-2">Click to view details</p>
            </div>
        </div>

        <!-- Table for larger screens -->
        <div class="hidden sm:block">
            <Table :columns="[
                { key: 'visit_date', label: 'Date of Visit' },
                { key: 'resident', label: 'Resident Name' },
                { key: 'reason_for_visit', label: 'Reason' },
                { key: 'diagnosis', label: 'Diagnosis' }
            ]" :rows="visits" @row-click="handleRowClick" clickable>
                <template #cell(resident)="{ row }">
                    <span v-if="row.resident">{{ row.resident.last_name }}, {{ row.resident.first_name }}</span>
                    <span v-else class="text-gray-400">N/A</span>
                </template>

                <template #cell(visit_date)="{ row }">
                    {{ formatDate(row.visit_date) }}
                </template>

                <template #cell(diagnosis)="{ row }">
                    {{ row.diagnosis || 'N/A' }}
                </template>
            </Table>
        </div>

        <!-- Pagination -->
        <Paginate v-if="!isLoading && paginate.total > 0" @page-changed="handlePageChange"
            :totalPages="paginate.last_page" :currentPage="paginate.current_page" :totalItems="paginate.total"
            class="mt-4" />
    </div>

    <!-- Visit Details Modal -->
    <Modal :show="showModal" @close="closeModal" title="Clinic Visit Details" maxWidth="3xl">
        <div v-if="selectedVisit" class="space-y-6">
            <!-- Visit Header -->
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <CalendarIcon class="w-6 h-6 text-green-600" />
                        <div>
                            <h3 class="text-lg font-semibold text-green-800">
                                {{ formatDateTime(selectedVisit.visit_date) }}
                            </h3>
                            <p class="text-sm text-green-600">Visit Date & Time</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-green-700">Visit ID</p>
                        <p class="text-lg font-semibold text-green-800">#{{ selectedVisit.id }}</p>
                    </div>
                </div>
            </div>

            <!-- Resident Information -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="flex items-start space-x-3">
                    <UserIcon class="w-6 h-6 text-blue-600 mt-1" />
                    <div class="flex-1">
                        <h4 class="font-semibold text-blue-800 mb-3">Resident Information</h4>
                        <div v-if="selectedResident" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-blue-700">Full Name:</p>
                                <p class="text-sm text-blue-600">{{ residentDisplayName }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700">Age:</p>
                                <p class="text-sm text-blue-600">{{ selectedResident.age || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700">Gender:</p>
                                <p class="text-sm text-blue-600">{{ selectedResident.gender || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700">Civil Status:</p>
                                <p class="text-sm text-blue-600">{{ selectedResident.civil_status || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700">Contact:</p>
                                <p class="text-sm text-blue-600">{{ selectedResident.contact_number || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700">Address:</p>
                                <p class="text-sm text-blue-600">
                                    {{ selectedResident.address || `Purok ${selectedResident.purok || 'N/A'}` }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700">Occupation:</p>
                                <p class="text-sm text-blue-600">{{ selectedResident.occupation || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700">Emergency Contact:</p>
                                <p class="text-sm text-blue-600">{{ selectedResident.emergency_contact || 'N/A' }}</p>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            Resident information not available
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visit Details -->
            <div class="space-y-4">
                <!-- Reason for Visit -->
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <div class="flex items-start space-x-3">
                        <ClipboardDocumentListIcon class="w-6 h-6 text-yellow-600 mt-1" />
                        <div class="flex-1">
                            <h4 class="font-semibold text-yellow-800 mb-2">Reason for Visit</h4>
                            <p class="text-sm text-yellow-700">
                                {{ selectedVisit.reason_for_visit || 'Not specified' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Diagnosis -->
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <div class="flex items-start space-x-3">
                        <BeakerIcon class="w-6 h-6 text-purple-600 mt-1" />
                        <div class="flex-1">
                            <h4 class="font-semibold text-purple-800 mb-2">Diagnosis</h4>
                            <p class="text-sm text-purple-700">
                                {{ selectedVisit.diagnosis || 'No diagnosis recorded' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Treatment Notes -->
                <div v-if="selectedVisit.treatment_notes" class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                    <div class="flex items-start space-x-3">
                        <DocumentTextIcon class="w-6 h-6 text-indigo-600 mt-1" />
                        <div class="flex-1">
                            <h4 class="font-semibold text-indigo-800 mb-2">Treatment Notes</h4>
                            <p class="text-sm text-indigo-700 whitespace-pre-wrap">
                                {{ selectedVisit.treatment_notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Prescription -->
                <div v-if="selectedVisit.prescription" class="bg-red-50 p-4 rounded-lg border border-red-200">
                    <div class="flex items-start space-x-3">
                        <DocumentTextIcon class="w-6 h-6 text-red-600 mt-1" />
                        <div class="flex-1">
                            <h4 class="font-semibold text-red-800 mb-2">Prescription</h4>
                            <p class="text-sm text-red-700 whitespace-pre-wrap">
                                {{ selectedVisit.prescription }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                <!-- Delete Button -->
                <button @click="deleteVisit"
                    class="flex items-center space-x-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                    <TrashIcon class="w-4 h-4" />
                    <span>Delete Visit</span>
                </button>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button @click="closeModal"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                        Close
                    </button>
                    <router-link v-if="selectedVisit" :to="`/health/clinic-visits/edit/${selectedVisit.id}`"
                        class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 font-medium"
                        @click="closeModal">
                        Edit Visit
                    </router-link>
                </div>
            </div>
        </template>
    </Modal>
</template>