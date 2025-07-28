<script setup>
import { useOfficialStore } from '@/Stores'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';

const router = useRouter();
const { showToast } = useToast();

const officialStore = useOfficialStore();

const officialDataForm = ref({
    name: '',
    position: '',
    description: '',
    terms: '',
    no_of_per_term: '',
    elected_date: '',
    start_date: '',
    end_date: '',
    resident_id: '',
});

const createOfficial = async () => {
    try {
        await officialStore.addOfficial(officialDataForm.value);
        showToast({ icon: 'success', title: 'Official created successfully' });
        router.push('/officials');
    } catch (error) {
        // Improved error handling
        const errorMsg = error.response?.data?.message || 
                        error.response?.data?.errors?.join(', ') || 
                        error.message;
        showToast({ 
            icon: 'error', 
            title: 'Failed to create official',
            text: errorMsg 
        });
    }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
    <form @submit.prevent="createOfficial">
      <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
        <h1 class="text-2xl font-bold mb-6">Add New Official</h1>
        <h2 class="text-lg font-semibold mb-4">Official Profile</h2>

        <div class="grid grid-cols-12 gap-4">
          <!-- Full Name -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Full Name</label>
            <input
              type="text"
              v-model="officialDataForm.name"
              placeholder="Juan Dela Cruz"
              required
              class="border border-gray-200 rounded-md px-4 py-2"
            />
          </div>

          <!-- Position -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Position</label>
            <input
              type="text"
              v-model="officialDataForm.position"
              placeholder="Barangay Captain"
              required
              class="border border-gray-200 rounded-md px-4 py-2"
            />
          </div>

          <!-- Image Placeholder -->
          <div class="col-span-4 row-span-2 flex justify-center items-center">
            <div class="w-40 h-40 bg-gray-200 rounded-md"></div>
          </div>

          <!-- terms -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Terms</label>
            <input
              type="text"
              v-model="officialDataForm.terms"
              placeholder="YY-YY"
              class="border border-gray-200 rounded-md px-4 py-2"
            />
          </div>

          <!-- Resident ID -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Resident ID</label>
            <input
              type="text"
              v-model="officialDataForm.resident_id"
              placeholder="Resident ID"
              class="border border-gray-200 rounded-md px-4 py-2"
            />
          </div>

          <!-- Number of term -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Number of Term</label>
            <input
              type="number"
              v-model="officialDataForm.no_of_per_term"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm"
            />
          </div>

          <!-- Elected Date -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Elected Date</label>
            <input
              type="date"
              v-model="officialDataForm.elected_date"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm"
            />
          </div>

          <!-- Start Date -->
<div class="col-span-4 flex flex-col gap-2">
  <label class="text-sm font-semibold text-gray-600">Start Date</label>
  <input
    type="date"
    v-model="officialDataForm.start_date"
    class="border border-gray-300 rounded-md px-4 py-2 text-sm"
  />
</div>

          <!-- End Date -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">End Date</label>
            <input
              type="date"
              v-model="officialDataForm.end_date"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm"
            />
          </div>

          <!-- Description (full width) -->
          <div class="col-span-12 flex flex-col gap-2 mt-4">
            <label class="text-sm font-semibold text-gray-600">Description</label>
            <textarea
              v-model="officialDataForm.description"
              placeholder="Enter a detailed description..."
              rows="4"
              class="resize-y border border-gray-200 rounded-md px-4 py-2"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-center mt-10 gap-4">
          <button
            type="submit"
            class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md"
          >
            Save
          </button>
          <router-link
            to="/officials"
            class="bg-white px-6 py-2 rounded-xl shadow-xl ml-4 font-bold hover:bg-gray-200"
          >
            Cancel
          </router-link>
        </div>
      </div>
    </form>
  </div>
</template>