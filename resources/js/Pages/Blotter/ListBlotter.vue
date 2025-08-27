<script setup>
import { Table, Paginate } from '@/Components'
import Modal from '@/Components/Modal.vue'
import { useRoute, useRouter } from "vue-router"
import { useBlotterStore, useResidentStore } from '@/Stores'
import { storeToRefs } from 'pinia'
import { ref, onMounted } from "vue"
import useToast from '@/Utils/useToast'
import BlotterPrintTemplate from './BlotterPrintTemplate.vue'

const { showToast } = useToast()
const route = useRoute()
const router = useRouter()

const blotterStore = useBlotterStore()
const residentStore = useResidentStore()
const { residents } = storeToRefs(residentStore)
const { blotters, isLoading, paginate } = storeToRefs(blotterStore)

const currentPage = ref(parseInt(route.query.page) || 1)
const showModal = ref(false)
const showPrintModal = ref(false)
const selectedBlotter = ref(null)
const selectedPrintBlotter = ref(null)

// Format date for display
const formatDateTime = (isoString) => {
    if (!isoString) return 'N/A'
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    }
    return new Date(isoString).toLocaleString('en-US', options)
}

// Helper functions
const getResidentNumber = (id) => {
    const resident = residents.value.find(r => Number(r.id) === Number(id))
    return resident ? resident.resident_number : 'N/A'
}

const getResidentName = (id) => {
    const resident = residents.value.find(r => Number(r.id) === Number(id));
    if (!resident) return 'N/A';

    // Safely include middle name if it exists
    const middle = resident.middle_name ? ` ${resident.middle_name}` : '';
    return `${resident.first_name}${middle} ${resident.last_name}`;
};

const getSupportingDocuments = (blotter) => {
    if (!blotter?.supporting_documents) return []
    return Array.isArray(blotter.supporting_documents)
        ? blotter.supporting_documents
        : []
}

const getFileName = (doc) => {
    return doc.name || 'Unknown file'
}

const getFilePath = (doc) => {
    return doc.path || ''
}

// Pagination handler
const handlePageChange = (page) => {
    currentPage.value = page
    blotterStore.getBlotters(page)
    router.replace({ query: { page: page } })
}

// Open blotter details modal
const openModal = (blotter) => {
    selectedBlotter.value = blotter
    showModal.value = true
}

// Close modal
const closeModal = () => {
    showModal.value = false
    selectedBlotter.value = null
}

// Open print modal
const openPrintModal = (blotter) => {
    selectedPrintBlotter.value = blotter
    showPrintModal.value = true
}

const closePrintModal = () => {
    showPrintModal.value = false
    selectedPrintBlotter.value = null
}

// Handle print action
const handlePrint = () => {
    showToast({ icon: 'success', title: 'Print dialog opened successfully' })
}

// Delete blotter
const deleteBlotter = async (blotterId) => {
    if (!confirm('Are you sure you want to delete this blotter?')) return

    try {
        await blotterStore.deleteBlotter(blotterId)
        showToast({ icon: 'success', title: 'Blotter deleted successfully' })
        blotterStore.getBlotters(currentPage.value)

        // 🔑 Close the modal
        closeModal()
    } catch (error) {
        showToast({ icon: 'error', title: 'Failed to delete blotter' })
        console.error(error)
    }
}

// Edit blotter
const editBlotter = (blotterId) => {
    // Close all modals first
    showModal.value = false
    showPrintModal.value = false
    selectedBlotter.value = null
    selectedPrintBlotter.value = null

    // Navigate to edit page
    router.push(`/blotter/edit-blotter/${blotterId}`)
}

// Responsive table columns
const mobileColumns = [
    { key: "complainants_id", label: "Complainant", formatter: (value) => getResidentName(value) },
    { key: "respondents_id", label: "Respondent", formatter: (value) => getResidentName(value) }
];

const desktopColumns = [
    { key: "complainants_id", label: "Complainant", formatter: (value) => getResidentName(value) },
    { key: "respondents_id", label: "Respondent", formatter: (value) => getResidentName(value) },
    { key: "blotter_no", label: "Blotter ID" },
    { key: "title_case", label: "Title" },
    { key: "filing_date", label: "Filing Date", formatter: (value) => formatDateTime(value) },
    { key: "status", label: "Status" }
];

