<script setup>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import { onMounted, ref, watch, computed, onUnmounted, nextTick, onBeforeUnmount } from 'vue';
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router';
import { useComplaintStore, useResidentStore } from '@/Stores';
import useToast from '@/Utils/useToast';
import { axios } from '@/Utils';

const router = useRouter();
const route = useRoute();
const { showToast } = useToast();

const complaintStore = useComplaintStore();
const residentStore = useResidentStore();
const residents = ref([]);
const complaintId = route.params.id;
const isLoading = ref(true);
const isDestroyed = ref(false); // Track component destruction

const selectedComplainant = ref(null);
const selectedRespondent = ref(null);

const complaintForm = ref({
    complainant_name: '',
    respondent_name: '',
    case_no: '',
    title: '',
    description: '',
    resolution: '',
    filing_date: '',
    complainant_id: '',
    respondent_id: '',
    nature_of_complaint: '',
    incident_datetime: '',
    incident_location: '',
    supporting_documents: [],
    witness: '',
    status: '',
});

const existingSupportingDocuments = ref([]);
const newSupportingDocuments = ref([]);
const formErrors = ref({});

// Watchers array to store watcher cleanup functions
const watchers = [];

// Format datetime for input fields (convert from ISO to datetime-local format)
const formatDateTimeForInput = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toISOString().slice(0, 16);
};

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
    if (isDestroyed.value) return;
    const newFiles = Array.from(event.target.files);
    const existingNames = new Set(newSupportingDocuments.value.map(file => file.name));
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

// Exclude selected respondent from complainant list
const filteredComplainants = computed(() => {
    if (isDestroyed.value) return [];
    return residents.value.filter(resident =>
        selectedRespondent.value ? resident.id !== selectedRespondent.value.id : true
    );
});

// Exclude selected complainant from respondent list
const filteredRespondents = computed(() => {
    if (isDestroyed.value) return [];
    return residents.value.filter(resident =>
        selectedComplainant.value ? resident.id !== selectedComplainant.value.id : true
    );
});

// Safe watchers that check for component destruction
const complainantWatcher = watch(selectedComplainant, (val) => {
    if (isDestroyed.value || isLoading.value) return;
    complaintForm.value.complainant_id = val?.id ?? '';
});

const respondentWatcher = watch(selectedRespondent, (val) => {
    if (isDestroyed.value || isLoading.value) return;
    complaintForm.value.respondent_id = val?.id ?? '';
});

watchers.push(complainantWatcher, respondentWatcher);

// Cleanup function
const cleanup = () => {
    isDestroyed.value = true;
    watchers.forEach(stopWatcher => {
        if (typeof stopWatcher === 'function') {
            try {
                stopWatcher();
            } catch (error) {
                console.warn('Error stopping watcher:', error);
            }
        }
    });

    selectedComplainant.value = null;
    selectedRespondent.value = null;
    residents.value = [];
    existingSupportingDocuments.value = [];
    newSupportingDocuments.value = [];
    formErrors.value = {};
    isLoading.value = false;
};

// Navigation guard
onBeforeRouteLeave((to, from, next) => {
    if (isLoading.value) {
        if (confirm('Form is still processing. Are you sure you want to leave?')) {
            cleanup();
            next();
        } else {
            next(false);
        }
    } else {
        cleanup();
        next();
    }
});

onBeforeUnmount(() => {
    cleanup();
});

onUnmounted(() => {
    cleanup();
});

