<script setup>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import { useRouter } from 'vue-router';
import { useComplaintStore, useResidentStore } from '@/Stores';
import useToast from '@/Utils/useToast';
import { onMounted, ref, watch, computed } from 'vue';

const router = useRouter();
const { showToast } = useToast();

const complaintStore = useComplaintStore();
const residentStore = useResidentStore();
const residents = ref([]);

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

const handleFileUpload = (event) => {
  const newFiles = Array.from(event.target.files);

  const existingNames = new Set(
    complaintForm.value.supporting_documents.map(file => file.name)
  );

  const uniqueNewFiles = newFiles.filter(file => !existingNames.has(file.name));

  complaintForm.value.supporting_documents = [
    ...complaintForm.value.supporting_documents,
    ...uniqueNewFiles
  ];

  event.target.value = '';
};

const formErrors = ref({});

// Exclude selected respondent from complainant list
const filteredComplainants = computed(() => {
  return residents.value.filter(resident =>
    selectedRespondent.value ? resident.id !== selectedRespondent.value.id : true
  );
});

// Exclude selected complainant from respondent list
const filteredRespondents = computed(() => {
  return residents.value.filter(resident =>
    selectedComplainant.value ? resident.id !== selectedComplainant.value.id : true
  );
});

watch(selectedComplainant, (val) => {
  complaintForm.value.complainant_id = val?.id ?? '';
});

watch(selectedRespondent, (val) => {
  complaintForm.value.respondent_id = val?.id ?? '';
});

onMounted(async () => {
  await residentStore.getResidents();
  residents.value = residentStore.residents;
});

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

  // Check if complainant and respondent are the same
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

const submitForm = async () => {
  if (!validateForm()) {
    showToast({ icon: 'error', title: 'Please fill in all required fields.' });
    return;
  }

  try {
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
      if (key === 'supporting_documents' && Array.isArray(value)) {
        value.forEach(file => {
          formData.append('supporting_documents[]', file);
        });
      } else {
        formData.append(key, value);
      }
    });

    await complaintStore.createComplaint(formData);
    showToast({ icon: 'success', title: 'Complaint submitted successfully.' });
    await complaintStore.getComplaints(1);
    router.push('/complaints/list-complaints');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      const messages = Object.values(error.response.data.errors).flat().join(' ');
      showToast({ icon: 'error', title: messages });
    } else {
      showToast({ icon: 'error', title: error.message });
    }
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-4 px-4 sm:py-8 sm:px-6 lg:px-8">
    <!-- Mobile-first container -->
    <div class="max-w-md mx-auto sm:max-w-2xl lg:max-w-4xl">
      <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

        <!-- Header Section -->
        <div class="px-4 py-5 sm:px-6 lg:px-8 border-b border-gray-200">
          <h1 class="text-lg font-semibold text-gray-900 sm:text-xl lg:text-2xl">
            Add New Complaint
          </h1>
          <p class="mt-1 text-sm text-gray-500 sm:text-base">
            Fill out all required fields to submit a new complaint
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
                class="mobile-multiselect" />
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
                class="mobile-multiselect" />
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
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
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
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" />
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
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" />
              <p v-if="formErrors.title" class="text-xs text-red-600 mt-1">
                {{ formErrors.title }}
              </p>
            </div>

            <!-- Location of Incident -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                Location of Incident <span class="text-red-500">*</span>
              </label>
              <input v-model="complaintForm.incident_location" type="text" placeholder="Enter incident location"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" />
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
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"></textarea>
            <p v-if="formErrors.description" class="text-xs text-red-600 mt-1">
              {{ formErrors.description }}
            </p>
          </div>

          <!-- Resolution Section -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Resolution <span class="text-red-500">*</span>
            </label>
            <textarea v-model="complaintForm.resolution" rows="3" placeholder="Enter proposed or actual resolution"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"></textarea>
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
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" />
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
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" />
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
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
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
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"></textarea>
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
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />

            <!-- Upload button -->
            <button type="button"
              class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
              @click="$refs.fileInput.click()">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Upload Files
            </button>

            <!-- File list -->
            <div v-if="complaintForm.supporting_documents.length" class="mt-3 space-y-2">
              <div v-for="(file, index) in complaintForm.supporting_documents" :key="index"
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
                  @click="complaintForm.supporting_documents.splice(index, 1)">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer/Action Buttons -->
        <div class="px-4 py-4 sm:px-6 lg:px-8 bg-gray-50 border-t border-gray-200">
          <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <router-link to="/complaints"
              class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
              Cancel
            </router-link>
            <button type="submit"
              class="inline-flex justify-center items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Save Complaint
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