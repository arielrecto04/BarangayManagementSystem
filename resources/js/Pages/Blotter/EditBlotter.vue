<script setup>
import { useBlotterStore, useResidentStore } from '@/Stores'
import { ref, onMounted, watch, onBeforeUnmount, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';
import { storeToRefs } from 'pinia';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import { onBeforeRouteLeave } from 'vue-router';

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

const formatDateForDatetimeLocal = (dateString) => {
    if (!dateString) return '';

    if (dateString.includes('T') && dateString.match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/)) {
        return dateString.slice(0, 16);
    }

    if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return `${dateString}T00:00`;
    }

    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';

    return date.toISOString().slice(0, 16);
};

const existingSupportingDocuments = ref([]);
const newSupportingDocuments = ref([]);
const formErrors = ref({});
const isDestroyed = ref(false);
const isInitialized = ref(false);
const isDocumentOperationInProgress = ref(false);

// Computed properties for filtered lists
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

const updateBlotterField = async (field, value) => {
    if (blotter.value && !isDestroyed.value && isInitialized.value) {
        await nextTick();
        blotter.value[field] = value;
    }
};

// Debounced watchers to prevent rapid mutations
let complainantTimeout = null;
let respondentTimeout = null;
let documentUpdateTimeout = null;

// Debounce function for document operations
const debouncedDocumentUpdate = (operation) => {
    if (documentUpdateTimeout) {
        clearTimeout(documentUpdateTimeout);
    }

    documentUpdateTimeout = setTimeout(async () => {
        if (isDestroyed.value) return;

        isDocumentOperationInProgress.value = true;
        await nextTick();
        await operation();
        await nextTick();
        isDocumentOperationInProgress.value = false;
    }, 50);
};

watch(selectedComplainant, async (val) => {
    if (!isInitialized.value || isDestroyed.value || isDocumentOperationInProgress.value) {
        return;
    }

    if (complainantTimeout) {
        clearTimeout(complainantTimeout);
        complainantTimeout = null;
    }

    complainantTimeout = setTimeout(async () => {
        try {
            // Double-check state before proceeding
            if (isDestroyed.value || !blotter.value) return;

            await nextTick();

            // Ensure blotter still exists after nextTick
            if (!blotter.value || isDestroyed.value) return;

            blotter.value.complainants_id = val?.id ?? '';
            blotter.value.complainant_address = val?.address ?? '';

            // Prevent same person being both complainant and respondent
            if (val && selectedRespondent.value && val.id === selectedRespondent.value.id) {
                selectedRespondent.value = null;
                if (blotter.value) {
                    blotter.value.respondents_id = '';
                    blotter.value.respondent_address = '';
                }
            }
        } catch (error) {
            console.error('Error in complainant watcher:', error);
            // Don't throw - just log the error to prevent cascading failures
        }
    }, 150);
}, { immediate: false });

watch(selectedRespondent, async (val) => {
    if (!isInitialized.value || isDestroyed.value || isDocumentOperationInProgress.value) {
        return;
    }

    if (respondentTimeout) {
        clearTimeout(respondentTimeout);
        respondentTimeout = null;
    }

    respondentTimeout = setTimeout(async () => {
        try {
            // Double-check state before proceeding
            if (isDestroyed.value || !blotter.value) return;

            await nextTick();

            // Ensure blotter still exists after nextTick
            if (!blotter.value || isDestroyed.value) return;

            blotter.value.respondents_id = val?.id ?? '';
            blotter.value.respondent_address = val?.address ?? '';

            // Prevent same person being both complainant and respondent
            if (val && selectedComplainant.value && val.id === selectedComplainant.value.id) {
                selectedComplainant.value = null;
                if (blotter.value) {
                    blotter.value.complainants_id = '';
                    blotter.value.complainant_address = '';
                }
            }
        } catch (error) {
            console.error('Error in respondent watcher:', error);
            // Don't throw - just log the error to prevent cascading failures
        }
    }, 150);
}, { immediate: false });

