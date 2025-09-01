<script setup>
import Multiselect from 'vue-multiselect';
import { useBlotterStore, useResidentStore } from '@/Stores'
import { ref, watch, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const router = useRouter();
const { showToast } = useToast();
const blotterStore = useBlotterStore();
const residentStore = useResidentStore();

// Refs
const residents = ref([]);
const errors = ref({});
const selectedComplainant = ref(null);
const selectedRespondent = ref(null);
const fileInput = ref(null);

// Form data - REMOVED ADDRESS FIELDS
const blotterDataForm = ref({
    complainants_name: '',
    respondents_name: '',
    blotter_no: '',
    filing_date: '',
    title_case: '',
    nature_of_case: '',
    complainants_id: '',
    respondents_id: '',
    incident_location: '',
    datetime_of_incident: '',
    blotter_type: '',
    barangay_case_no: '',
    status: '',
    description: '',
    witness: '',
    supporting_documents: []
});

// Computed properties for filtered lists
const filteredComplainants = computed(() => {
    const allResidents = residentStore.residents || [];
    return allResidents.filter(resident =>
        !selectedRespondent.value || resident.id !== selectedRespondent.value.id
    );
});

const filteredRespondents = computed(() => {
    const allResidents = residentStore.residents || [];
    return allResidents.filter(resident =>
        !selectedComplainant.value || resident.id !== selectedComplainant.value.id
    );
});
// Replace your onMounted hook with this version that includes debugging
onMounted(async () => {
    try {
        console.log('Fetching residents...');
        await residentStore.getResidents();
        console.log('Store residents:', residentStore.residents);
        residents.value = residentStore.residents;
        console.log('Local residents:', residents.value);
    } catch (error) {
        console.error('Error fetching residents:', error);
    }
});

// Watchers for form fields to clear errors when changed
watch(() => blotterDataForm.value.blotter_no, () => clearError('blotter_no'));
watch(() => blotterDataForm.value.filing_date, () => clearError('filing_date'));
watch(() => blotterDataForm.value.title_case, () => clearError('title_case'));
watch(() => blotterDataForm.value.nature_of_case, () => clearError('nature_of_case'));
watch(() => blotterDataForm.value.complainants_id, () => clearError('complainants_id'));
watch(() => blotterDataForm.value.respondents_id, () => clearError('respondents_id'));
watch(() => blotterDataForm.value.incident_location, () => clearError('incident_location'));
watch(() => blotterDataForm.value.datetime_of_incident, () => clearError('datetime_of_incident'));
watch(() => blotterDataForm.value.blotter_type, () => clearError('blotter_type'));
watch(() => blotterDataForm.value.barangay_case_no, () => clearError('barangay_case_no'));
watch(() => blotterDataForm.value.status, () => clearError('status'));
watch(() => blotterDataForm.value.description, () => clearError('description'));
watch(() => blotterDataForm.value.witness, () => clearError('witness'));

const clearError = (fieldName) => {
    if (errors.value[fieldName]) {
        errors.value[fieldName] = '';
    }
};

// Watchers - REMOVED ADDRESS SETTING
watch(selectedComplainant, (val) => {
    blotterDataForm.value.complainants_id = val?.id ?? '';
    blotterDataForm.value.complainants_name = val ? `${val.first_name} ${val.last_name}` : '';

    // Clear respondent if same as complainant
    if (val && selectedRespondent.value && val.id === selectedRespondent.value.id) {
        selectedRespondent.value = null;
        blotterDataForm.value.respondents_id = '';
        blotterDataForm.value.respondents_name = '';
    }
});

watch(selectedRespondent, (val) => {
    blotterDataForm.value.respondents_id = val?.id ?? '';
    blotterDataForm.value.respondents_name = val ? `${val.first_name} ${val.last_name}` : '';

    // Clear complainant if same as respondent
    if (val && selectedComplainant.value && val.id === selectedComplainant.value.id) {
        selectedComplainant.value = null;
        blotterDataForm.value.complainants_id = '';
        blotterDataForm.value.complainants_name = '';
    }
});


// Methods
const handleFileUpload = (event) => {
    const newFiles = Array.from(event.target.files);

    console.log('Files selected:', newFiles.map(f => ({
        name: f.name,
        size: f.size,
        type: f.type
    })));

    // Check for duplicates
    const existingNames = new Set(
        blotterDataForm.value.supporting_documents.map(file => file.name)
    );

    const uniqueNewFiles = newFiles.filter(file => {
        if (existingNames.has(file.name)) {
            console.warn('Duplicate file detected:', file.name);
            return false;
        }
        return true;
    });

    if (uniqueNewFiles.length !== newFiles.length) {
        showToast({
            icon: 'warning',
            title: 'Some duplicate files were skipped'
        });
    }

    // Add unique files to form
    blotterDataForm.value.supporting_documents = [
        ...blotterDataForm.value.supporting_documents,
        ...uniqueNewFiles
    ];

    console.log('Total files after upload:', blotterDataForm.value.supporting_documents.length);

    // Clear input to allow re-selecting the same file
    event.target.value = '';
};
const validateForm = () => {
    errors.value = {};
    let isValid = true;

    console.log('=== FORM VALIDATION DEBUG ===');
    console.log('Current form data:', JSON.stringify(blotterDataForm.value, null, 2));

    // REMOVED ADDRESS FIELDS FROM VALIDATION
    const requiredFields = {
        blotter_no: 'Blotter No',
        filing_date: 'Filing Date',
        title_case: 'Title Case',
        nature_of_case: 'Nature of Case',
        complainants_id: 'Complainant',
        respondents_id: 'Respondent',
        incident_location: 'Location of Incident',
        datetime_of_incident: 'Date/Time of Incident',
        blotter_type: 'Blotter Type',
        barangay_case_no: 'Barangay Case No',
        status: 'Status',
        description: 'Description',
    };

    console.log('Required fields to validate:', requiredFields);

    for (const [field, label] of Object.entries(requiredFields)) {
        const fieldValue = blotterDataForm.value[field];
        console.log(`Checking field "${field}":`, {
            value: fieldValue,
            type: typeof fieldValue,
            isEmpty: !fieldValue,
            isEmptyString: fieldValue === '',
            isNull: fieldValue === null,
            isUndefined: fieldValue === undefined
        });

        if (!fieldValue) {
            console.error(`❌ Field "${field}" is invalid:`, fieldValue);
            errors.value[field] = `${label} is required`;
            isValid = false;
        } else {
            console.log(`✅ Field "${field}" is valid:`, fieldValue);
        }
    }

    console.log('Final validation result:', isValid);
    console.log('Errors object:', errors.value);
    console.log('=== END VALIDATION DEBUG ===');

    return isValid;
};

const createBlotter = async () => {
    try {
        console.log('=== CREATE BLOTTER DEBUG ===');
        console.log('Form data before validation:', blotterDataForm.value);

        if (!validateForm()) {
            console.error('❌ Validation failed!');
            showToast({ icon: 'error', title: 'Please fill in all required fields' });
            return;
        }

        console.log('✅ Validation passed, creating FormData...');

        // FIXED: Use the store's prepareFormData method or create FormData properly
        const formData = new FormData();

        // Add required fields that might be missing
        const formDataWithDefaults = {
            ...blotterDataForm.value,
            complainants_type: 'App\\Models\\Resident',
            respondents_type: 'App\\Models\\Resident',
            total_cases: '0',
            witness: blotterDataForm.value.witness || ''
        };

        // Append all form fields except supporting_documents
        Object.entries(formDataWithDefaults).forEach(([key, value]) => {
            if (key === 'supporting_documents') {
                // Handle files separately
                if (Array.isArray(value) && value.length > 0) {
                    console.log(`Adding ${value.length} files for supporting_documents`);
                    value.forEach((file, index) => {
                        formData.append('supporting_documents[]', file);
                        console.log(`File ${index}:`, file.name, file.type, file.size);
                    });
                } else {
                    console.log('No supporting documents to add');
                }
            } else {
                console.log(`Adding form field: ${key} = ${value}`);
                formData.append(key, value || '');
            }
        });

        // Debug FormData contents
        console.log('FormData contents:');
        for (let pair of formData.entries()) {
            if (pair[1] instanceof File) {
                console.log(pair[0], 'File:', pair[1].name, pair[1].type, pair[1].size);
            } else {
                console.log(pair[0], pair[1]);
            }
        }

        console.log('Calling blotterStore.addBlotter...');
        const result = await blotterStore.addBlotter(formData);

        console.log('✅ Blotter created successfully!', result);
        showToast({ icon: 'success', title: 'Blotter created successfully' });

        // Clear form after successful creation
        const resetForm = () => {
            blotterDataForm.value = {
                complainants_name: '',
                respondents_name: '',
                blotter_no: '',
                filing_date: '',
                title_case: '',
                nature_of_case: '',
                complainants_id: '',
                respondents_id: '',
                incident_location: '',
                datetime_of_incident: '',
                blotter_type: '',
                barangay_case_no: '',
                status: '',
                description: '',
                witness: '',
                supporting_documents: []
            };

            selectedComplainant.value = null;
            selectedRespondent.value = null;
            errors.value = {};

            // Clear file input
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        };


        // Navigate back to list
        router.push('/blotter/list-blotter');

    } catch (error) {
        console.error('❌ Error creating blotter:', error);
        console.error('Error details:', {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status,
            statusText: error.response?.statusText
        });

        // Show specific error message if available
        let errorMessage = 'Failed to create blotter';
        if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            errorMessage = errors.join(', ');
        } else if (error.message) {
            errorMessage = error.message;
        }

        showToast({ icon: 'error', title: errorMessage });
    }
};

