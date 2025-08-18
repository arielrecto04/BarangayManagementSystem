<script setup>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import { onMounted, ref, watch, computed, onUnmounted, nextTick, onBeforeUnmount } from 'vue';
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router';
import { useComplaintStore, useResidentStore } from '@/Stores';
import useToast from '@/Utils/useToast';
import { axios } from '@/utils';

const router = useRouter();
const route = useRoute();
const { showToast } = useToast();

const complaintStore = useComplaintStore();
const residentStore = useResidentStore();

// FIXED: Better route parameter handling
const complaintId = computed(() => route.params.id);
const residents = ref([]);
const isLoading = ref(true);
const isDestroyed = ref(false);
const isMounted = ref(false);
const isInitialized = ref(false);

// FIXED: Simplified component state management
let isComponentActive = true;

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

// Format datetime for input fields
const formatDateTimeForInput = (isoString) => {
    if (!isoString) return '';
    try {
        const date = new Date(isoString);
        return date.toISOString().slice(0, 16);
    } catch (error) {
        console.error('Date formatting error:', error);
        return '';
    }
};

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
    if (!isComponentActive || isDestroyed.value) return;

    try {
        const newFiles = Array.from(event.target.files);
        const existingNames = new Set(newSupportingDocuments.value.map(file => file.name));
        const uniqueNewFiles = newFiles.filter(file => !existingNames.has(file.name));

        newSupportingDocuments.value = [
            ...newSupportingDocuments.value,
            ...uniqueNewFiles
        ];

        event.target.value = '';
    } catch (error) {
        console.error('File upload error:', error);
    }
};

const removeExistingDocument = (index) => {
    if (!isComponentActive || isDestroyed.value) return;
    existingSupportingDocuments.value.splice(index, 1);
};

const removeNewDocument = (index) => {
    if (!isComponentActive || isDestroyed.value) return;
    newSupportingDocuments.value.splice(index, 1);
};

// Computed properties with safety checks
const filteredComplainants = computed(() => {
    if (isDestroyed.value || !isInitialized.value) return [];
    return residents.value.filter(resident =>
        selectedRespondent.value ? Number(resident.id) !== Number(selectedRespondent.value.id) : true
    );
});

const filteredRespondents = computed(() => {
    if (isDestroyed.value || !isInitialized.value) return [];
    return residents.value.filter(resident =>
        selectedComplainant.value ? Number(resident.id) !== Number(selectedComplainant.value.id) : true
    );
});

// FIXED: Safer watchers with better conditions
const setupWatchers = () => {
    const complainantWatcher = watch(selectedComplainant, (val) => {
        if (!isComponentActive || isDestroyed.value || isLoading.value || !isInitialized.value) return;

        nextTick(() => {
            if (isComponentActive && !isDestroyed.value && isInitialized.value) {
                complaintForm.value.complainant_id = val?.id ?? '';
            }
        });
    }, { flush: 'post' });

    const respondentWatcher = watch(selectedRespondent, (val) => {
        if (!isComponentActive || isDestroyed.value || isLoading.value || !isInitialized.value) return;

        nextTick(() => {
            if (isComponentActive && !isDestroyed.value && isInitialized.value) {
                complaintForm.value.respondent_id = val?.id ?? '';
            }
        });
    }, { flush: 'post' });

    watchers.push(complainantWatcher, respondentWatcher);
};

// FIXED: Comprehensive cleanup function
const cleanup = () => {
    console.log('EditComplaint: Starting cleanup...');

    isDestroyed.value = true;
    isMounted.value = false;
    isInitialized.value = false;
    isComponentActive = false;

    // Stop all watchers first
    watchers.forEach(stopWatcher => {
        if (typeof stopWatcher === 'function') {
            try {
                stopWatcher();
            } catch (error) {
                console.warn('Error stopping watcher:', error);
            }
        }
    });
    watchers.length = 0; // Clear the array

    // Clear refs safely
    selectedComplainant.value = null;
    selectedRespondent.value = null;
    residents.value = [];
    existingSupportingDocuments.value = [];
    newSupportingDocuments.value = [];
    formErrors.value = {};

    // Clear store state if available
    if (residentStore.clearState && typeof residentStore.clearState === 'function') {
        try {
            residentStore.clearState();
        } catch (error) {
            console.warn('Error clearing resident store state:', error);
        }
    }

    isLoading.value = false;
};

