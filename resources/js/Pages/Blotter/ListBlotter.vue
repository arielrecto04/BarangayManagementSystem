<script setup>
import { AuthLayout } from "@/Layouts";
import { Table } from '@/Components'
import { useRoute } from "vue-router";
import { useBlotterStore, useResidentStore } from '@/Stores'
import { storeToRefs } from 'pinia';
import { onMounted, ref } from "vue";
import useToast from '@/Utils/useToast';

const { showToast } = useToast();

const route = useRoute();
const blotterStore = useBlotterStore();
const residentStore = useResidentStore();
const { residents } = storeToRefs(residentStore);
const { blotters, isLoading } = storeToRefs(blotterStore);

const showModal = ref(false);
const selectedBlotter = ref(null);

const openModal = async (blotter) => {
    try {
        // Fetch detailed blotter data to get supporting documents
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

// Updated getResidentName function to handle both ID and name
const getResidentName = (id) => {
    if (!id) return 'N/A';
    const resident = residents.value.find(r => r.id == id);
    return resident ? `${resident.first_name} ${resident.last_name}` : 'N/A';
};

// Helper function to check if supporting documents exist and are valid
const getSupportingDocuments = (blotter) => {
    if (!blotter?.supporting_documents) return [];

    // Handle both array and string cases
    if (Array.isArray(blotter.supporting_documents)) {
        // New format: array of objects with 'path' and 'name'
        return blotter.supporting_documents.filter(doc => {
            // Handle new format (objects with path and name)
            if (typeof doc === 'object' && doc.path && doc.name) {
                return true;
            }
            // Handle old format (just strings)
            if (typeof doc === 'string') {
                return true;
            }
            return false;
        });
    }

    // If it's a string (JSON), try to parse it
    if (typeof blotter.supporting_documents === 'string') {
        try {
            const parsed = JSON.parse(blotter.supporting_documents);
            return Array.isArray(parsed) ? parsed.filter(doc => {
                if (typeof doc === 'object' && doc.path && doc.name) {
                    return true;
                }
                if (typeof doc === 'string') {
                    return true;
                }
                return false;
            }) : [];
        } catch (e) {
            console.warn('Failed to parse supporting documents:', e);
            return [];
        }
    }

    return [];
};

// Helper function to get filename - handles both old and new formats
const getFileName = (doc) => {
    // New format: object with name property
    if (typeof doc === 'object' && doc.name) {
        return doc.name;
    }
    // Old format: just file path string
    if (typeof doc === 'string') {
        return doc.split('/').pop() || 'Unknown file';
    }
    return 'Unknown file';
};

// Helper function to get file path - handles both old and new formats
const getFilePath = (doc) => {
    // New format: object with path property
    if (typeof doc === 'object' && doc.path) {
        return doc.path;
    }
    // Old format: just file path string
    if (typeof doc === 'string') {
        return doc;
    }
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

const deleteBlotter = async (blotterId) => {
    try {
        await blotterStore.deleteBlotter(blotterId);
        showToast({ icon: 'success', title: 'Blotter deleted successfully' });
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}

onMounted(() => {
    blotterStore.getBlotters();
    residentStore.getResidents(); // Make sure residents are loaded
})
</script>

<template>
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold text-gray-600">List of Blotters</h1>
        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>
        <Table v-else :columns="columns" :rows="blotters">
            <template #actions="{ row }">
                <div class="flex justify-center gap-2">
                    <router-link :to="`/blotter/edit-blotter/${row.id}`"
                        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">Edit</router-link>
                    <button @click="deleteBlotter(row.id)"
                        class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Delete</button>
                    <button @click="openModal(row)"
                        class="bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600">View</button>
                </div>
            </template>
        </Table>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="relative z-60 bg-white rounded-xl p-6 w-full max-w-4xl shadow-xl max-h-[90vh] overflow-y-auto">
                <!-- Close Button -->
                <button @click="closeModal"
                    class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">&times;</button>

                <!-- Modal Title -->
                <h2 class="text-lg font-bold mb-6 text-gray-700">Blotter Details</h2>

                <!-- Grid Layout for Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
                    <div>
                        <strong>Blotter No:</strong><br />
                        {{ selectedBlotter?.blotter_no }}
                    </div>
                    <div>
                        <strong>Filing Date:</strong><br />
                        {{ formatDate(selectedBlotter?.filing_date) }}
                    </div>
                    <div class="capitalize">
                        <strong>Title Case:</strong><br />
                        {{ selectedBlotter?.title_case }}
                    </div>
                    <div class="capitalize">
                        <strong>Nature of Case:</strong><br />
                        {{ selectedBlotter?.nature_of_case }}
                    </div>
                    <div>
                        <strong>Complainant:</strong><br />
                        {{ getResidentName(selectedBlotter?.complainants_id) }}
                        <span class="text-gray-500 text-xs">(ID: {{ String(selectedBlotter?.complainants_id).padStart(4,
                            '0')
                        }})</span>
                    </div>
                    <div>
                        <strong>Complainant Address:</strong><br />
                        {{ selectedBlotter?.complainant_address }}
                    </div>
                    <div>
                        <strong>Respondent:</strong><br />
                        {{ getResidentName(selectedBlotter?.respondents_id) }}
                        <span class="text-gray-500 text-xs">(ID: {{ String(selectedBlotter?.respondents_id).padStart(4,
                            '0')
                        }})</span>
                    </div>
                    <div>
                        <strong>Respondent Address:</strong><br />
                        {{ selectedBlotter?.respondent_address }}
                    </div>
                    <div>
                        <strong>Location of Incident:</strong><br />
                        {{ selectedBlotter?.place }}
                    </div>
                    <div>
                        <strong>Date/Time of Incident:</strong><br />
                        {{ formatDate(selectedBlotter?.datetime_of_incident) }}
                    </div>
                    <div>
                        <strong>Status:</strong><br />
                        <span :class="{
                            'text-green-600 font-semibold': selectedBlotter?.status === 'Resolved',
                            'text-yellow-600 font-semibold': selectedBlotter?.status === 'In Progress',
                            'text-red-600 font-semibold': selectedBlotter?.status === 'Open'
                        }">
                            {{ selectedBlotter?.status }}
                        </span>
                    </div>
                    <div>
                        <strong>Barangay Case No:</strong><br />
                        {{ selectedBlotter?.barangay_case_no }}
                    </div>

                    <div class="">
                        <strong class="block mb-2">Witness:</strong>
                        <div v-if="selectedBlotter?.witness" class="pl-2">
                            <ul class="list-disc pl-5 space-y-1">
                                <li v-for="(witness, index) in selectedBlotter.witness.split('\n').filter(name => name.trim())"
                                    :key="index" class="text-gray-800">
                                    {{ witness.trim() }}
                                </li>
                            </ul>
                        </div>
                        <div v-else class="text-gray-400 italic">
                            No witnesses listed
                        </div>
                    </div>
                    <div>
                        <strong>Blotter Type:</strong><br />
                        <span class="capitalize">{{ selectedBlotter?.blotter_type }}</span>
                    </div>

                    <div class="col-span-2">
                        <strong>Description:</strong><br />
                        <textarea readonly
                            class="w-full h-40 mt-1 p-2 border rounded bg-gray-50 resize-none overflow-y-auto text-sm leading-relaxed"
                            :value="selectedBlotter?.description"></textarea>
                    </div>

                    <!-- Supporting Documents Section -->
                    <div class="col-span-2">
                        <strong>Supporting Documents:</strong><br />
                        <div v-if="getSupportingDocuments(selectedBlotter).length > 0">
                            <ul class="list-disc pl-5">
                                <li v-for="(doc, index) in getSupportingDocuments(selectedBlotter)" :key="index">
                                    <a :href="`/storage/${getFilePath(doc)}`" target="_blank"
                                        class="text-blue-500 hover:underline">
                                        {{ getFileName(doc) }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="text-gray-500 italic">
                            No supporting documents available
                        </div>
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