const cleanup = () => {
    console.log('Cleanup initiated');
    isDestroyed.value = true;
    isInitialized.value = false;

    // Clear all timeouts
    if (complainantTimeout) {
        clearTimeout(complainantTimeout);
        complainantTimeout = null;
    }
    if (respondentTimeout) {
        clearTimeout(respondentTimeout);
        respondentTimeout = null;
    }
    if (documentUpdateTimeout) {
        clearTimeout(documentUpdateTimeout);
        documentUpdateTimeout = null;
    }

    // Reset all reactive refs
    selectedComplainant.value = null;
    selectedRespondent.value = null;
    existingSupportingDocuments.value = [];
    newSupportingDocuments.value = [];
    formErrors.value = {};
    isDocumentOperationInProgress.value = false;

    // Clear store state to prevent persistence issues
    blotterStore.resetBlotter();

    console.log('Cleanup completed');
};

onBeforeRouteLeave((to, from, next) => {
    console.log('Route leaving, cleaning up...');
    cleanup();
    next();
});

// Ensure cleanup happens before unmount
onBeforeUnmount(() => {
    cleanup();
});

onBeforeUnmount(() => {
    cleanup();
});

onMounted(async () => {
    try {
        await residentStore.getResidents();
        residents.value = residentStore.residents;

        await blotterStore.getBlotterById(blotterId);
        await nextTick();

        if (blotter.value && !isDestroyed.value) {
            blotter.value.filing_date = formatDateForDatetimeLocal(blotter.value.filing_date);
            blotter.value.datetime_of_incident = formatDateForInput(blotter.value.datetime_of_incident);

            // Load existing supporting documents with stable IDs
            if (blotter.value.supporting_documents && Array.isArray(blotter.value.supporting_documents)) {
                existingSupportingDocuments.value = blotter.value.supporting_documents.map((doc, index) => ({
                    ...doc,
                    _id: `existing_${Date.now()}_${index}_${getFileName(doc)}`
                }));
            }

            selectedComplainant.value = residents.value.find(r => r.id == blotter.value.complainants_id) || null;
            selectedRespondent.value = residents.value.find(r => r.id == blotter.value.respondents_id) || null;
        }

        isInitialized.value = true;

    } catch (error) {
        console.error('Mount error:', error);
        showToast({ icon: 'error', title: 'Failed to load blotter data' });
    }
});

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

const handleFileUpload = async (event) => {
    if (isDestroyed.value || isDocumentOperationInProgress.value) return;

    const newFiles = Array.from(event.target.files);

    // Add unique IDs to new files
    const filesWithIds = newFiles.map((file, index) => {
        const fileWithId = Object.assign(file, {
            _id: `new_${Date.now()}_${index}_${file.name}_${file.lastModified || Math.random()}`
        });
        return fileWithId;
    });

    const existingNames = new Set(
        newSupportingDocuments.value.map(file => file.name)
    );

    const uniqueNewFiles = filesWithIds.filter(file => !existingNames.has(file.name));

    if (uniqueNewFiles.length > 0) {
        debouncedDocumentUpdate(async () => {
            newSupportingDocuments.value = [
                ...newSupportingDocuments.value,
                ...uniqueNewFiles
            ];
        });
    }

    event.target.value = '';
};

const removeExistingDocument = async (doc) => {
    if (isDestroyed.value || isDocumentOperationInProgress.value) return;

    debouncedDocumentUpdate(async () => {
        existingSupportingDocuments.value = existingSupportingDocuments.value
            .filter(d => d._id !== doc._id);
    });
};

const removeNewDocument = async (file) => {
    if (isDestroyed.value || isDocumentOperationInProgress.value) return;

    debouncedDocumentUpdate(async () => {
        newSupportingDocuments.value = newSupportingDocuments.value
            .filter(f => f._id !== file._id);
    });
};

const updateBlotterData = async () => {
    try {
        if (!blotter.value || isDocumentOperationInProgress.value) return;

        console.log('=== UPDATE BLOTTER DEBUG ===');
        console.log('Existing documents:', existingSupportingDocuments.value);
        console.log('New documents:', newSupportingDocuments.value.length);

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

        const formData = new FormData();

        Object.entries(updateData).forEach(([key, value]) => {
            formData.append(key, value || '');
        });

        if (newSupportingDocuments.value.length > 0) {
            console.log(`Adding ${newSupportingDocuments.value.length} new files`);
            newSupportingDocuments.value.forEach((file, index) => {
                formData.append('supporting_documents[]', file);
                console.log(`New file ${index}:`, file.name);
            });
        }

        // Remove the _id properties before sending existing documents
        const cleanExistingDocuments = existingSupportingDocuments.value.map(doc => {
            const { _id, ...cleanDoc } = doc;
            return cleanDoc;
        });

        console.log('Existing documents being sent:', cleanExistingDocuments);
        formData.append('existing_documents', JSON.stringify(cleanExistingDocuments));

        console.log('Calling updateBlotter with FormData...');
        await blotterStore.updateBlotter(blotterId, formData);

        showToast({ icon: 'success', title: 'Blotter updated successfully' });
        cleanup();
        router.push('/blotter');
    } catch (error) {
        console.error('Update error:', error);
        showToast({ icon: 'error', title: error.message || 'Failed to update blotter' });
    }
};