onMounted(async () => {
    try {
        isLoading.value = true;
        isDestroyed.value = false;

        await residentStore.getResidents();
        if (isDestroyed.value) return;
        residents.value = residentStore.residents;

        const response = await axios.get(`/complaints/${complaintId}`);
        if (isDestroyed.value) return;

        const complaintData = response.data;
        Object.assign(complaintForm.value, complaintData);

        if (complaintData.filing_date) {
            complaintForm.value.filing_date = formatDateTimeForInput(complaintData.filing_date);
        }
        if (complaintData.incident_datetime) {
            complaintForm.value.incident_datetime = formatDateTimeForInput(complaintData.incident_datetime);
        }

        if (complaintData.supporting_documents) {
            existingSupportingDocuments.value = Array.isArray(complaintData.supporting_documents)
                ? [...complaintData.supporting_documents]
                : [];
        }

        await nextTick();

        if (!isDestroyed.value) {
            if (complaintData.complainant_id) {
                selectedComplainant.value = residents.value.find(
                    r => String(r.id) === String(complaintData.complainant_id)
                );
            }
            if (complaintData.respondent_id) {
                selectedRespondent.value = residents.value.find(
                    r => String(r.id) === String(complaintData.respondent_id)
                );
            }
        }
    } catch (error) {
        console.error('Error in onMounted:', error);
        if (!isDestroyed.value) {
            formErrors.value.component = true;
            showToast({ icon: 'error', title: 'Error loading complaint data' });
        }
    } finally {
        if (!isDestroyed.value) {
            isLoading.value = false;
        }
    }
});

const handleCancel = async () => {
    cleanup();
    await nextTick();
    try {
        router.push('/complaints/list-complaints');
    } catch (error) {
        console.warn('Navigation error:', error);
    }
};

// ✅ New validation function
const validateForm = () => {
    formErrors.value = {};
    let isValid = true;

    const fields = {
        complainant_id: 'Complainant',
        respondent_id: 'Respondent',
        case_no: 'Case Number',
        title: 'Title',
        description: 'Description',
        resolution: 'Resolution',
        filing_date: 'Filing Date',
        nature_of_complaint: 'Nature of Complaint',
        incident_datetime: 'Date/Time of Incident',
        incident_location: 'Location of Incident',
        witness: 'Witness',
        status: 'Status',
    };

    for (const [field, label] of Object.entries(fields)) {
        if (!complaintForm.value[field]) {
            formErrors.value[field] = `${label} is required.`;
            isValid = false;
        }
    }

    if (
        complaintForm.value.complainant_id &&
        complaintForm.value.respondent_id &&
        complaintForm.value.complainant_id === complaintForm.value.respondent_id
    ) {
        formErrors.value.complainant_id = 'Complainant and Respondent cannot be the same.';
        formErrors.value.respondent_id = 'Complainant and Respondent cannot be the same.';
        isValid = false;
    }

    return isValid;
};

// ✅ Updated submitForm using validateForm
const submitForm = async () => {
    if (isLoading.value || isDestroyed.value) return;

    formErrors.value = {};

    if (!validateForm()) {
        showToast({ icon: 'error', title: 'Please fill in all required fields.' });
        return;
    }

    try {
        isLoading.value = true;

        const complainant = residents.value.find(r => r.id === complaintForm.value.complainant_id);
        const respondent = residents.value.find(r => r.id === complaintForm.value.respondent_id);

        complaintForm.value.complainant_name = complainant
            ? `${complainant.first_name} ${complainant.last_name}`
            : '';
        complaintForm.value.respondent_name = respondent
            ? `${respondent.first_name} ${respondent.last_name}`
            : '';

        const formData = new FormData();

        Object.entries(complaintForm.value).forEach(([key, value]) => {
            if (key !== 'supporting_documents') {
                formData.append(key, value || '');
            }
        });

        if (existingSupportingDocuments.value.length > 0) {
            formData.append('existing_documents', JSON.stringify(existingSupportingDocuments.value));
        }
        if (newSupportingDocuments.value.length > 0) {
            newSupportingDocuments.value.forEach(file => {
                formData.append('supporting_documents[]', file);
            });
        }

        formData.append('_method', 'PATCH');

        await complaintStore.updateComplaint(complaintId, formData);

        if (isDestroyed.value) return;

        showToast({ icon: 'success', title: 'Complaint updated successfully.' });
        await complaintStore.getComplaints(1);
        cleanup();
        await nextTick();
        await new Promise(resolve => setTimeout(resolve, 100));
        router.push('/complaints/list-complaints');

    } catch (error) {
        if (isDestroyed.value) return;

        if (error.response && error.response.status === 422 && error.response.data.errors) {
            const errors = error.response.data.errors;
            formErrors.value = {};
            for (const [field, messages] of Object.entries(errors)) {
                formErrors.value[field] = messages.join(' ');
            }
            const message = Object.values(errors).flat().join(' ');
            showToast({ icon: 'error', title: message });
        } else {
            showToast({ icon: 'error', title: error.message });
        }
    } finally {
        if (!isDestroyed.value) {
            isLoading.value = false;
        }
    }
};
</script>