// FIXED: Proper lifecycle hook registration
onBeforeUnmount(() => {
    cleanup();
});

onUnmounted(() => {
    cleanup();
});

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

// FIXED: Streamlined initialization process
const initializeComponent = async () => {
    try {
        console.log('EditComplaint: Starting initialization for complaint ID:', complaintId.value);

        if (!complaintId.value) {
            throw new Error('No complaint ID provided');
        }

        isLoading.value = true;
        isDestroyed.value = false;
        isComponentActive = true;

        // Load residents first
        console.log('EditComplaint: Loading residents...');
        if (residentStore.residents.length === 0) {
            await residentStore.getResidents();
        }

        if (!isComponentActive || isDestroyed.value) {
            console.log('EditComplaint: Component destroyed during resident loading');
            return;
        }

        residents.value = [...residentStore.residents];
        console.log('EditComplaint: Loaded residents count:', residents.value.length);

        // Load complaint data
        console.log('EditComplaint: Loading complaint data...');
        const response = await axios.get(`/complaints/${complaintId.value}`);

        if (!isComponentActive || isDestroyed.value) {
            console.log('EditComplaint: Component destroyed during complaint loading');
            return;
        }

        const complaintData = response.data;
        console.log('EditComplaint: Loaded complaint data:', complaintData);

        // Populate form
        Object.assign(complaintForm.value, complaintData);

        // Format dates
        if (complaintData.filing_date) {
            complaintForm.value.filing_date = formatDateTimeForInput(complaintData.filing_date);
        }
        if (complaintData.incident_datetime) {
            complaintForm.value.incident_datetime = formatDateTimeForInput(complaintData.incident_datetime);
        }

        // Handle supporting documents
        if (complaintData.supporting_documents) {
            existingSupportingDocuments.value = Array.isArray(complaintData.supporting_documents)
                ? [...complaintData.supporting_documents]
                : [];
        }

        // Wait for DOM update
        await nextTick();

        if (!isComponentActive || isDestroyed.value) {
            console.log('EditComplaint: Component destroyed during DOM update');
            return;
        }

        // Set up resident selections
        if (complaintData.complainant_id) {
            selectedComplainant.value = residents.value.find(
                r => Number(r.id) === Number(complaintData.complainant_id)
            ) || null;
            console.log('EditComplaint: Set complainant:', selectedComplainant.value);
        }

        if (complaintData.respondent_id) {
            selectedRespondent.value = residents.value.find(
                r => Number(r.id) === Number(complaintData.respondent_id)
            ) || null;
            console.log('EditComplaint: Set respondent:', selectedRespondent.value);
        }

        // Final setup
        await nextTick();
        if (isComponentActive && !isDestroyed.value) {
            isInitialized.value = true;
            isMounted.value = true;
            setupWatchers();
            console.log('EditComplaint: Initialization complete');
        }

    } catch (error) {
        console.error('EditComplaint: Error during initialization:', error);
        if (isComponentActive && !isDestroyed.value) {
            formErrors.value.component = true;
            showToast({ icon: 'error', title: 'Error loading complaint data: ' + error.message });
        }
    } finally {
        if (isComponentActive && !isDestroyed.value) {
            isLoading.value = false;
        }
    }
};

// FIXED: Clean onMounted implementation
onMounted(async () => {
    console.log('EditComplaint: Component mounted');
    await initializeComponent();
});

// FIXED: Better route change handling
watch(() => route.params.id, async (newId, oldId) => {
    if (newId && newId !== oldId && !isDestroyed.value) {
        console.log('EditComplaint: Route changed, reinitializing...');
        await initializeComponent();
    }
});

const handleCancel = async () => {
    console.log('EditComplaint: Cancel requested');
    cleanup();
    await nextTick();
    try {
        await router.push('/complaints/list-complaints');
    } catch (error) {
        console.warn('Navigation error:', error);
    }
};

// Validation function
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
        Number(complaintForm.value.complainant_id) === Number(complaintForm.value.respondent_id)
    ) {
        formErrors.value.complainant_id = 'Complainant and Respondent cannot be the same.';
        formErrors.value.respondent_id = 'Complainant and Respondent cannot be the same.';
        isValid = false;
    }

    return isValid;
};

