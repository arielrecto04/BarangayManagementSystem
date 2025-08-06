<script setup>
import { useBlotterStore, useResidentStore } from '@/Stores'
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
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
const blotterDataForm = ref({
    blotter_no: '',
    filing_date: '',
    title_case: '',
    nature_of_case: '',
    complainants_id: '',
    respondents_id: '',
    complainants_type: 'App\\Models\\Resident',
    respondents_type: 'App\\Models\\Resident',
    place: '',
    datetime_of_incident: '',
    blotter_type: '',
    barangay_case_no: '',
    total_cases: '0',
    open_cases: '0',
    in_progress: '0',
    resolved: '0',
    status: '',
    description: '',
    witness: '',
    supporting_documents: []
});
const formatDateForInput = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toISOString().slice(0, 16);
};
const existingSupportingDocuments = ref([]);
const newSupportingDocuments = ref([]);
const formErrors = ref({});
const isDestroyed = ref(false);

// Add this cleanup function
const cleanup = () => {
    selectedComplainant.value = null;
    selectedRespondent.value = null;
    blotterStore._blotter = null;
    isDestroyed.value = true;
};

const updateResidentData  = async () => {
    try {
        if (!blotter.value) return;

        // If there are new documents to upload, create FormData
        if (newSupportingDocuments.value.length > 0) {
            const formData = new FormData();

            // Add all blotter fields
            Object.entries(blotter.value).forEach(([key, value]) => {
                if (key !== 'supporting_documents' && value !== null && value !== undefined) {
                    formData.append(key, value);
                }
            });

            // Add new supporting documents
            newSupportingDocuments.value.forEach(file => {
                formData.append('supporting_documents[]', file);
            });

            await blotterStore.updateBlotter(blotterId, formData);
        } else {
            // No new files, just update the data
            await blotterStore.updateBlotter(blotterId, blotter.value);
        }

        showToast({ icon: 'success', title: 'Blotter updated successfully' });
        cleanup();
        router.push('/blotter');
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}

// Add beforeUnmount hook
onBeforeUnmount(() => {
    isDestroyed.value = true;
    cleanup();
});

onMounted(async () => {
    await residentStore.getResidents();
    residents.value = residentStore.residents;
    await blotterStore.getBlotterById(blotterId);
    // Format dates after loading
     if (blotter.value) {
        blotter.value.filing_date = blotter.value.filing_date?.split('T')[0] || '';
        blotter.value.datetime_of_incident = formatDateForInput(blotter.value.datetime_of_incident);
    }
});

watch(() => blotter.value, (newBlotter) => {
    if (newBlotter) {
        selectedComplainant.value = residents.value.find(r => r.id == newBlotter.complainants_id);
        selectedRespondent.value = residents.value.find(r => r.id == newBlotter.respondents_id);

        newBlotter.filing_date = newBlotter.filing_date?.split('T')[0] || '';
        newBlotter.datetime_of_incident = formatDateForInput(newBlotter.datetime_of_incident);

        // Handle existing supporting documents
        if (newBlotter.supporting_documents && Array.isArray(newBlotter.supporting_documents)) {
            existingSupportingDocuments.value = [...newBlotter.supporting_documents];
        } else {
            existingSupportingDocuments.value = [];
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
  blotterDataForm.value.supporting_documents = [
    ...blotterDataForm.value.supporting_documents || [],
    ...newFiles
  ];

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
  // Clean up Multiselect references
  selectedComplainant.value = null;
  selectedRespondent.value = null;

  // Reset the current blotter in store
  blotterStore._blotter = null;

  // Mark as destroyed
  isDestroyed.value = true;

  // Navigate back to parent blotter route
  router.push('/blotter');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <template v-if="isLoading || !resident">
            <div class="flex justify-center items-center">
                <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
            </div>
        </template>
        <template v-else>
            <form @submit.prevent="updateResidentData">
                <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
                    <h1 class="text-2xl font-bold mb-6">Edit Resident</h1>
                    <h2 class="text-lg font-semibold mb-4">Resident Profile</h2>

                    <div class="grid grid-cols-3 gap-4">
                        <!-- First Row -->

                        <div class="flex flex-col gap-2">
                            <label for="first_name" class="text-sm font-semibold text-gray-600">First Name</label>
                            <input type="text" placeholder="First Name" v-model="resident.first_name"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />

                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="last_name" class="text-sm font-semibold text-gray-600">Last Name</label>
                            <input type="text" placeholder="Last Name" v-model="resident.last_name"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="row-span-3 flex justify-center items-center">
                            <div class="w-50 h-50 bg-gray-200 rounded-md"></div>
                        </div>

                        <!-- Second Row -->
                        <div class="flex flex-col gap-2">
                            <label for="birthday" class="text-sm font-semibold text-gray-600">Birthday</label>
                            <input type="date" placeholder="Birthday" v-model="resident.birthday"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="age" class="text-sm font-semibold text-gray-600">Age</label>
                            <input type="number" placeholder="Age" v-model="resident.age"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
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
                                :options="residents"
                                :custom-label="resident => `${resident.first_name} ${resident.last_name} `"
                                track-by="id"
                                placeholder="Search or select complainant"
                                :searchable="true"
                                :show-labels="false"
                                @input="val => blotter.complainants_id = val?.id"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="respondents_id" class="text-sm font-semibold text-gray-600">Respondent</label>
                            <Multiselect
                                v-model="selectedRespondent"
                                :options="residents"
                                :custom-label="resident => `${resident.first_name} ${resident.last_name} `"
                                track-by="id"
                                placeholder="Search or select respondent"
                                :searchable="true"
                                :show-labels="false"
                                @input="val => blotter.respondents_id = val?.id"
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
                            <textarea id="witness" type="text" v-model="blotter.witness" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2 justify-center">
                            <label for="status" class="text-sm font-semibold text-gray-600">Status</label>
                            <select id="status" v-model="blotter.status" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                            </select>
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
                    </div>
                </div>
            </form>
        </template>
    </div>
</template>
