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
            place: blotter.value.place,
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
            <form @submit.prevent="updateBlotterData">
                <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
                    <h1 class="text-2xl font-bold mb-6">Edit Blotter</h1>
                    <h2 class="text-lg font-semibold mb-4">Blotter Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="blotter_no" class="text-sm font-semibold text-gray-600">Blotter No</label>
                            <input id="blotter_no" type="text" v-model="blotter.blotter_no" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="filing_date" class="text-sm font-semibold text-gray-600">Filing Date</label>
                            <input id="filing_date" type="date" v-model="blotter.filing_date" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="title_case" class="text-sm font-semibold text-gray-600">Title Case</label>
                            <input id="title_case" type="text" v-model="blotter.title_case" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                        <label for="nature_of_case" class="text-sm font-semibold text-gray-600">Nature of Case</label>
                            <select 
                                id="nature_of_case" 
                                v-model="blotter.nature_of_case" 
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2"
                            >
                                <option value="" disabled>Select Nature of Case</option>
                                <option value="Civil case">Civil Case</option>
                                <option value="Criminal case">Criminal Case</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="complainants_id" class="text-sm font-semibold text-gray-600">Complainant</label>
                            <Multiselect
                                v-model="selectedComplainant"
                                :options="filteredComplainants"
                                :custom-label="resident => `${resident.first_name} ${resident.last_name} `"
                                track-by="id"
                                placeholder="Search or select complainant"
                                :searchable="true"
                                :show-labels="false"
                                @input="val => blotter.complainants_id = val?.id"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="complainant_address" class="text-sm font-semibold text-gray-600">Complainant Address</label>
                            <input 
                                id="complainant_address" 
                                type="text" 
                                placeholder="Complainant address will auto-fill" 
                                v-model="blotter.complainant_address" 
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2"
                                readonly
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="respondents_id" class="text-sm font-semibold text-gray-600">Respondent</label>
                            <Multiselect
                                v-model="selectedRespondent"
                                :options="filteredRespondents"
                                :custom-label="resident => `${resident.first_name} ${resident.last_name} `"
                                track-by="id"
                                placeholder="Search or select respondent"
                                :searchable="true"
                                :show-labels="false"
                                @input="val => blotter.respondents_id = val?.id"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="respondent_address" class="text-sm font-semibold text-gray-600">Respondent Address</label>
                            <input 
                                id="respondent_address" 
                                type="text" 
                                placeholder="Respondent address will auto-fill" 
                                v-model="blotter.respondent_address" 
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2"
                                readonly
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="place" class="text-sm font-semibold text-gray-600">Place</label>
                            <input id="place" type="text" v-model="blotter.place" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="datetime_of_incident" class="text-sm font-semibold text-gray-600">Date/Time of Incident</label>
                            <input id="datetime_of_incident" type="datetime-local" v-model="blotter.datetime_of_incident" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="blotter_type" class="text-sm font-semibold text-gray-600">Blotter Type</label>
                            <input id="blotter_type" type="text" v-model="blotter.blotter_type" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="barangay_case_no" class="text-sm font-semibold text-gray-600">Barangay Case No</label>
                            <input id="barangay_case_no" type="text" v-model="blotter.barangay_case_no" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="col-span-2 flex flex-col gap-2">
                            <label for="description" class="text-sm font-semibold text-gray-600">Description</label>
                            <textarea id="description" v-model="blotter.description" class="input-style col-span-2 border border-gray-200 rounded-md px-4 py-2" rows="4"></textarea>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="witness" class="text-sm font-semibold text-gray-600">Witness</label>
                            <textarea id="witness" v-model="blotter.witness" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="status" class="text-sm font-semibold text-gray-600">Status</label>
                            <select id="status" v-model="blotter.status" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                            </select>
                        </div>
                        
                        <!-- Existing Supporting Documents -->
                        <div class="col-span-2 flex flex-col gap-2" v-if="existingSupportingDocuments.length > 0">
                            <label class="text-sm font-semibold text-gray-600">Current Supporting Documents</label>
                            <div class="space-y-1 text-sm text-gray-700">
                                <div v-for="(doc, index) in existingSupportingDocuments" :key="index" class="flex items-center gap-2 justify-between bg-blue-50 rounded px-2 py-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-blue-600">📄</span>
                                        <span>{{ getFileName(doc) }}</span>
                                        <a v-if="doc.url" :href="doc.url" target="_blank" class="text-blue-500 hover:underline text-xs">(View)</a>
                                    </div>
                                    <button 
                                        type="button" 
                                        class="text-red-500 hover:underline"
                                        @click="removeExistingDocument(index)"
                                        aria-label="Remove existing document"
                                    >✖</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- New Supporting Documents Upload -->
                        <div class="col-span-2 flex flex-col gap-2">
                            <label for="new_supporting_documents" class="text-sm font-semibold text-gray-600">Add New Supporting Documents</label>
                            <input 
                                id="new_supporting_documents"
                                type="file" 
                                multiple 
                                class="hidden" 
                                @change="handleFileUpload" 
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                ref="fileInput"
                            />
                            <button
                                type="button"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md w-fit"
                                @click="$refs.fileInput.click()"
                            >
                                Upload Files
                            </button>
                            <div v-if="newSupportingDocuments.length" class="mt-2 space-y-1 text-sm text-gray-700">
                                <div v-for="(file, index) in newSupportingDocuments" :key="index" class="flex items-center gap-2 justify-between bg-green-50 rounded px-2 py-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-600">📄</span>
                                        <span>{{ file.name }}</span>
                                        <span class="text-green-600 text-xs">(New)</span>
                                    </div>
                                    <button 
                                        type="button" 
                                        class="text-red-500 hover:underline"
                                        @click="removeNewDocument(index)"
                                        aria-label="Remove new document"
                                    >✖</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-center mt-10 gap-4">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md">Save</button>
                        <button 
                             @click="handleCancel"
                            class="bg-white px-6 py-2 rounded-xl shadow-xl ml-4 font-bold hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                    </div>
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
  background: #3b82f6;
}

.multiselect__option--highlight::after {
  background: #3b82f6;
}
</style>