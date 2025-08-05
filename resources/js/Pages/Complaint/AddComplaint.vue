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

const submitForm = async () => {
  formErrors.value = {}; // reset

  // Frontend validation
  const requiredFields = [
    'complainant_id',
    'respondent_id',
    'case_no',
    'title',
    'description',
    'resolution',
    'filing_date',
    'nature_of_complaint',
    'incident_datetime',
    'incident_location',
    'witness'
  ];

  requiredFields.forEach(field => {
    if (!complaintForm.value[field]) {
      formErrors.value[field] = 'Please fill out this field';
    }
  });

  // Prevent same person as both complainant and respondent
  if (
    complaintForm.value.complainant_id &&
    complaintForm.value.respondent_id &&
    complaintForm.value.complainant_id === complaintForm.value.respondent_id
  ) {
    showToast({ icon: 'error', title: 'Complainant and Respondent cannot be the same person.' });
    return;
  }

  if (Object.keys(formErrors.value).length > 0) {
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
        // Append each file with [] syntax
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
    <form @submit.prevent="createComplaint" class="bg-white p-10 rounded-xl shadow-md w-full max-w-4xl">
      <h1 class="text-2xl font-bold mb-6">Add New Complaint</h1>

      <div class="grid grid-cols-2 gap-4">
        <!-- Complainant Searchable Dropdown -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm mb-1">Complainant</label>
          <Multiselect v-model="selectedComplainant" :options="filteredComplainants"
            :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
            placeholder="Search or select complainant" :searchable="true" :show-labels="false" />
        </div>

        <!-- Respondent Searchable Dropdown -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm mb-1">Respondent</label>
          <Multiselect v-model="selectedRespondent" :options="filteredRespondents"
            :custom-label="resident => `${resident.first_name} ${resident.last_name}`" track-by="id"
            placeholder="Search or select respondent" :searchable="true" :show-labels="false" />
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
        </div>
        <!-- Case Number -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Case Number</label>
          <input v-model="complaintForm.case_no" type="text" class="border rounded-md p-2" />
        </div>
        <!-- Title -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Title</label>
          <input v-model="complaintForm.title" type="text" class="border rounded-md p-2" />
        </div>
        <!-- Location of Incident -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Location of Incident</label>
          <input v-model="complaintForm.incident_location" type="text" class="border rounded-md p-2" />
        </div>
        <!-- Description -->
        <div class="flex flex-col col-span-2">
          <label class="font-semibold text-sm">Description</label>
          <textarea v-model="complaintForm.description" class="border rounded-md p-2"></textarea>
        </div>
        <!-- Resolution -->
        <div class="flex flex-col col-span-2">
          <label class="font-semibold text-sm">Resolution</label>
          <textarea v-model="complaintForm.resolution" class="border rounded-md p-2"></textarea>
        </div>
        <!-- Date -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Date & Time of Incident</label>
          <input type="datetime-local" v-model="complaintForm.incident_datetime" class="border rounded-md p-2" />
        </div>
        <!-- Filing Date -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Filing Date & Time</label>
          <input type="datetime-local" v-model="complaintForm.filing_date" class="border rounded-md p-2" />
        </div>

        <!-- Status -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Status</label>
          <select v-model="complaintForm.status" class="border rounded-md p-2">
            <option value="Open">Open</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
          </select>
        </div>
        <!-- Witness -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Witness</label>
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
  background: #16A34A;
}

.multiselect__option--highlight::after {
  background: #16A34A;
}
</style>
