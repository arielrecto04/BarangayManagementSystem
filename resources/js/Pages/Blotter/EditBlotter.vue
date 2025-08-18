<script setup>
import { useBlotterStore, useResidentStore } from '@/Stores'
import { ref, onMounted, watch, onBeforeUnmount, computed } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';
import { storeToRefs } from 'pinia';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const router = useRouter();
const { showToast } = useToast();

const blotterStore = useBlotterStore();
const residentStore = useResidentStore();
const residents = ref([]);
const selectedComplainant = ref(null);
const selectedRespondent = ref(null);

const { blotter, isLoading } = storeToRefs(blotterStore);
const blotterId = router.currentRoute.value.params.id;

const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().slice(0, 16);
};

const existingSupportingDocuments = ref([]);
const newSupportingDocuments = ref([]);
const formErrors = ref({});
const isDestroyed = ref(false);

// Computed properties for filtered lists (added from addblotter.vue)
const filteredComplainants = computed(() => {
    return residents.value.filter(resident =>
        !selectedRespondent.value || resident.id !== selectedRespondent.value.id
    );
});

const filteredRespondents = computed(() => {
    return residents.value.filter(resident =>
        !selectedComplainant.value || resident.id !== selectedComplainant.value.id
    );
});

// Watch for complainant selection and auto-fill address
watch(selectedComplainant, (val) => {
    if (blotter.value && !isDestroyed.value) {
        blotter.value.complainants_id = val?.id ?? '';
        blotter.value.complainant_address = val?.address ?? '';

        // Clear respondent if same as complainant (added from addblotter.vue)
        if (val && selectedRespondent.value && val.id === selectedRespondent.value.id) {
            selectedRespondent.value = null;
            blotter.value.respondents_id = '';
            blotter.value.respondent_address = '';
        }
    }
});

// Watch for respondent selection and auto-fill address
watch(selectedRespondent, (val) => {
    if (blotter.value && !isDestroyed.value) {
        blotter.value.respondents_id = val?.id ?? '';
        blotter.value.respondent_address = val?.address ?? '';

        // Clear complainant if same as respondent (added from addblotter.vue)
        if (val && selectedComplainant.value && val.id === selectedComplainant.value.id) {
            selectedComplainant.value = null;
            blotter.value.complainants_id = '';
            blotter.value.complainant_address = '';
        }
    }
});

// Add this cleanup function
const cleanup = () => {
    isDestroyed.value = true;
    selectedComplainant.value = null;
    selectedRespondent.value = null;
    blotterStore._blotter = null;
    existingSupportingDocuments.value = [];
    newSupportingDocuments.value = [];
};

const updateBlotterData = async () => {
    try {
        if (!blotter.value) return;

        // Prepare the update data - don't include supporting documents in the JSON payload
        const updateData = {
            blotter_no: blotter.value.blotter_no,
            filing_date: blotter.value.filing_date,
            title_case: blotter.value.title_case,
            nature_of_case: blotter.value.nature_of_case,
            complainants_id: blotter.value.complainants_id,
            respondents_id: blotter.value.respondents_id,
            complainant_address: blotter.value.complainant_address,
            respondent_address: blotter.value.respondent_address,
            complainants_name: 'App\\Models\\Resident',
            respondents_name: 'App\\Models\\Resident',
            incident_location: blotter.value.incident_location,
            datetime_of_incident: blotter.value.datetime_of_incident,
            blotter_type: blotter.value.blotter_type,
            barangay_case_no: blotter.value.barangay_case_no,
            total_cases: '0',
            status: blotter.value.status,
            description: blotter.value.description,
            witness: blotter.value.witness || ''
        };

        // Always use FormData to handle document changes properly
        const formData = new FormData();

        // Append all form fields
        Object.entries(updateData).forEach(([key, value]) => {
            formData.append(key, value || '');
        });

        // Append new files if any
        if (newSupportingDocuments.value.length > 0) {
            newSupportingDocuments.value.forEach(file => {
                formData.append('supporting_documents[]', file);
            });
        }

        // Always send existing documents info (even if empty array - this tells backend what to keep)
        formData.append('existing_documents', JSON.stringify(existingSupportingDocuments.value));

        await blotterStore.updateBlotter(blotterId, formData);

        showToast({ icon: 'success', title: 'Blotter updated successfully' });
        cleanup();
        router.push('/blotter');
    } catch (error) {
        console.error('Update error:', error);
        showToast({ icon: 'error', title: error.message || 'Failed to update blotter' });
    }
}

