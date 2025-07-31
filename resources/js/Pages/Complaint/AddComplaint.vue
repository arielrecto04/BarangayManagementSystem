<script setup>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import { useRouter } from 'vue-router';
import { useComplaintStore, useResidentStore } from '@/Stores';
import useToast from '@/Utils/useToast';
import { onMounted, ref, watch } from 'vue';


const router = useRouter();
const { showToast } = useToast();
const complaintStore = useComplaintStore();
const residentStore = useResidentStore();
const residents = ref([]);

const selectedComplainant = ref(null);
const selectedRespondent = ref(null);

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

const complaintForm = ref({
  complainant_name: '',
  respondent_name: '',
  case_no: '',
  title: '',
  description: '',
  resolution: '',
  date: '',
  filing_date: '',
  complainant_id: '',
  respondent_id: ''
});

const formErrors = ref({});

const submitForm = async () => {
  formErrors.value = {}; // reset

  // Basic frontend validation
  const requiredFields = [
    'complainant_id',
    'respondent_id',
    'case_no',
    'title',
    'description',
    'resolution',
    'date',
    'filing_date'
  ];

  requiredFields.forEach(field => {
    if (!complaintForm.value[field]) {
      formErrors.value[field] = 'Please fill out this field';
    }
  });

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

    await complaintStore.createComplaint(complaintForm.value);
    showToast({ icon: 'success', title: 'Complaint submitted successfully.' });

    // Refresh complaints list and redirect to list view
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
          <Multiselect
            v-model="selectedComplainant"
            :options="residents"
            :custom-label="resident => `${resident.first_name} ${resident.last_name}`"
            track-by="id"
            placeholder="Search or select complainant"
            :searchable="true"
            :show-labels="false"
          />
        </div>

        <!-- Respondent Searchable Dropdown -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm mb-1">Respondent</label>
          <Multiselect
            v-model="selectedRespondent"
            :options="residents"
            :custom-label="resident => `${resident.first_name} ${resident.last_name}`"
            track-by="id"
            placeholder="Search or select respondent"
            :searchable="true"
            :show-labels="false"
          />
        </div>
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Case No</label>
          <input v-model="complaintForm.case_no" type="text" class="border rounded-md p-2" />
        </div>
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Title</label>
          <input v-model="complaintForm.title" type="text" class="border rounded-md p-2" />
        </div>
        <div class="flex flex-col col-span-2">
          <label class="font-semibold text-sm">Description</label>
          <textarea v-model="complaintForm.description" class="border rounded-md p-2"></textarea>
        </div>
        <div class="flex flex-col col-span-2">
          <label class="font-semibold text-sm">Resolution</label>
          <textarea v-model="complaintForm.resolution" class="border rounded-md p-2"></textarea>
        </div>
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Date</label>
          <input type="date" v-model="complaintForm.date" class="border rounded-md p-2" />
        </div>
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Filing Date</label>
          <input type="date" v-model="complaintForm.filing_date" class="border rounded-md p-2" />
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
