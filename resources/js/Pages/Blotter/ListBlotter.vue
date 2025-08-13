<script setup>
import { AuthLayout } from "@/Layouts";
import { Table } from '@/Components'
import { useRoute, useRouter } from "vue-router";
import { useBlotterStore, useResidentStore } from '@/Stores'
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref, nextTick, watch } from "vue";
import useToast from '@/Utils/useToast';
import BlotterPrintTemplate from './BlotterPrintTemplate.vue';

const showPrintModal = ref(false);
const selectedPrintBlotter = ref(null);

const openPrintModal = (blotter) => {
    selectedPrintBlotter.value = blotter;
    showPrintModal.value = true;
    closeMenu(blotter.id);
};

const closePrintModal = () => {
    showPrintModal.value = false;
    selectedPrintBlotter.value = null;
};

const handlePrint = () => {
    showToast({ icon: 'success', title: 'Print dialog opened successfully' });
};

const { showToast } = useToast();
const route = useRoute();
const router = useRouter();

const blotterStore = useBlotterStore();
const residentStore = useResidentStore();
const { residents } = storeToRefs(residentStore);
const { blotters, isLoading } = storeToRefs(blotterStore);

const showModal = ref(false);
const selectedBlotter = ref(null);

const openModal = async (blotter) => {
    try {
        // Fetch detailed blotter data; ensure latest supporting documents
        await blotterStore.getBlotterById(blotter.id);
        selectedBlotter.value = blotterStore.blotter;
        showModal.value = true;
    } catch (error) {
        console.error('Error fetching blotter details:', error);
        showToast({ icon: 'error', title: 'Failed to load blotter details' });
    }
};