const handleCancel = () => {
    cleanup();
    router.push('/blotter');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-start p-2 sm:p-6 lg:p-10">
        <template v-if="isLoading || !blotter || !isInitialized">
            <div class="flex justify-center items-center pt-20">
                <div
                    class="animate-spin rounded-full h-16 w-16 sm:h-24 sm:w-24 lg:h-32 lg:w-32 border-b-2 border-gray-900">
                </div>
            </div>
        </template>
        <template v-else>
            <form @submit.prevent="updateBlotterData"
                class="bg-white p-4 sm:p-6 lg:p-10 rounded-lg sm:rounded-xl shadow-md w-full max-w-4xl">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold mb-4 sm:mb-6">Edit Blotter</h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">

                    <!-- Complainant Searchable Dropdown -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm mb-1">Complainant</label>
                        <Multiselect v-model="selectedComplainant" :options="filteredComplainants"
                            :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                            placeholder="Search or select complainant" :searchable="true" :show-labels="false"
                            :allow-empty="true" :disabled="isDocumentOperationInProgress"
                            @input="val => blotter.complainants_id = val?.id" />
                    </div>

                    <!-- Respondent Searchable Dropdown -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm mb-1">Respondent</label>
                        <Multiselect v-model="selectedRespondent" :options="filteredRespondents"
                            :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                            placeholder="Search or select respondent" :searchable="true" :show-labels="false"
                            :allow-empty="true" :disabled="isDocumentOperationInProgress"
                            @input="val => blotter.respondents_id = val?.id" />
                    </div>

                    <!-- Nature of Case -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm">Nature of Case</label>
                        <select v-model="blotter.nature_of_case" class="border rounded-md p-2 text-sm"
                            :disabled="isDocumentOperationInProgress">
                            <option value="" disabled>Select Nature of Case</option>
                            <option value="civil case">Civil Case</option>
                            <option value="criminal case">Criminal Case</option>
                        </select>
                    </div>

                    <!-- Blotter Type -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm">Blotter Type</label>
                        <select v-model="blotter.blotter_type" class="border rounded-md p-2 text-sm"
                            :disabled="isDocumentOperationInProgress">
                            <option value="" disabled>Select Blotter Type</option>
                            <option value="Incident">Incident</option>
                            <option value="Complaint">Complaint</option>
                            <option value="Request">Request</option>
                        </select>
                    </div>

                    <!-- Blotter Number -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm">Blotter Number</label>
                        <input v-model="blotter.blotter_no" type="text" class="border rounded-md p-2 text-sm"
                            placeholder="Enter Blotter No" :disabled="isDocumentOperationInProgress" />
                    </div>

                    <!-- Barangay Case Number -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm">Barangay Case Number</label>
                        <input v-model="blotter.barangay_case_no" type="text" class="border rounded-md p-2 text-sm"
                            placeholder="Enter Case No" :disabled="isDocumentOperationInProgress" />
                    </div>

                    <!-- Title Case -->
                    <div class="flex flex-col sm:col-span-2">
                        <label class="font-semibold text-xs sm:text-sm">Title Case</label>
                        <input v-model="blotter.title_case" type="text" class="border rounded-md p-2 text-sm"
                            placeholder="Enter Title Case" :disabled="isDocumentOperationInProgress" />
                    </div>

                    <!-- Location of Incident -->
                    <div class="flex flex-col sm:col-span-2">
                        <label class="font-semibold text-xs sm:text-sm">Location of Incident</label>
                        <input v-model="blotter.incident_location" type="text" class="border rounded-md p-2 text-sm"
                            placeholder="Enter Location of Incident" :disabled="isDocumentOperationInProgress" />
                    </div>

                    <!-- Date & Time of Incident -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm">Date & Time of Incident</label>
                        <input type="datetime-local" v-model="blotter.datetime_of_incident"
                            class="border rounded-md p-2 text-sm" :disabled="isDocumentOperationInProgress" />
                    </div>

                    <!-- Filing Date & Time -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm">Filing Date & Time</label>
                        <input type="datetime-local" v-model="blotter.filing_date" class="border rounded-md p-2 text-sm"
                            :disabled="isDocumentOperationInProgress" />
                    </div>

                    <!-- Status -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm">Status</label>
                        <select v-model="blotter.status" class="border rounded-md p-2 text-sm"
                            :disabled="isDocumentOperationInProgress">
                            <option value="" disabled>Select Status</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>

                    <!-- Witness -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-xs sm:text-sm">Witness/es</label>
                        <textarea v-model="blotter.witness" class="border rounded-md p-2 text-sm h-20 sm:h-auto"
                            placeholder="Enter Witness Name" :disabled="isDocumentOperationInProgress"></textarea>
                    </div>

                    <!-- Description (full width) -->
                    <div class="flex flex-col sm:col-span-2">
                        <label class="font-semibold text-xs sm:text-sm">Description</label>
                        <textarea v-model="blotter.description" class="border rounded-md p-2 text-sm h-24 sm:h-auto"
                            placeholder="Enter Description" rows="4"
                            :disabled="isDocumentOperationInProgress"></textarea>
                    </div>

                    <!-- Supporting Documents Upload (full width) -->
                    <div class="flex flex-col sm:col-span-2">
                        <label class="font-semibold text-xs sm:text-sm mb-1">Supporting Documents</label>
                        <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileUpload"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                        <button type="button"
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-md w-full sm:w-fit disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                            :disabled="isDocumentOperationInProgress" @click="$refs.fileInput.click()">
                            {{ isDocumentOperationInProgress ? 'Processing...' : 'Upload Files' }}
                        </button>

                        <!-- Existing Supporting Documents -->
                        <div v-if="existingSupportingDocuments.length > 0 && !isDocumentOperationInProgress"
                            class="mt-2 space-y-1 text-xs sm:text-sm text-gray-700">
                            <div v-for="doc in existingSupportingDocuments" :key="doc._id"
                                class="flex items-center gap-2 justify-between bg-blue-50 rounded px-2 py-1">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <span class="text-blue-600 text-sm">📄</span>
                                    <span class="truncate">{{ getFileName(doc) }}</span>
                                    <a v-if="doc.url" :href="doc.url" target="_blank"
                                        class="text-blue-500 hover:underline text-xs whitespace-nowrap">(View)</a>
                                </div>
                                <button type="button" class="text-red-500 hover:underline flex-shrink-0 ml-2"
                                    @click="removeExistingDocument(doc)" aria-label="Remove existing document">
                                    ✖
                                </button>
                            </div>
                        </div>

                        <!-- New Supporting Documents -->
                        <div v-if="newSupportingDocuments.length && !isDocumentOperationInProgress"
                            class="mt-2 space-y-1 text-xs sm:text-sm text-gray-700">
                            <div v-for="file in newSupportingDocuments" :key="file._id"
                                class="flex items-center gap-2 justify-between bg-green-50 rounded px-2 py-1">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <span class="text-green-600 text-sm">📄</span>
                                    <span class="truncate">{{ file.name }}</span>
                                    <span class="text-green-600 text-xs whitespace-nowrap">(New)</span>
                                </div>
                                <button type="button"
                                    class="transition-transform duration-300 hover:rotate-180 flex-shrink-0 ml-2"
                                    @click="removeNewDocument(file)" aria-label="Remove new document">
                                    ✖
                                </button>
                            </div>
                        </div>

                        <!-- Loading indicator for document operations -->
                        <div v-if="isDocumentOperationInProgress"
                            class="mt-2 flex items-center gap-2 text-xs sm:text-sm text-gray-600">
                            <div class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-blue-600">
                            </div>
                            <span>Processing documents...</span>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end gap-2 sm:gap-4">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium order-1 sm:order-2"
                        :disabled="isDocumentOperationInProgress">
                        {{ isDocumentOperationInProgress ? 'Processing...' : 'Save' }}
                    </button>
                    <button @click="handleCancel" type="button"
                        class="bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium order-2 sm:order-1"
                        :disabled="isDocumentOperationInProgress">
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