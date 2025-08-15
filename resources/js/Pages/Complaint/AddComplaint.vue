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
  <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
    <form @submit.prevent="submitForm" class="bg-white p-10 rounded-xl shadow-md w-full max-w-4xl">
      <h1 class="text-2xl font-bold mb-6">Add New Complaint</h1>

      <div class="grid grid-cols-2 gap-4">
        <!-- Complainant Searchable Dropdown -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm mb-1">Complainant</label>
          <Multiselect v-model="selectedComplainant" :options="filteredComplainants"
            :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
            placeholder="Search or select complainant" :searchable="true" :show-labels="false" />
          <p v-if="formErrors.complainant_id" class="text-red-500 text-sm mt-1">{{ formErrors.complainant_id }}</p>
        </div>

        <!-- Respondent Searchable Dropdown -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm mb-1">Respondent</label>
          <Multiselect v-model="selectedRespondent" :options="filteredRespondents"
            :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
            placeholder="Search or select respondent" :searchable="true" :show-labels="false" />
          <p v-if="formErrors.respondent_id" class="text-red-500 text-sm mt-1">{{ formErrors.respondent_id }}</p>
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
          <p v-if="formErrors.nature_of_complaint" class="text-red-500 text-sm mt-1">{{ formErrors.nature_of_complaint
            }}</p>
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
          <p v-if="formErrors.incident_location" class="text-red-500 text-sm mt-1">{{ formErrors.incident_location }}
          </p>
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
          <input type="datetime-local" v-model="complaintForm.incident_datetime" class="border rounded-md p-2" />
          <p v-if="formErrors.incident_datetime" class="text-red-500 text-sm mt-1">{{ formErrors.incident_datetime }}
          </p>
        </div>
        <!-- Filing Date -->
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
          <label class="font-semibold text-sm">Witness/es</label>
          <textarea v-model="complaintForm.witness" class="border rounded-md p-2"></textarea>
        </div>

        <!-- Supporting Document -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm mb-1">Supporting Documents</label>

          <!-- Hidden file input -->
          <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileUpload"
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />

          <!-- Custom upload button -->
          <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-fit"
            @click="$refs.fileInput.click()">
            Upload Files
          </button>

          <!-- List selected file names -->
          <div v-if="complaintForm.supporting_documents.length" class="mt-2 space-y-1 text-sm text-gray-700">
            <div v-for="(file, index) in complaintForm.supporting_documents" :key="index"
              class="flex items-center gap-2">
              <span>{{ file.name }}</span>
              <button type="button" class="text-red-500 hover:underline"
                @click="complaintForm.supporting_documents.splice(index, 1)">
                ✖
              </button>
            </div>
          </div>
        </div>
      </div>


      <!-- Cancel -->
      <div class="mt-6 flex justify-end gap-4">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">Save</button>
        <router-link to="/complaints" class="bg-gray-300 px-4 py-2 rounded-md">Cancel</router-link>
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