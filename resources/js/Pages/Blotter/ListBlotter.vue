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
    const resident = residents.value.find(r => Number(r.id) === Number(id))
    return resident ? `${resident.first_name} ${resident.last_name}` : 'N/A'
}

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

    await blotterStore.deleteBlotter(blotterId)
    showToast({ icon: 'success', title: 'Blotter deleted successfully' })
    blotterStore.getBlotters(currentPage.value)
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

// Table columns
const columns = [
    {
        key: "complainants_id",
        label: "Complainant",
        formatter: (value) => getResidentName(value)
    },
    {
        key: "respondents_id",
        label: "Respondent",
        formatter: (value) => getResidentName(value)
    },
    { key: "blotter_no", label: "Blotter ID" },
    { key: "title_case", label: "Title" },
    {
        key: "filing_date",
        label: "Filing Date",
        formatter: (value) => formatDateTime(value)
    },
    { key: "status", label: "Status" }
]

// Initial data loading
blotterStore.getBlotters(currentPage.value)
residentStore.getResidents()
</script>

<template>
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold text-gray-600">List of Blotters</h1>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>

        <!-- Blotter Table -->
        <Table v-else :columns="columns" :rows="blotters">
            <template #actions="{ row }">
                <div class="flex gap-2">
                    <!-- View Button -->
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

        <!-- Pagination -->
        <Paginate v-if="paginate" @page-changed="handlePageChange" :maxVisibleButtons="5"
            :totalPages="paginate.last_page" :totalItems="paginate.total" :currentPage="paginate.current_page"
            :itemsPerPage="paginate.per_page" />

        <!-- Print Template Modal -->
        <BlotterPrintTemplate v-if="showPrintModal" :blotter="selectedPrintBlotter" @close="closePrintModal"
            @print="handlePrint" />

        <!-- Modal for Blotter Details -->
        <Modal :show="showModal" title="Blotter Details" max-width="4xl" @close="closeModal">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-gray-800 text-sm">
                <!-- Complainant Section -->
                <div>
                    <h3 class="font-semibold mb-3 text-blue-800">Complainant Information</h3>
                    <div class="mb-3">
                        <h4 class="font-medium mb-1 text-gray-600">Name:</h4>
                        <p class="text-gray-900">{{ getResidentName(selectedBlotter?.complainants_id) || 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <h4 class="font-medium mb-1 text-gray-600">Resident Number:</h4>
                        <div
                            class="bg-blue-50 inline-block rounded px-2 py-1 text-blue-700 text-xs font-mono select-all">
                            {{ getResidentNumber(selectedBlotter?.complainants_id) || 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Respondent Section -->
                <div>
                    <h3 class="font-semibold mb-3 text-red-800">Respondent Information</h3>
                    <div class="mb-3">
                        <h4 class="font-medium mb-1 text-gray-600">Name:</h4>
                        <p class="text-gray-900">{{ getResidentName(selectedBlotter?.respondents_id) || 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <h4 class="font-medium mb-1 text-gray-600">Resident Number:</h4>
                        <div
                            class="bg-blue-50 inline-block rounded px-2 py-1 text-blue-700 text-xs font-mono select-all">
                            {{ getResidentNumber(selectedBlotter?.respondents_id) || 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Case Details -->
                <div>
                    <h3 class="font-semibold mb-1">Blotter Number:</h3>
                    <p>{{ selectedBlotter?.blotter_no || 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">Blotter Type:</h3>
                    <p class="capitalize">{{ selectedBlotter?.blotter_type || 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">Title:</h3>
                    <p>{{ selectedBlotter?.title_case || 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">Barangay Case Number:</h3>
                    <p>{{ selectedBlotter?.barangay_case_no || 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">Nature of Case:</h3>
                    <p>{{ selectedBlotter?.nature_of_case || 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">Status:</h3>
                    <p :class="{
                        'text-green-600 font-semibold': selectedBlotter?.status === 'Resolved',
                        'text-yellow-600 font-semibold': selectedBlotter?.status === 'In Progress',
                        'text-red-600 font-semibold': selectedBlotter?.status === 'Open'
                    }">
                        {{ selectedBlotter?.status || 'N/A' }}
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">Filing Date:</h3>
                    <p>{{ formatDateTime(selectedBlotter?.filing_date) || 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">Date & Time of Incident:</h3>
                    <p>{{ formatDateTime(selectedBlotter?.datetime_of_incident) || 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">Location of Incident:</h3>
                    <p>{{ selectedBlotter?.place || 'N/A' }}</p>
                </div>

                <!-- Witnesses -->
                <div class="md:col-span-2">
                    <h3 class="font-semibold mb-2">Witness/es:</h3>
                    <div v-if="selectedBlotter?.witness"
                        class="pl-4 max-h-32 overflow-y-auto bg-gray-50 border border-gray-300 rounded p-2 text-gray-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li v-for="(witness, index) in selectedBlotter.witness.split('\n').filter(name => name.trim())"
                                :key="index">
                                {{ witness.trim() }}
                            </li>
                        </ul>
                    </div>
                    <p v-else class="italic text-gray-400">No witnesses listed</p>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <h3 class="font-semibold mb-1">Description:</h3>
                    <textarea readonly
                        class="w-full h-40 p-3 border border-gray-300 rounded resize-none bg-gray-50 text-gray-800 text-sm leading-relaxed focus:outline-none"
                        :value="selectedBlotter?.description || 'N/A'"></textarea>
                </div>

                <!-- Resolution -->
                <div class="md:col-span-2">
                    <h3 class="font-semibold mb-1">Resolution:</h3>
                    <textarea readonly
                        class="w-full h-40 p-3 border border-gray-300 rounded resize-none bg-gray-50 text-gray-800 text-sm leading-relaxed focus:outline-none"
                        :value="selectedBlotter?.resolution || 'N/A'"></textarea>
                </div>

                <!-- Supporting Documents -->
                <div class="md:col-span-2">
                    <h3 class="font-semibold mb-1">Supporting Documents:</h3>
                    <div v-if="getSupportingDocuments(selectedBlotter).length > 0">
                        <ul class="list-disc list-inside space-y-1">
                            <li v-for="(doc, index) in getSupportingDocuments(selectedBlotter)" :key="index">
                                <a :href="`/storage/${getFilePath(doc)}`" target="_blank"
                                    class="text-blue-600 hover:underline font-medium">
                                    {{ getFileName(doc) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <p v-else class="italic text-gray-400">No supporting documents available</p>
                </div>

                <div class="flex justify-end gap-2 mb-4 md:col-span-2">
                    <button @click="editBlotter(selectedBlotter.id)"
                        class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors">
                        Edit
                    </button>
                    <button @click="openPrintModal(selectedBlotter)"
                        class="px-4 py-2 text-white bg-purple-600 rounded hover:bg-purple-700 transition-colors">
                        Print
                    </button>
                    <button @click="deleteBlotter(selectedBlotter.id)"
                        class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700 transition-colors">
                        Delete
                    </button>
                </div>

            </div>
        </Modal>
    </div>
</template>

<style scoped>
.action-button {
    padding: 0.5rem;
    border-radius: 9999px;
    transition: background-color 0.2s;
}

.action-button:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.action-button svg {
    width: 1.25rem;
    height: 1.25rem;
}
</style>