<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const form = ref({
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
});

const fetchComplaint = async () => {
  try {
    const response = await axios.get(`/api/complaints/${route.params.id}`);
    form.value = { ...response.data };
  } catch (error) {
    console.error('Failed to load complaint:', error);
  }
};

const updateComplaint = async () => {
  try {
    await axios.put(`/api/complaints/${route.params.id}`, form.value);
    router.push('/complaints');
  } catch (error) {
    console.error('Failed to update complaint:', error);
  }
};

onMounted(fetchComplaint);
</script>

<template>
  <div>
    <h1 class="mb-4 text-xl font-bold">Edit Complaint</h1>
    <form @submit.prevent="updateComplaint" class="space-y-4">
      <div>
        <label>Case No</label>
        <input v-model="form.case_no" type="text" class="border w-full px-2 py-1" required />
      </div>
      <div>
        <label>Title</label>
        <input v-model="form.title" type="text" class="border w-full px-2 py-1" required />
      </div>
      <div>
        <label>Description</label>
        <textarea v-model="form.description" class="border w-full px-2 py-1" required></textarea>
      </div>
      <div>
        <label>Resolution</label>
        <textarea v-model="form.resolution" class="border w-full px-2 py-1"></textarea>
      </div>
      <div>
        <label>Date</label>
        <input v-model="form.date" type="date" class="border w-full px-2 py-1" />
      </div>
      <div>
        <label>Filing Date</label>
        <input v-model="form.filing_date" type="date" class="border w-full px-2 py-1" />
      </div>
      <div>
        <label>Complainant Name</label>
        <input v-model="form.complainant_name" type="text" class="border w-full px-2 py-1" />
      </div>
      <div>
        <label>Respondent Name</label>
        <input v-model="form.respondent_name" type="text" class="border w-full px-2 py-1" />
      </div>
      <div>
        <label>Complainant ID</label>
        <input v-model="form.complainant_id" type="number" class="border w-full px-2 py-1" />
      </div>
      <div>
        <label>Respondent ID</label>
        <input v-model="form.respondent_id" type="number" class="border w-full px-2 py-1" />
      </div>
      <div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2">Update</button>
      </div>
    </form>
  </div>
</template>