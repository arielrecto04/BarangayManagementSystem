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
  supporting_documents: null,
  witness: '',
  status: '',
});

const handleFileUpload = (event) => {
  complaintForm.value.supporting_documents = event.target.files[0];
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
      formData.append(key, value);
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
        <!-- Supporting Document -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Supporting Documents</label>
          <input type="file" @change="handleFileUpload" class="border rounded-md p-2"
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
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
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Complainant ID</label>
          <input v-model="complaintForm.complainant_id" type="text" class="border rounded-md p-2" />
        </div>
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Respondent ID</label>
          <input v-model="complaintForm.respondent_id" type="text" class="border rounded-md p-2" />
        </div>
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Complainant ID</label>
          <input v-model="complaintForm.complainant_id" type="text" class="border rounded-md p-2" />
        </div>
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Respondent ID</label>
          <input v-model="complaintForm.respondent_id" type="text" class="border rounded-md p-2" />
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
  background: #3b82f6;
}

.multiselect__option--highlight::after {
  background: #3b82f6;
}
</style>