const closeModal = () => {
    showModal.value = false;
    selectedBlotter.value = null;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const getResidentNumber = (id) => {
    if (!id) return 'N/A';
    const resident = residents.value.find(r => r.id == id);
    return resident ? resident.resident_number : 'N/A';
};

const getResidentName = (id) => {
    if (!id) return 'N/A';
    const resident = residents.value.find(r => r.id == id);
    return resident ? `${resident.first_name} ${resident.last_name}` : 'N/A';
};

const getSupportingDocuments = (blotter) => {
    if (!blotter?.supporting_documents) return [];

    if (Array.isArray(blotter.supporting_documents)) {
        return blotter.supporting_documents.filter(doc => {
            if (typeof doc === 'object' && doc.path && doc.name) return true;
            if (typeof doc === 'string') return true;
            return false;
        });
    }

    if (typeof blotter.supporting_documents === 'string') {
        try {
            const parsed = JSON.parse(blotter.supporting_documents);
            return Array.isArray(parsed) ? parsed.filter(doc => {
                if (typeof doc === 'object' && doc.path && doc.name) return true;
                if (typeof doc === 'string') return true;
                return false;
            }) : [];
        } catch (e) {
            console.warn('Failed to parse supporting documents:', e);
            return [];
        }
    }
    return [];
};

const getFileName = (doc) => {
    if (typeof doc === 'object' && doc.name) return doc.name;
    if (typeof doc === 'string') return doc.split('/').pop() || 'Unknown file';
    return 'Unknown file';
};

const getFilePath = (doc) => {
    if (typeof doc === 'object' && doc.path) return doc.path;
    if (typeof doc === 'string') return doc;
    return '';
};

const columns = [
    { key: "blotter_no", label: "Blotter ID" },
    { key: "title_case", label: "Title" },
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
    { key: "status", label: "Status" },
    {
        key: "filing_date",
        label: "Date",
        formatter: (value) => formatDate(value)
    }
];

// --- Burger menu state and logic ---

const menuPosition = ref({ top: 0, left: 0 });
const teleportMenuRowId = ref(null);

const toggleMenu = async (event, blotterId) => {
    if (teleportMenuRowId.value === blotterId) {
        teleportMenuRowId.value = null;
        return;
    }
    if (teleportMenuRowId.value !== null) {
        teleportMenuRowId.value = null;
        await nextTick();
    }
    const rect = event.currentTarget.getBoundingClientRect();
    const dropdownWidth = 192; // approximate width of dropdown (w-48)
    const viewportWidth = window.innerWidth;
    const scrollX = window.scrollX;

    let leftPosition = rect.left + scrollX;
    if (rect.left + dropdownWidth > viewportWidth) {
        leftPosition = rect.left + scrollX - dropdownWidth + rect.width;
    } else {
        leftPosition = rect.left + scrollX;
    }
    leftPosition = Math.max(leftPosition, 0);

    menuPosition.value = {
        top: rect.bottom + window.scrollY,
        left: leftPosition,
    };

    await nextTick();
    teleportMenuRowId.value = blotterId;
};

const closeMenu = (blotterId) => {
    teleportMenuRowId.value = null;
};

// Close menu on clicking outside
const handleClickOutside = (event) => {
    const target = event.target;
    if (!target.closest('.burger-menu-container') &&
        !target.closest('[data-teleport-menu]') &&
        teleportMenuRowId.value !== null) {
        teleportMenuRowId.value = null;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const editBlotter = (blotterId) => {
    router.push(`/blotter/edit-blotter/${blotterId}`);
    closeMenu(blotterId);
};

const deleteBlotterWithMenu = async (blotterId) => {
    if (confirm('Are you sure you want to delete this blotter?')) {
        try {
            await blotterStore.deleteBlotter(blotterId);
            showToast({ icon: 'success', title: 'Blotter deleted successfully' });
            blotterStore.getBlotters();
            closeMenu(blotterId);
        } catch (error) {
            showToast({ icon: 'error', title: error.message });
        }
    }
};

const viewBlotter = (blotter) => {
    openModal(blotter);
    closeMenu(blotter.id);
};

// Initial data fetch
onMounted(() => {
    blotterStore.getBlotters();
    residentStore.getResidents(); // ensure residents loaded
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold text-gray-600">List of Blotters</h1>

        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>

        <Table :columns="columns" :rows="blotters" v-else>
            <template #actions="{ row }">
                <div class="relative burger-menu-container">
                    <!-- Burger Menu Button -->
                    <button @click="(e) => toggleMenu(e, row.id)"
                        class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'bg-gray-100': teleportMenuRowId === row.id }">
                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path d="M10 4a2 2 0 100-4 2 2 0 000 4z" />
                            <path d="M10 20a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                </div>
            </template>
        </Table>

        <!-- Teleport dropdown menu -->
        <Teleport to="body">
            <div v-if="teleportMenuRowId !== null" data-teleport-menu :style="{
                position: 'absolute',
                top: menuPosition.top + 'px',
                left: menuPosition.left + 'px',
                zIndex: 9999
            }" class="bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-48">

                <!-- Edit -->
                <button @click="editBlotter(teleportMenuRowId)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </button>

                <!-- View -->
                <button @click="viewBlotter(blotters.find(b => b.id === teleportMenuRowId))"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View
                </button>

                <!-- Print -->
                <button @click="openPrintModal(blotters.find(b => b.id === teleportMenuRowId))"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>

                <hr class="my-2 border-gray-200">

                <!-- Delete -->
                <button @click="deleteBlotterWithMenu(teleportMenuRowId)"
                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </div>
        </Teleport>

        <!-- Print Modal -->
        <BlotterPrintTemplate v-if="showPrintModal" :blotter="selectedPrintBlotter" @close="closePrintModal"
            @print="handlePrint" />

        <!-- Modal for Blotter Details -->
        <div v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
            <div
                class="relative z-60 bg-white rounded-xl shadow-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto scroll-m-4">
                <!-- Close Button -->
                <button @click="closeModal"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-3xl font-bold leading-none focus:outline-none"
                    aria-label="Close modal">
                    &times;
                </button>

                <!-- Modal Title -->
                <h2 class="text-2xl font-semibold mb-8 text-gray-800 border-b pb-4">
                    Blotter Details
                </h2>

                <!-- Grid Layout for Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-gray-800 text-sm">

                    <!-- Complainant Section -->
                    <div>
                        <h3 class="font-semibold mb-3 text-blue-800">Complainant Information</h3>

                        <!-- Complainant Name -->
                        <div class="mb-3">
                            <h4 class="font-medium mb-1 text-gray-600">Name:</h4>
                            <p class="text-gray-900">{{ getResidentName(selectedBlotter?.complainants_id) || 'N/A' }}
                            </p>
                        </div>

                        <!-- Complainant Resident Number -->
                        <div class="mb-3">
                            <h4 class="font-medium mb-1 text-gray-600">Resident Number:</h4>
                            <div
                                class="bg-blue-50 inline-block rounded px-2 py-1 text-blue-700 text-xs font-mono select-all">
                                {{ getResidentNumber(selectedBlotter?.complainants_id) || 'N/A' }}
                            </div>
                        </div>

                        <!-- Complainant Resident ID -->
                        <div class="mb-3">
                            <h4 class="font-medium mb-1 text-gray-600">Resident ID:</h4>
                            <p class="text-gray-500 text-sm">
                                {{ selectedBlotter?.complainants_id || 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <!-- Respondent Section -->
                    <div>
                        <h3 class="font-semibold mb-3 text-red-800">Respondent Information</h3>

                        <!-- Respondent Name -->
                        <div class="mb-3">
                            <h4 class="font-medium mb-1 text-gray-600">Name:</h4>
                            <p class="text-gray-900">{{ getResidentName(selectedBlotter?.respondents_id) || 'N/A' }}</p>
                        </div>

                        <!-- Respondent Resident Number -->
                        <div class="mb-3">
                            <h4 class="font-medium mb-1 text-gray-600">Resident Number:</h4>
                            <div
                                class="bg-blue-50 inline-block rounded px-2 py-1 text-blue-700 text-xs font-mono select-all">
                                {{ getResidentNumber(selectedBlotter?.respondents_id) || 'N/A' }}
                            </div>
                        </div>

                        <!-- Respondent Resident ID -->
                        <div class="mb-3">
                            <h4 class="font-medium mb-1 text-gray-600">Resident ID:</h4>
                            <p class="text-gray-500 text-sm">
                                {{ selectedBlotter?.respondents_id || 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <!-- Blotter Number -->
                    <div>
                        <h3 class="font-semibold mb-1">Blotter Number:</h3>
                        <p>{{ selectedBlotter?.blotter_no || 'N/A' }}</p>
                    </div>

                    <!-- Blotter Type -->
                    <div>
                        <h3 class="font-semibold mb-1">Blotter Type:</h3>
                        <p class="capitalize">{{ selectedBlotter?.blotter_type || 'N/A' }}</p>
                    </div>

                    <!-- Title Case -->
                    <div>
                        <h3 class="font-semibold mb-1 capitalize">Title Case:</h3>
                        <p>{{ selectedBlotter?.title_case || 'N/A' }}</p>
                    </div>

                    <!-- Barangay Case Number -->
                    <div>
                        <h3 class="font-semibold mb-1">Barangay Case Number:</h3>
                        <p>{{ selectedBlotter?.barangay_case_no || 'N/A' }}</p>
                    </div>

                    <!-- Nature of Case -->
                    <div>
                        <h3 class="font-semibold mb-1 capitalize">Nature of Case:</h3>
                        <p>{{ selectedBlotter?.nature_of_case || 'N/A' }}</p>
                    </div>

                    <!-- Filing Date -->
                    <div>
                        <h3 class="font-semibold mb-1">Filing Date:</h3>
                        <p>{{ formatDate(selectedBlotter?.filing_date) || 'N/A' }}</p>
                    </div>

                    <!-- Location of Incident -->
                    <div>
                        <h3 class="font-semibold mb-1">Location of Incident:</h3>
                        <p>{{ selectedBlotter?.place || 'N/A' }}</p>
                    </div>

                    <!-- Date & Time of Incident -->
                    <div>
                        <h3 class="font-semibold mb-1">Date & Time of Incident:</h3>
                        <p>{{ formatDate(selectedBlotter?.datetime_of_incident) || 'N/A' }}</p>
                    </div>

                    <!-- Witness/es -->
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

                    <!-- Status -->
                    <div class="md:col-span-2">
                        <h3 class="font-semibold mb-1">Status:</h3>
                        <p :class="{
                            'text-green-600 font-semibold': selectedBlotter?.status === 'Resolved',
                            'text-yellow-600 font-semibold': selectedBlotter?.status === 'In Progress',
                            'text-red-600 font-semibold': selectedBlotter?.status === 'Open'
                        }">
                            {{ selectedBlotter?.status || 'N/A' }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <h3 class="font-semibold mb-1">Description:</h3>
                        <textarea readonly
                            class="w-full h-40 p-3 border border-gray-300 rounded resize-none bg-gray-50 text-gray-800 text-sm leading-relaxed focus:outline-none"
                            :value="selectedBlotter?.description || 'N/A'"></textarea>
                    </div>

                    <!-- Supporting Documents Section -->
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
                </div>
            </div>
        </div>

    </div>
</template>

<style>
.multiselect {
    min-height: auto;
}

.multiselect__tags {
    min-height: 44px;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem;
}

.multiselect__input,
.multiselect__single {
    font-size: 1rem;
    margin-bottom: 0;
    padding: 0;
}

.multiselect__placeholder {
    margin-bottom: 0;
    padding-top: 0;
    padding-left: 0;
}

.multiselect__option--highlight {
    background: #3b82f6;
}

.multiselect__option--highlight::after {
    background: #3b82f6;
}

/* Custom scrollbar for modal */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>