<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>

        <div v-else-if="formErrors.component" class="text-red-500 text-center">
            <p>An error occurred loading the form. Please refresh the page.</p>
        </div>

        <form v-if="!isLoading && !formErrors.component && !isDestroyed" :key="`form-${complaintId}`"
            @submit.prevent="submitForm" class="bg-white p-10 rounded-xl shadow-md w-full max-w-4xl">

            <h1 class="text-2xl font-bold mb-6">Edit Complaint</h1>

            <div class="grid grid-cols-2 gap-4">
                <!-- Complainant Searchable Dropdown -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm mb-1">Complainant</label>
                    <Multiselect v-if="residents.length > 0 && !isDestroyed" v-model="selectedComplainant"
                        :options="filteredComplainants"
                        :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                        placeholder="Search or select complainant" :searchable="true" :show-labels="false"
                        :key="`complainant-${residents.length}`" />
                    <p v-if="formErrors.complainant_id" class="text-red-500 text-sm mt-1">{{ formErrors.complainant_id
                    }}</p>
                </div>

                <!-- Respondent Searchable Dropdown -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm mb-1">Respondent</label>
                    <Multiselect v-if="residents.length > 0 && !isDestroyed" v-model="selectedRespondent"
                        :options="filteredRespondents"
                        :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                        placeholder="Search or select respondent" :searchable="true" :show-labels="false"
                        :key="`respondent-${residents.length}`" />
                    <p v-if="formErrors.respondent_id" class="text-red-500 text-sm mt-1">{{ formErrors.respondent_id }}
                    </p>
                </div>

                <!-- Nature of Complaint -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Nature of Complaint</label>
                    <select v-model="complaintForm.nature_of_complaint" class="border rounded-md p-2">
                        <option value="Civil">Civil</option>
                        <option value="Criminal">Criminal</option>
                        <option value="Administrative">Administrative</option>
                        <option value="Domestic/Family Disputes">Domestic/Family Disputes</option>
                        <option value="Community & Public Order">Community & Public Order</option>
                        <option value="VAWC">VAWC</option>
                        <option value="Business | Economic">Business | Economic</option>
                    </select>
                    <p v-if="formErrors.nature_of_complaint" class="text-red-500 text-sm mt-1">{{
                        formErrors.nature_of_complaint }}</p>
                </div>

                <!-- Case Number -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Case Number</label>
                    <input v-model="complaintForm.case_no" type="text" class="border rounded-md p-2" />
                    <p v-if="formErrors.case_no" class="text-red-500 text-sm mt-1">{{ formErrors.case_no }}</p>
                </div>

                <!-- Title -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Title</label>
                    <input v-model="complaintForm.title" type="text" class="border rounded-md p-2" />
                    <p v-if="formErrors.title" class="text-red-500 text-sm mt-1">{{ formErrors.title }}</p>
                </div>

                <!-- Location of Incident -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Location of Incident</label>
                    <input v-model="complaintForm.incident_location" type="text" class="border rounded-md p-2" />
                    <p v-if="formErrors.incident_location" class="text-red-500 text-sm mt-1">{{
                        formErrors.incident_location }}</p>
                </div>

                <!-- Description -->
                <div class="flex flex-col col-span-2">
                    <label class="font-semibold text-sm">Description</label>
                    <textarea v-model="complaintForm.description" class="border rounded-md p-2"></textarea>
                    <p v-if="formErrors.description" class="text-red-500 text-sm mt-1">{{ formErrors.description }}</p>
                </div>

                <!-- Resolution -->
                <div class="flex flex-col col-span-2">
                    <label class="font-semibold text-sm">Resolution</label>
                    <textarea v-model="complaintForm.resolution" class="border rounded-md p-2"></textarea>
                    <p v-if="formErrors.resolution" class="text-red-500 text-sm mt-1">{{ formErrors.resolution }}</p>
                </div>

                <!-- Date & Time of Incident -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Date & Time of Incident</label>
                    <input type="datetime-local" v-model="complaintForm.incident_datetime"
                        class="border rounded-md p-2" />
                    <p v-if="formErrors.incident_datetime" class="text-red-500 text-sm mt-1">{{
                        formErrors.incident_datetime }}</p>
                </div>

                <!-- Filing Date & Time -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Filing Date & Time</label>
                    <input type="datetime-local" v-model="complaintForm.filing_date" class="border rounded-md p-2" />
                    <p v-if="formErrors.filing_date" class="text-red-500 text-sm mt-1">{{ formErrors.filing_date }}</p>
                </div>

                <!-- Status -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Status</label>
                    <select v-model="complaintForm.status" class="border rounded-md p-2">
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                    <p v-if="formErrors.status" class="text-red-500 text-sm mt-1">{{ formErrors.status }}</p>
                </div>

                <!-- Witness -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Witness</label>
                    <textarea v-model="complaintForm.witness" class="border rounded-md p-2"></textarea>
                </div>
            </div>

            <!-- Supporting Documents -->
            <div class="flex flex-col col-span-2">
                <label class="font-semibold text-sm mb-1">Supporting Documents</label>

                <!-- Existing documents -->
                <div v-if="existingSupportingDocuments.length > 0" class="mb-3">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Current Documents:</h4>
                    <div class="space-y-1 text-sm">
                        <div v-for="(doc, index) in existingSupportingDocuments" :key="`existing-${index}`"
                            class="flex items-center gap-2 p-2 bg-blue-50 rounded">
                            <span class="flex-1">{{ getFileName(doc) }}</span>
                            <a v-if="typeof doc === 'object' && doc.path" :href="`/storage/${doc.path}`" target="_blank"
                                class="text-blue-600 hover:underline text-xs">
                                View
                            </a>
                            <button type="button" class="text-red-500 hover:text-red-700 text-xs"
                                @click="removeExistingDocument(index)">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Upload new files -->
                <div>
                    <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileUpload"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />

                    <button type="button"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-fit mb-2"
                        @click="$refs.fileInput?.click()">
                        Upload Additional Files
                    </button>

                    <!-- Show newly selected files -->
                    <div v-if="newSupportingDocuments.length > 0" class="space-y-1 text-sm">
                        <h4 class="text-sm font-medium text-gray-700">New Files to Upload:</h4>
                        <div v-for="(file, index) in newSupportingDocuments" :key="`new-${index}`"
                            class="flex items-center gap-2 p-2 bg-green-50 rounded">
                            <span class="flex-1">{{ file.name }}</span>
                            <button type="button" class="text-red-500 hover:text-red-700 text-xs"
                                @click="removeNewDocument(index)">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end gap-4">
                <button type="submit" :disabled="isLoading || isDestroyed"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md disabled:opacity-50">
                    {{ isLoading ? 'Updating...' : 'Update Complaint' }}
                </button>
                <button type="button" @click="handleCancel" class="bg-gray-300 px-4 py-2 rounded-md"
                    :disabled="isLoading">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>

<style>
/* Match the existing input styling */
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