// Add beforeUnmount hook
onBeforeUnmount(() => {
    cleanup();
});

onMounted(async () => {
    try {
        await residentStore.getResidents();
        residents.value = residentStore.residents;
        await blotterStore.getBlotterById(blotterId);

        // Format dates after loading
        if (blotter.value) {
            blotter.value.filing_date = blotter.value.filing_date?.split('T')[0] || '';
            blotter.value.datetime_of_incident = formatDateForInput(blotter.value.datetime_of_incident);

            // Load existing supporting documents
            if (blotter.value.supporting_documents && Array.isArray(blotter.value.supporting_documents)) {
                existingSupportingDocuments.value = [...blotter.value.supporting_documents];
            }
        }
    } catch (error) {
        console.error('Mount error:', error);
        showToast({ icon: 'error', title: 'Failed to load blotter data' });
    }
});

watch(() => blotter.value, (newBlotter) => {
    if (newBlotter && !isDestroyed.value) {
        selectedComplainant.value = residents.value.find(r => r.id == newBlotter.complainants_id);
        selectedRespondent.value = residents.value.find(r => r.id == newBlotter.respondents_id);

        newBlotter.filing_date = newBlotter.filing_date?.split('T')[0] || '';
        newBlotter.datetime_of_incident = formatDateForInput(newBlotter.datetime_of_incident);

        // Load existing supporting documents
        if (newBlotter.supporting_documents && Array.isArray(newBlotter.supporting_documents)) {
            existingSupportingDocuments.value = [...newBlotter.supporting_documents];
        }
    }
}, { immediate: true });

// Helper function to get filename from document object
const getFileName = (doc) => {
    try {
        if (typeof doc === 'object' && doc && doc.name) {
            return doc.name;
        }
        if (typeof doc === 'string' && doc.length > 0) {
            return doc.split('/').pop() || 'Unknown file';
        }
        return 'Unknown file';
    } catch (error) {
        console.error('Error getting filename:', error);
        return 'Unknown file';
    }
};

const handleFileUpload = (event) => {
    if (isDestroyed.value) return; // Prevent operations after destruction

    const newFiles = Array.from(event.target.files);

    const existingNames = new Set(
        newSupportingDocuments.value.map(file => file.name)
    );

    const uniqueNewFiles = newFiles.filter(file => !existingNames.has(file.name));

    newSupportingDocuments.value = [
        ...newSupportingDocuments.value,
        ...uniqueNewFiles
    ];

    event.target.value = '';
};

const removeExistingDocument = (index) => {
    if (isDestroyed.value) return;
    existingSupportingDocuments.value.splice(index, 1);
};

const removeNewDocument = (index) => {
    if (isDestroyed.value) return;
    newSupportingDocuments.value.splice(index, 1);
};