// Enhanced submitForm with better state management
const submitForm = async () => {
    if (isLoading.value || isDestroyed.value || !isComponentActive || !isInitialized.value) {
        console.log('EditComplaint: Submit blocked - component not ready');
        return;
    }

    formErrors.value = {};

    if (!validateForm()) {
        showToast({ icon: 'error', title: 'Please fill in all required fields.' });
        return;
    }

    try {
        console.log('EditComplaint: Starting form submission...');
        isLoading.value = true;

        // Use the safe method to get residents if available
        const complainant = residentStore.getResidentByIdSync
            ? residentStore.getResidentByIdSync(complaintForm.value.complainant_id)
            : residents.value.find(r => Number(r.id) === Number(complaintForm.value.complainant_id));

        const respondent = residentStore.getResidentByIdSync
            ? residentStore.getResidentByIdSync(complaintForm.value.respondent_id)
            : residents.value.find(r => Number(r.id) === Number(complaintForm.value.respondent_id));

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

        await complaintStore.updateComplaint(complaintId.value, formData);

        if (isDestroyed.value || !isComponentActive) return;

        console.log('EditComplaint: Form submitted successfully');
        showToast({ icon: 'success', title: 'Complaint updated successfully.' });
        await complaintStore.getComplaints(1);

        cleanup();
        await nextTick();
        await new Promise(resolve => setTimeout(resolve, 100));
        await router.push('/complaints/list-complaints');

    } catch (error) {
        console.error('EditComplaint: Form submission error:', error);
        if (isDestroyed.value || !isComponentActive) return;

        if (error.response && error.response.status === 422 && error.response.data.errors) {
            const errors = error.response.data.errors;
            formErrors.value = {};
            for (const [field, messages] of Object.entries(errors)) {
                formErrors.value[field] = messages.join(' ');
            }
            const message = Object.values(errors).flat().join(' ');
            showToast({ icon: 'error', title: message });
        } else {
            showToast({ icon: 'error', title: error.message || 'An error occurred during submission' });
        }
    } finally {
        if (!isDestroyed.value && isComponentActive) {
            isLoading.value = false;
        }
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex justify-center items-center min-h-screen">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="formErrors.component" class="text-center py-8">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto">
                <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-semibold text-red-800 mb-2">Failed to Load Complaint</h3>
                <p class="text-red-600 mb-4">There was an error loading the complaint data. Please try again.</p>
                <button @click="initializeComponent" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Retry
                </button>
            </div>
        </div>

        <!-- Main Form -->
        <form v-else-if="isMounted && isInitialized && !isDestroyed" @submit.prevent="submitForm"
            class="bg-white p-10 rounded-xl shadow-md w-full max-w-4xl">
            <h1 class="text-2xl font-bold mb-6">Edit Complaint</h1>

            <div class="grid grid-cols-2 gap-4">
                <!-- Complainant Searchable Dropdown -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm mb-1">Complainant</label>
                    <Multiselect v-model="selectedComplainant" :options="filteredComplainants"
                        :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                        placeholder="Search or select complainant" :searchable="true" :show-labels="false"
                        :disabled="isLoading || isDestroyed">
                        <template #option="{ option }">
                            <div class="flex justify-between items-center">
                                <span>{{ option.first_name }} {{ option.last_name }}</span>
                                <span class="text-xs text-gray-500">#{{ option.resident_number }}</span>
                            </div>
                        </template>
                    </Multiselect>
                    <p v-if="formErrors.complainant_id" class="text-red-500 text-sm mt-1">{{ formErrors.complainant_id
                        }}</p>
                </div>

                <!-- Respondent Searchable Dropdown -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm mb-1">Respondent</label>
                    <Multiselect v-model="selectedRespondent" :options="filteredRespondents"
                        :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                        placeholder="Search or select respondent" :searchable="true" :show-labels="false"
                        :disabled="isLoading || isDestroyed">
                        <template #option="{ option }">
                            <div class="flex justify-between items-center">
                                <span>{{ option.first_name }} {{ option.last_name }}</span>
                                <span class="text-xs text-gray-500">#{{ option.resident_number }}</span>
                            </div>
                        </template>
                    </Multiselect>
                    <p v-if="formErrors.respondent_id" class="text-red-500 text-sm mt-1">{{ formErrors.respondent_id }}
                    </p>
                </div>

                <!-- Nature of Complaint -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Nature of Complaint</label>
                    <select v-model="complaintForm.nature_of_complaint" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed">
                        <option value="">Select nature of complaint</option>
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
                    <input v-model="complaintForm.case_no" type="text" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed" />
                    <p v-if="formErrors.case_no" class="text-red-500 text-sm mt-1">{{ formErrors.case_no }}</p>
                </div>

                <!-- Title -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Title</label>
                    <input v-model="complaintForm.title" type="text" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed" />
                    <p v-if="formErrors.title" class="text-red-500 text-sm mt-1">{{ formErrors.title }}</p>
                </div>

                <!-- Location of Incident -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Location of Incident</label>
                    <input v-model="complaintForm.incident_location" type="text" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed" />
                    <p v-if="formErrors.incident_location" class="text-red-500 text-sm mt-1">{{
                        formErrors.incident_location }}</p>
                </div>

                <!-- Description -->
                <div class="flex flex-col col-span-2">
                    <label class="font-semibold text-sm">Description</label>
                    <textarea v-model="complaintForm.description" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed"></textarea>
                    <p v-if="formErrors.description" class="text-red-500 text-sm mt-1">{{ formErrors.description }}</p>
                </div>

                <!-- Resolution -->
                <div class="flex flex-col col-span-2">
                    <label class="font-semibold text-sm">Resolution</label>
                    <textarea v-model="complaintForm.resolution" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed"></textarea>
                    <p v-if="formErrors.resolution" class="text-red-500 text-sm mt-1">{{ formErrors.resolution }}</p>
                </div>

                <!-- Date & Time of Incident -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Date & Time of Incident</label>
                    <input type="datetime-local" v-model="complaintForm.incident_datetime" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed" />
                    <p v-if="formErrors.incident_datetime" class="text-red-500 text-sm mt-1">{{
                        formErrors.incident_datetime }}</p>
                </div>

                <!-- Filing Date -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Filing Date & Time</label>
                    <input type="datetime-local" v-model="complaintForm.filing_date" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed" />
                    <p v-if="formErrors.filing_date" class="text-red-500 text-sm mt-1">{{ formErrors.filing_date }}</p>
                </div>

                <!-- Status -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Status</label>
                    <select v-model="complaintForm.status" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed">
                        <option value="">Select status</option>
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                    <p v-if="formErrors.status" class="text-red-500 text-sm mt-1">{{ formErrors.status }}</p>
                </div>

                <!-- Witness -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Witness/es</label>
                    <textarea v-model="complaintForm.witness" class="border rounded-md p-2"
                        :disabled="isLoading || isDestroyed"></textarea>
                    <p v-if="formErrors.witness" class="text-red-500 text-sm mt-1">{{ formErrors.witness }}</p>
                </div>

                <!-- Supporting Documents -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm mb-1">Supporting Documents</label>

                    <!-- Hidden file input -->
                    <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileUpload"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" :disabled="isLoading || isDestroyed" />

                    <!-- Custom upload button -->
                    <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-fit"
                        @click="$refs.fileInput.click()" :disabled="isLoading || isDestroyed">
                        Upload Files
                    </button>

                    <!-- Existing Documents -->
                    <div v-if="existingSupportingDocuments.length" class="mt-2">
                        <p class="text-sm font-medium text-gray-600 mb-1">Existing Documents:</p>
                        <div class="space-y-1 text-sm text-gray-700">
                            <div v-for="(doc, index) in existingSupportingDocuments" :key="`existing-${index}`"
                                class="flex items-center gap-2">
                                <span>{{ getFileName(doc) }}</span>
                                <button type="button"
                                    class="text-red-500 transition-transform duration-300 hover:rotate-180"
                                    @click="removeExistingDocument(index)" :disabled="isLoading || isDestroyed">
                                    ✖
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- New Documents -->
                    <div v-if="newSupportingDocuments.length" class="mt-2">
                        <p class="text-sm font-medium text-gray-600 mb-1">New Documents:</p>
                        <div class="space-y-1 text-sm text-gray-700">
                            <div v-for="(file, index) in newSupportingDocuments" :key="`new-${index}`"
                                class="flex items-center gap-2">
                                <span>{{ file.name }}</span>
                                <button type="button"
                                    class="text-red-500 transition-transform duration-300 hover:rotate-180"
                                    @click="removeNewDocument(index)" :disabled="isLoading || isDestroyed">
                                    ✖
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md"
                    :disabled="isLoading || isDestroyed || !isInitialized">
                    <span v-if="isLoading">Updating...</span>
                    <span v-else>Update Complaint</span>
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
    background: hsl(142, 76%, 36%);
}

.multiselect__option--highlight::after {
    background: #16A34A;
}
</style>