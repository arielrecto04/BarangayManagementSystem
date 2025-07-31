<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useComplaintStore, useResidentStore } from '@/Stores';
import useToast from '@/Utils/useToast';
import { axios } from '@/utils';

const router = useRouter();
const route = useRoute();
const { showToast } = useToast();

const complaintStore = useComplaintStore();
const residentStore = useResidentStore();
const residents = ref([]);
const complaintId = route.params.id;
const isLoading = ref(true);

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
  respondent_id: '',
  status: '',
});

const formErrors = ref({});

onMounted(async () => {
  try {
    await residentStore.getResidents();
    residents.value = residentStore.residents;

    const response = await axios.get(`/api/complaints/${complaintId}`);
    Object.assign(complaintForm.value, response.data);
  } catch (error) {
    console.error(error);
    showToast({ icon: 'error', title: 'Error loading complaint data' });
  } finally {
    isLoading.value = false;
  }
});


const submitForm = async () => {
  formErrors.value = {}; // reset

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

    await complaintStore.updateComplaint(complaintId, complaintForm.value);
    showToast({ icon: 'success', title: 'Complaint updated successfully.' });
    router.push('/complaints');
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
    <div v-if="isLoading">Loading...</div>
    <form @submit.prevent="submitForm" class="bg-white p-10 rounded-xl shadow-md w-full max-w-4xl">
      <h1 class="text-2xl font-bold mb-6">Edit Complaint</h1>

      <div class="grid grid-cols-2 gap-4">
        <!-- Same form fields as AddComplaint.vue -->
        <!-- Complainant -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Complainant</label>
          <select v-model="complaintForm.complainant_id" class="border rounded-md p-2">
            <option disabled value="">Select Complainant</option>
            <option v-for="resident in residents" :key="resident.id" :value="resident.id">
              {{ resident.first_name }} {{ resident.last_name }}
            </option>
          </select>
        </div>

        <!-- Respondent -->
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Respondent</label>
          <select v-model="complaintForm.respondent_id" class="border rounded-md p-2">
            <option disabled value="">Select Respondent</option>
            <option v-for="resident in residents" :key="resident.id" :value="resident.id">
              {{ resident.first_name }} {{ resident.last_name }}
            </option>
          </select>
        </div>

        <!-- Other fields -->
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
  <label class="font-semibold text-sm">Status</label>
  <select v-model="complaintForm.status" class="border rounded-md p-2">
    <option value="Open">Open</option>
    <option value="In Progress">In Progress</option>
    <option value="Resolved">Resolved</option>
  </select>
</div>

        <div class="flex flex-col">
          <label class="font-semibold text-sm">Filing Date</label>
          <input type="date" v-model="complaintForm.filing_date" class="border rounded-md p-2" />
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">Update</button>
        <router-link to="/complaints" class="bg-gray-300 px-4 py-2 rounded-md">Cancel</router-link>
      </div>
    </form>
  </div>
</template>
