<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useComplaintStore } from '@/Stores'
import useToast from '@/Utils/useToast';

const router = useRouter();
const { showToast } = useToast();
const complaintStore = useComplaintStore();

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

const createComplaint = async () => {
  try {
    await complaintStore.addComplaint(complaintForm.value);
    showToast({ icon: 'success', title: 'Complaint created successfully' });
    router.push('/complaints');
  } catch (error) {
    showToast({ icon: 'error', title: error.message });
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
    <form @submit.prevent="createComplaint" class="bg-white p-10 rounded-xl shadow-md w-full max-w-4xl">
      <h1 class="text-2xl font-bold mb-6">Add New Complaint</h1>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Complainant Name</label>
          <input v-model="complaintForm.complainant_name" type="text" class="border rounded-md p-2" />
        </div>
        <div class="flex flex-col">
          <label class="font-semibold text-sm">Respondent Name</label>
          <input v-model="complaintForm.respondent_name" type="text" class="border rounded-md p-2" />
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