const handleCancel = () => {
    cleanup();
    router.push('/blotter');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <template v-if="isLoading || !blotter">
            <div class="flex justify-center items-center">
                <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
            </div>
        </template>
        <template v-else>
            <form @submit.prevent="updateBlotterData" class="bg-white p-10 rounded-xl shadow-md w-full max-w-4xl">
                <h1 class="text-2xl font-bold mb-6">Edit Blotter</h1>

                <div class="grid grid-cols-2 gap-4">

                    <!-- Complainant Searchable Dropdown -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm mb-1">Complainant</label>
                        <Multiselect v-model="selectedComplainant" :options="filteredComplainants"
                            :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                            placeholder="Search or select complainant" :searchable="true" :show-labels="false"
                            :allow-empty="true" @input="val => blotter.complainants_id = val?.id" />
                    </div>

                    <!-- Respondent Searchable Dropdown -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm mb-1">Respondent</label>
                        <Multiselect v-model="selectedRespondent" :options="filteredRespondents"
                            :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                            placeholder="Search or select respondent" :searchable="true" :show-labels="false"
                            :allow-empty="true" @input="val => blotter.respondents_id = val?.id" />
                    </div>

                    <!-- Nature of Case -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Nature of Case</label>
                        <select v-model="blotter.nature_of_case" class="border rounded-md p-2">
                            <option value="" disabled>Select Nature of Case</option>
                            <option value="civil case">Civil Case</option>
                            <option value="criminal case">Criminal Case</option>
                        </select>
                    </div>

                    <!-- Blotter Type -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Blotter Type</label>
                        <select v-model="blotter.blotter_type" class="border rounded-md p-2">
                            <option value="" disabled>Select Blotter Type</option>
                            <option value="Incident">Incident</option>
                            <option value="Complaint">Complaint</option>
                            <option value="Request">Request</option>
                        </select>
                    </div>

                    <!-- Blotter Number -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Blotter Number</label>
                        <input v-model="blotter.blotter_no" type="text" class="border rounded-md p-2"
                            placeholder="Enter Blotter No" />
                    </div>

                    <!-- Barangay Case Number -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Barangay Case Number</label>
                        <input v-model="blotter.barangay_case_no" type="text" class="border rounded-md p-2"
                            placeholder="Enter Case No" />
                    </div>

                    <!-- Title Case -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Title Case</label>
                        <input v-model="blotter.title_case" type="text" class="border rounded-md p-2"
                            placeholder="Enter Title Case" />
                    </div>

                    <!-- Location of Incident -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Location of Incident</label>
                        <input v-model="blotter.incident_location" type="text" class="border rounded-md p-2"
                            placeholder="Enter Location of Incident" />
                    </div>

                    <!-- Date & Time of Incident -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Date & Time of Incident</label>
                        <input type="datetime-local" v-model="blotter.datetime_of_incident"
                            class="border rounded-md p-2" />
                    </div>

                    <!-- Filing Date & Time -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Filing Date & Time</label>
                        <input type="datetime-local" v-model="blotter.filing_date" class="border rounded-md p-2" />
                    </div>

                    <!-- Status -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Status</label>
                        <select v-model="blotter.status" class="border rounded-md p-2">
                            <option value="" disabled>Select Status</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>

                    <!-- Witness -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-sm">Witness/es</label>
                        <textarea v-model="blotter.witness" class="border rounded-md p-2"
                            placeholder="Enter Witness Name"></textarea>
                    </div>

                    <!-- Description (full width) -->
                    <div class="flex flex-col col-span-2">
                        <label class="font-semibold text-sm">Description</label>
                        <textarea v-model="blotter.description" class="border rounded-md p-2"
                            placeholder="Enter Description" rows="4"></textarea>
                    </div>

                    <!-- Supporting Documents Upload (full width) -->
                    <div class="flex flex-col col-span-2">
                        <label class="font-semibold text-sm mb-1">Supporting Documents</label>
                        <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileUpload"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                        <button type="button"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-fit"
                            @click="$refs.fileInput.click()">
                            Upload Files
                        </button>

                        <!-- Existing Supporting Documents -->
                        <div v-if="existingSupportingDocuments.length > 0" class="mt-2 space-y-1 text-sm text-gray-700">
                            <div v-for="(doc, index) in existingSupportingDocuments" :key="index"
                                class="flex items-center gap-2 justify-between bg-blue-50 rounded px-2 py-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-blue-600">📄</span>
                                    <span>{{ getFileName(doc) }}</span>
                                    <a v-if="doc.url" :href="doc.url" target="_blank"
                                        class="text-blue-500 hover:underline text-xs">(View)</a>
                                </div>
                                <button type="button" class="text-red-500 hover:underline"
                                    @click="removeExistingDocument(index)" aria-label="Remove existing document">
                                    ✖
                                </button>
                            </div>
                        </div>

                        <!-- New Supporting Documents -->
                        <div v-if="newSupportingDocuments.length" class="mt-2 space-y-1 text-sm text-gray-700">
                            <div v-for="(file, index) in newSupportingDocuments" :key="index"
                                class="flex items-center gap-2 justify-between bg-green-50 rounded px-2 py-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">📄</span>
                                    <span>{{ file.name }}</span>
                                    <span class="text-green-600 text-xs">(New)</span>
                                </div>
                                <button type="button" class="transition-transform duration-300 hover:rotate-180"
                                    @click="removeNewDocument(index)" aria-label="Remove new document">
                                    ✖
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex justify-end gap-4">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">Save</button>
                    <button @click="handleCancel" type="button"
                        class="bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </template>
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
    background: #16A34A;
}

.multiselect__option--highlight::after {
    background: #16A34A;
}
</style>