const removeFile = (index) => {
    console.log('Removing file at index:', index);
    blotterDataForm.value.supporting_documents.splice(index, 1);
    console.log('Remaining files:', blotterDataForm.value.supporting_documents.length);
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <form @submit.prevent="createBlotter" class="bg-white p-10 rounded-xl shadow-md w-full max-w-4xl">
            <h1 class="text-2xl font-bold mb-6">Add New Blotter</h1>

            <div class="grid grid-cols-2 gap-4">
                <!-- Complainant Searchable Dropdown -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm mb-1">Complainant</label>
                    <Multiselect v-model="selectedComplainant" :options="filteredComplainants"
                        :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                        placeholder="Search or select complainant" :searchable="true" :show-labels="false"
                        :allow-empty="true" @select="val => blotterDataForm.complainants_id = val?.id" />
                    <p v-if="errors.complainants_id" class="text-red-500 text-sm mt-1">{{ errors.complainants_id }}</p>
                    <!-- Debug info (remove after fixing) -->
                    <p class="text-xs text-gray-500 mt-1">Available complainants: {{ filteredComplainants.length }}</p>
                </div>

                <!-- Respondent Searchable Dropdown -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm mb-1">Respondent</label>
                    <Multiselect v-model="selectedRespondent" :options="filteredRespondents"
                        :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
                        placeholder="Search or select respondent" :searchable="true" :show-labels="false"
                        :allow-empty="true" @select="val => blotterDataForm.respondents_id = val?.id" />
                    <p v-if="errors.respondents_id" class="text-red-500 text-sm mt-1">{{ errors.respondents_id }}</p>
                    <!-- Debug info (remove after fixing) -->
                    <p class="text-xs text-gray-500 mt-1">Available respondents: {{ filteredRespondents.length }}</p>
                </div>

                <!-- Nature of Case -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Nature of Case</label>
                    <select v-model="blotterDataForm.nature_of_case" class="border rounded-md p-2">
                        <option value="" disabled selected>Select Nature of Case</option>
                        <option value="civil case">Civil Case</option>
                        <option value="criminal case">Criminal Case</option>
                    </select>
                    <p v-if="errors.nature_of_case" class="text-red-500 text-sm mt-1">{{ errors.nature_of_case }}</p>
                </div>

                <!-- Blotter Type -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Blotter Type</label>
                    <select v-model="blotterDataForm.blotter_type" class="border rounded-md p-2">
                        <option value="" disabled selected>Select Blotter Type</option>
                        <option value="Incident">Incident</option>
                        <option value="Complaint">Complaint</option>
                        <option value="Request">Request</option>
                    </select>
                    <p v-if="errors.blotter_type" class="text-red-500 text-sm mt-1">{{ errors.blotter_type }}</p>
                </div>

                <!-- Blotter No -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Blotter No</label>
                    <input v-model="blotterDataForm.blotter_no" type="text" class="border rounded-md p-2"
                        placeholder="Enter Blotter No" />
                    <p v-if="errors.blotter_no" class="text-red-500 text-sm mt-1">{{ errors.blotter_no }}</p>
                </div>

                <!-- Barangay Case No -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Barangay Case No</label>
                    <input v-model="blotterDataForm.barangay_case_no" type="text" class="border rounded-md p-2"
                        placeholder="Enter Case No" />
                    <p v-if="errors.barangay_case_no" class="text-red-500 text-sm mt-1">{{ errors.barangay_case_no }}
                    </p>
                </div>

                <!-- Title Case -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Title Case</label>
                    <input v-model="blotterDataForm.title_case" type="text" class="border rounded-md p-2"
                        placeholder="Enter Title Case" />
                    <p v-if="errors.title_case" class="text-red-500 text-sm mt-1">{{ errors.title_case }}</p>
                </div>

                <!-- Location of Incident -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Location of Incident</label>
                    <input v-model="blotterDataForm.incident_location" type="text" class="border rounded-md p-2"
                        placeholder="Enter Location of Incident" />
                    <p v-if="errors.place" class="text-red-500 text-sm mt-1">{{ errors.incident_location }}</p>
                </div>

                <!-- Description -->
                <div class="flex flex-col col-span-2">
                    <label class="font-semibold text-sm">Description</label>
                    <textarea v-model="blotterDataForm.description" class="border rounded-md p-2"
                        placeholder="Enter Description" rows="4"></textarea>
                    <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
                </div>

                <!-- Date & Time of Incident -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Date & Time of Incident</label>
                    <input type="datetime-local" v-model="blotterDataForm.datetime_of_incident"
                        class="border rounded-md p-2" />
                    <p v-if="errors.datetime_of_incident" class="text-red-500 text-sm mt-1">{{
                        errors.datetime_of_incident }}</p>
                </div>

                <!-- Filing Date -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Filing Date & Time</label>
                    <input type="datetime-local" v-model="blotterDataForm.filing_date" class="border rounded-md p-2" />
                    <p v-if="errors.filing_date" class="text-red-500 text-sm mt-1">{{ errors.filing_date }}</p>
                </div>

                <!-- Status -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Status</label>
                    <select v-model="blotterDataForm.status" class="border rounded-md p-2">
                        <option value="" disabled selected>Select Status</option>
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                    <p v-if="errors.status" class="text-red-500 text-sm mt-1">{{ errors.status }}</p>
                </div>

                <!-- Witness -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm">Witness/es</label>
                    <textarea v-model="blotterDataForm.witness" class="border rounded-md p-2"
                        placeholder="Enter Witness Name"></textarea>
                    <p v-if="errors.witness" class="text-red-500 text-sm mt-1">{{ errors.witness }}</p>
                </div>

                <!-- Supporting Document -->
                <div class="flex flex-col">
                    <label class="font-semibold text-sm mb-1">Supporting Documents</label>
                    <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileUpload"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                    <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-fit"
                        @click="$refs.fileInput.click()">
                        Upload Files
                    </button>
                    <div v-if="blotterDataForm.supporting_documents.length"
                        class="mt-2 space-y-1 text-sm text-gray-700">
                        <div v-for="(file, index) in blotterDataForm.supporting_documents" :key="index"
                            class="flex items-center gap-2">
                            <span>{{ file.name }}</span>
                            <button type="button" class="text-red-500 hover:underline"
                                @click="blotterDataForm.supporting_documents.splice(index, 1)">
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
                <router-link to="/blotter" class="bg-gray-300 px-4 py-2 rounded-md">Cancel</router-link>
            </div>
        </form>
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