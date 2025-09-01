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

    console.log('Removing existing document at index:', index);
    console.log('Before removal:', existingSupportingDocuments.value);

    existingSupportingDocuments.value.splice(index, 1);

    console.log('After removal:', existingSupportingDocuments.value);
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

        // Add all form fields except supporting_documents
        Object.entries(complaintForm.value).forEach(([key, value]) => {
            if (key !== 'supporting_documents') {
                formData.append(key, value || '');
            }
        });

        // FIXED: Always send existing documents array (even if empty)
        // This ensures that deleted files are actually removed
        formData.append('existing_documents', JSON.stringify(existingSupportingDocuments.value));

        // Add new files if any
        if (newSupportingDocuments.value.length > 0) {
            newSupportingDocuments.value.forEach(file => {
                formData.append('supporting_documents[]', file);
            });
        }

        formData.append('_method', 'PATCH');

        console.log('Existing documents being sent:', existingSupportingDocuments.value);
        console.log('New documents count:', newSupportingDocuments.value.length);

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
    <div class="min-h-screen bg-gray-50 py-4 px-4 sm:py-8 sm:px-6 lg:px-8">
        <!-- Mobile-first container -->
        <div class="max-w-md mx-auto sm:max-w-2xl lg:max-w-4xl">

            <!-- Loading State -->
            <div v-if="isLoading" class="flex justify-center items-center min-h-[60vh]">
                <div class="animate-spin rounded-full h-12 w-12 sm:h-16 sm:w-16 border-b-2 border-green-600"></div>
            </div>

            <!-- Error State -->
            <div v-else-if="formErrors.component" class="flex justify-center items-center min-h-[60vh]">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 sm:p-6 max-w-md mx-auto">
                    <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-red-400 mb-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-red-800 mb-2 text-center">Failed to Load Complaint</h3>
                    <p class="text-red-600 mb-4 text-center text-sm">There was an error loading the complaint data.
                        Please try again.</p>
                    <button @click="initializeComponent"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 w-full">
                        Retry
                    </button>
                </div>
            </div>

            <!-- Main Form -->
            <form v-else-if="isMounted && isInitialized && !isDestroyed" @submit.prevent="submitForm"
                class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                <!-- Header Section -->
                <div class="px-4 py-5 sm:px-6 lg:px-8 border-b border-gray-200">
                    <h1 class="text-lg font-semibold text-gray-900 sm:text-xl lg:text-2xl">
                        Edit Complaint
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 sm:text-base">
                        Update the complaint details below
                    </p>
                </div>

                <!-- Form Content -->
                <div class="px-4 py-6 sm:px-6 lg:px-8 space-y-6">

                    <!-- Parties Section -->
                    <div class="space-y-4 sm:grid sm:grid-cols-2 sm:gap-4 sm:space-y-0">
                        <!-- Complainant -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Complainant <span class="text-red-500">*</span>
                            </label>
                            <Multiselect v-model="selectedComplainant" :options="filteredComplainants"
                                :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                                placeholder="Search or select complainant" :searchable="true" :show-labels="false"
                                :disabled="isLoading || isDestroyed" class="mobile-multiselect">
                                <template #option="{ option }">
                                    <div class="flex justify-between items-center">
                                        <span>{{ option.first_name }} {{ option.last_name }}</span>
                                        <span class="text-xs text-gray-500">#{{ option.resident_number }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                            <p v-if="formErrors.complainant_id" class="text-xs text-red-600 mt-1">
                                {{ formErrors.complainant_id }}
                            </p>
                        </div>

                        <!-- Respondent -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Respondent <span class="text-red-500">*</span>
                            </label>
                            <Multiselect v-model="selectedRespondent" :options="filteredRespondents"
                                :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                                placeholder="Search or select respondent" :searchable="true" :show-labels="false"
                                :disabled="isLoading || isDestroyed" class="mobile-multiselect">
                                <template #option="{ option }">
                                    <div class="flex justify-between items-center">
                                        <span>{{ option.first_name }} {{ option.last_name }}</span>
                                        <span class="text-xs text-gray-500">#{{ option.resident_number }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                            <p v-if="formErrors.respondent_id" class="text-xs text-red-600 mt-1">
                                {{ formErrors.respondent_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Case Details Section -->
                    <div class="space-y-4 sm:grid sm:grid-cols-2 sm:gap-4 sm:space-y-0">
                        <!-- Nature of Complaint -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Nature of Complaint <span class="text-red-500">*</span>
                            </label>
                            <select v-model="complaintForm.nature_of_complaint"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                :disabled="isLoading || isDestroyed">
                                <option value="" disabled>Select nature of complaint</option>
                                <option value="Civil">Civil</option>
                                <option value="Criminal">Criminal</option>
                                <option value="Administrative">Administrative</option>
                                <option value="Domestic/Family Disputes">Domestic/Family Disputes</option>
                                <option value="Community & Public Order">Community & Public Order</option>
                                <option value="VAWC">VAWC</option>
                                <option value="Business | Economic">Business | Economic</option>
                            </select>
                            <p v-if="formErrors.nature_of_complaint" class="text-xs text-red-600 mt-1">
                                {{ formErrors.nature_of_complaint }}
                            </p>
                        </div>

                        <!-- Case Number -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Case Number <span class="text-red-500">*</span>
                            </label>
                            <input v-model="complaintForm.case_no" type="text" placeholder="Enter case number"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                :disabled="isLoading || isDestroyed" />
                            <p v-if="formErrors.case_no" class="text-xs text-red-600 mt-1">
                                {{ formErrors.case_no }}
                            </p>
                        </div>
                    </div>

                    <!-- Title and Location Section -->
                    <div class="space-y-4 sm:grid sm:grid-cols-2 sm:gap-4 sm:space-y-0">
                        <!-- Title -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input v-model="complaintForm.title" type="text" placeholder="Enter complaint title"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                :disabled="isLoading || isDestroyed" />
                            <p v-if="formErrors.title" class="text-xs text-red-600 mt-1">
                                {{ formErrors.title }}
                            </p>
                        </div>

                        <!-- Location of Incident -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Location of Incident <span class="text-red-500">*</span>
                            </label>
                            <input v-model="complaintForm.incident_location" type="text"
                                placeholder="Enter incident location"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                :disabled="isLoading || isDestroyed" />
                            <p v-if="formErrors.incident_location" class="text-xs text-red-600 mt-1">
                                {{ formErrors.incident_location }}
                            </p>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="complaintForm.description" rows="4"
                            placeholder="Provide detailed description of the complaint"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                            :disabled="isLoading || isDestroyed"></textarea>
                        <p v-if="formErrors.description" class="text-xs text-red-600 mt-1">
                            {{ formErrors.description }}
                        </p>
                    </div>

                    <!-- Resolution Section -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Resolution <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="complaintForm.resolution" rows="3"
                            placeholder="Enter proposed or actual resolution"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                            :disabled="isLoading || isDestroyed"></textarea>
                        <p v-if="formErrors.resolution" class="text-xs text-red-600 mt-1">
                            {{ formErrors.resolution }}
                        </p>
                    </div>

                    <!-- Date & Time Section -->
                    <div class="space-y-4 sm:grid sm:grid-cols-2 sm:gap-4 sm:space-y-0">
                        <!-- Date & Time of Incident -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Date & Time of Incident <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" v-model="complaintForm.incident_datetime"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                :disabled="isLoading || isDestroyed" />
                            <p v-if="formErrors.incident_datetime" class="text-xs text-red-600 mt-1">
                                {{ formErrors.incident_datetime }}
                            </p>
                        </div>

                        <!-- Filing Date -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Filing Date & Time <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" v-model="complaintForm.filing_date"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                :disabled="isLoading || isDestroyed" />
                            <p v-if="formErrors.filing_date" class="text-xs text-red-600 mt-1">
                                {{ formErrors.filing_date }}
                            </p>
                        </div>
                    </div>

                    <!-- Status and Witness Section -->
                    <div class="space-y-4 sm:grid sm:grid-cols-2 sm:gap-4 sm:space-y-0">
                        <!-- Status -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select v-model="complaintForm.status"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                :disabled="isLoading || isDestroyed">
                                <option value="" disabled>Select status</option>
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                            </select>
                            <p v-if="formErrors.status" class="text-xs text-red-600 mt-1">
                                {{ formErrors.status }}
                            </p>
                        </div>

                        <!-- Witness -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Witness/es <span class="text-red-500">*</span>
                            </label>
                            <textarea v-model="complaintForm.witness" rows="3" placeholder="List any witnesses"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                                :disabled="isLoading || isDestroyed"></textarea>
                            <p v-if="formErrors.witness" class="text-xs text-red-600 mt-1">
                                {{ formErrors.witness }}
                            </p>
                        </div>
                    </div>

                    <!-- Supporting Documents Section -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Supporting Documents
                        </label>

                        <!-- Hidden file input -->
                        <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileUpload"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" :disabled="isLoading || isDestroyed" />

                        <!-- Upload button -->
                        <button type="button"
                            class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                            @click="$refs.fileInput.click()" :disabled="isLoading || isDestroyed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Upload Files
                        </button>

                        <!-- Existing Documents -->
                        <div v-if="existingSupportingDocuments.length" class="mt-3 space-y-2">
                            <p class="text-sm font-medium text-gray-600">Existing Documents:</p>
                            <div v-for="(doc, index) in existingSupportingDocuments" :key="`existing-${index}`"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-900 truncate">{{ getFileName(doc) }}</span>
                                </div>
                                <button type="button"
                                    class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full transition-colors"
                                    @click="removeExistingDocument(index)" :disabled="isLoading || isDestroyed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- New Documents -->
                        <div v-if="newSupportingDocuments.length" class="mt-3 space-y-2">
                            <p class="text-sm font-medium text-gray-600">New Documents:</p>
                            <div v-for="(file, index) in newSupportingDocuments" :key="`new-${index}`"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-900 truncate">{{ file.name }}</span>
                                </div>
                                <button type="button"
                                    class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full transition-colors"
                                    @click="removeNewDocument(index)" :disabled="isLoading || isDestroyed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer/Action Buttons -->
                <div class="px-4 py-4 sm:px-6 lg:px-8 bg-gray-50 border-t border-gray-200">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" @click="handleCancel"
                            class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                            :disabled="isLoading">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex justify-center items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                            :disabled="isLoading || isDestroyed || !isInitialized">
                            <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ isLoading ? 'Updating...' : 'Update Complaint' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<style>
/* Mobile-first Multiselect styling */
.mobile-multiselect .multiselect {
    min-height: auto;
}

.mobile-multiselect .multiselect__tags {
    min-height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.mobile-multiselect .multiselect__tags:focus-within {
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.mobile-multiselect .multiselect__input,
.mobile-multiselect .multiselect__single {
    font-size: 0.875rem;
    margin-bottom: 0;
    padding: 0;
    line-height: 1.25rem;
}

.mobile-multiselect .multiselect__placeholder {
    margin-bottom: 0;
    padding-top: 0;
    padding-left: 0;
    color: #9ca3af;
    font-size: 0.875rem;
}

.mobile-multiselect .multiselect__option--highlight {
    background: #10b981;
}

.mobile-multiselect .multiselect__option--highlight::after {
    background: #059669;
}

.mobile-multiselect .multiselect__content-wrapper {
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.mobile-multiselect .multiselect__option {
    padding: 0.75rem;
    font-size: 0.875rem;
    border-bottom: 1px solid #f3f4f6;
}

.mobile-multiselect .multiselect__option:last-child {
    border-bottom: none;
}

/* Mobile optimizations for small screens */
@media (max-width: 640px) {
    .mobile-multiselect .multiselect__tags {
        min-height: 44px;
        /* Better touch target on mobile */
        padding: 0.75rem;
    }

    .mobile-multiselect .multiselect__option {
        padding: 1rem 0.75rem;
        /* More touch-friendly on mobile */
    }
}
</style>