// Initial data loading
blotterStore.getBlotters(currentPage.value)
residentStore.getResidents()
</script>

<template>
    <div class="flex flex-col gap-3 sm:gap-4 p-2 sm:p-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900">List of Blotters</h1>
            <div class="text-xs sm:text-sm text-gray-600" v-if="paginate">
                {{ paginate.from }}-{{ paginate.to }} of {{ paginate.total }} blotters
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 sm:h-12 sm:w-12 border-b-2 border-gray-900"></div>
        </div>

        <!-- Mobile Card View -->
        <div v-else class="block sm:hidden">
            <div class="space-y-3">
                <div v-for="blotter in blotters" :key="blotter.id"
                    class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-medium text-gray-500 mb-1">Complainant</div>
                            <div class="text-sm font-semibold text-gray-900 truncate">
                                {{ getResidentName(blotter.complainants_id) }}
                            </div>
                        </div>
                        <button @click="openModal(blotter)"
                            class="ml-2 p-1.5 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-2">
                        <div class="text-xs font-medium text-gray-500 mb-1">Respondent</div>
                        <div class="text-sm text-gray-700 truncate">
                            {{ getResidentName(blotter.respondents_id) }}
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <div class="bg-gray-100 px-2 py-1 rounded">
                            <span class="text-gray-600">ID:</span>
                            <span class="font-medium">{{ blotter.blotter_no }}</span>
                        </div>
                        <div class="bg-gray-100 px-2 py-1 rounded">
                            <span :class="{
                                'text-green-600 font-semibold': blotter.status === 'Resolved',
                                'text-yellow-600 font-semibold': blotter.status === 'In Progress',
                                'text-red-600 font-semibold': blotter.status === 'Open'
                            }">
                                {{ blotter.status }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <div class="text-xs text-gray-600 truncate" :title="blotter.title_case">
                            {{ blotter.title_case }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ formatDateTime(blotter.filing_date) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block">
            <Table :columns="desktopColumns" :rows="blotters">
                <template #actions="{ row }">
                    <div class="flex gap-2">
                        <button @click="openModal(row)" title="View"
                            class="text-gray-600 p-2 rounded text-sm transition-transform flex items-center justify-center hover:scale-125">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Pagination -->
        <div v-if="paginate" class="mt-4">
            <Paginate @page-changed="handlePageChange" :maxVisibleButtons="3" :totalPages="paginate.last_page"
                :totalItems="paginate.total" :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />
        </div>

        <!-- Print Template Modal -->
        <BlotterPrintTemplate v-if="showPrintModal" :blotter="selectedPrintBlotter" @close="closePrintModal"
            @print="handlePrint" />

        <!-- Modal for Blotter Details -->
        <Modal :show="showModal" title="Blotter Details" max-width="4xl" @close="closeModal">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-8 text-gray-800 text-sm">
                <!-- Complainant Section -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold mb-3 text-blue-800 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        Complainant Information
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <h4 class="font-medium text-gray-600 text-xs mb-1">Name:</h4>
                            <p class="text-gray-900 font-medium">{{ getResidentName(selectedBlotter?.complainants_id) ||
                                'N/A'
                                }}</p>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-600 text-xs mb-1">Resident Number:</h4>
                            <div class="bg-white inline-block rounded px-2 py-1 text-blue-700 text-xs font-mono">
                                {{ getResidentNumber(selectedBlotter?.complainants_id) || 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Respondent Section -->
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="font-semibold mb-3 text-red-800 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        Respondent Information
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <h4 class="font-medium text-gray-600 text-xs mb-1">Name:</h4>
                            <p class="text-gray-900 font-medium">{{ getResidentName(selectedBlotter?.respondents_id) ||
                                'N/A' }}
                            </p>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-600 text-xs mb-1">Resident Number:</h4>
                            <div class="bg-white inline-block rounded px-2 py-1 text-red-700 text-xs font-mono">
                                {{ getResidentNumber(selectedBlotter?.respondents_id) || 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Case Details Grid -->
                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-semibold mb-1 text-xs text-gray-600">Blotter Number:</h3>
                        <p class="text-gray-900">{{ selectedBlotter?.blotter_no || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1 text-xs text-gray-600">Blotter Type:</h3>
                        <p class="text-gray-900 capitalize">{{ selectedBlotter?.blotter_type || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1 text-xs text-gray-600">Title:</h3>
                        <p class="text-gray-900">{{ selectedBlotter?.title_case || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1 text-xs text-gray-600">Barangay Case Number:</h3>
                        <p class="text-gray-900">{{ selectedBlotter?.barangay_case_no || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1 text-xs text-gray-600">Nature of Case:</h3>
                        <p class="text-gray-900">{{ selectedBlotter?.nature_of_case || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1 text-xs text-gray-600">Status:</h3>
                        <span :class="{
                            'text-green-600 font-semibold bg-green-100 px-2 py-1 rounded-full text-xs': selectedBlotter?.status === 'Resolved',
                            'text-yellow-600 font-semibold bg-yellow-100 px-2 py-1 rounded-full text-xs': selectedBlotter?.status === 'In Progress',
                            'text-red-600 font-semibold bg-red-100 px-2 py-1 rounded-full text-xs': selectedBlotter?.status === 'Open'
                        }">
                            {{ selectedBlotter?.status || 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1 text-xs text-gray-600">Filing Date:</h3>
                        <p class="text-gray-900">{{ formatDateTime(selectedBlotter?.filing_date) || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1 text-xs text-gray-600">Date & Time of Incident:</h3>
                        <p class="text-gray-900">{{ formatDateTime(selectedBlotter?.datetime_of_incident) || 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="font-semibold mb-1 text-xs text-gray-600">Location of Incident:</h3>
                    <p class="text-gray-900">{{ selectedBlotter?.place || 'N/A' }}</p>
                </div>

                <!-- Witnesses -->
                <div class="lg:col-span-2">
                    <h3 class="font-semibold mb-2 text-xs text-gray-600">Witness/es:</h3>
                    <div v-if="selectedBlotter?.witness"
                        class="pl-4 max-h-24 sm:max-h-32 overflow-y-auto bg-gray-50 border border-gray-300 rounded p-3 text-gray-700">
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <li v-for="(witness, index) in selectedBlotter.witness.split('\n').filter(name => name.trim())"
                                :key="index">
                                {{ witness.trim() }}
                            </li>
                        </ul>
                    </div>
                    <p v-else class="italic text-gray-400 text-sm">No witnesses listed</p>
                </div>

                <!-- Description -->
                <div class="lg:col-span-2">
                    <h3 class="font-semibold mb-2 text-xs text-gray-600">Description:</h3>
                    <div class="bg-gray-50 border border-gray-300 rounded p-3 max-h-32 sm:max-h-40 overflow-y-auto">
                        <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-wrap">
                            {{ selectedBlotter?.description || 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Supporting Documents -->
                <div class="lg:col-span-2">
                    <h3 class="font-semibold mb-2 text-xs text-gray-600">Supporting Documents:</h3>
                    <div v-if="getSupportingDocuments(selectedBlotter).length > 0"
                        class="bg-gray-50 border border-gray-300 rounded p-3">
                        <ul class="space-y-2">
                            <li v-for="(doc, index) in getSupportingDocuments(selectedBlotter)" :key="index"
                                class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <a :href="`/storage/${getFilePath(doc)}`" target="_blank"
                                    class="text-blue-600 hover:underline font-medium text-sm truncate">
                                    {{ getFileName(doc) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <p v-else class="italic text-gray-400 text-sm">No supporting documents available</p>
                </div>

                <!-- Action Buttons -->
                <div class="lg:col-span-2 flex flex-col sm:flex-row justify-end gap-2 pt-4 border-t border-gray-200">
                    <button @click="editBlotter(selectedBlotter.id)"
                        class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                        Edit Blotter
                    </button>
                    <button @click="openPrintModal(selectedBlotter)"
                        class="px-4 py-2 text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                        Print Document
                    </button>
                    <button @click="deleteBlotter(selectedBlotter.id)"
                        class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
@media (max-width: 640px) {
    .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}
</style>