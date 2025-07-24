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
    term: '',
    
});



const createOfficial = async () => {

    console.log(officialDataForm.value);
    try {
        await officialStore.addOfficial(officialDataForm.value);
        showToast({ icon: 'success', title: 'Official created successfully' });
        router.push('/officials');

    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}


</script>

<template>
  <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
    <form @submit.prevent="createOfficial">
      <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
        <h1 class="text-2xl font-bold mb-6">Add New Official</h1>
        <h2 class="text-lg font-semibold mb-4">Official Profile</h2>

        <div class="grid grid-cols-3 gap-4">
          <!-- First Row -->
          <div class="flex flex-col gap-2">
            <label for="first_name" class="text-sm font-semibold text-gray-600">First Name</label>
            <input
              id="first_name"
              type="text"
              placeholder="First Name"
              v-model="officialDataForm.first_name"
              required
              class="col-span-1 border border-gray-200 rounded-md px-4 py-2"
            />
          </div>

          <div class="flex flex-col gap-2">
            <label for="position" class="text-sm font-semibold text-gray-600">Position</label>
            <input
              id="position"
              type="text"
              placeholder="Position"
              v-model="officialDataForm.position"
              required
              class="col-span-1 border border-gray-200 rounded-md px-4 py-2"
            />
          </div>

          <div class="row-span-3 flex justify-center items-center">
            <!-- Image placeholder (adjust w/h values to match Tailwind sizes) -->
            <div class="w-40 h-40 bg-gray-200 rounded-md"></div>
          </div>

          <!-- Second Row -->
          <div class="flex flex-col gap-2">
            <label for="description" class="text-sm font-semibold text-gray-600">Description</label>
            <textarea
              id="description"
              placeholder="Enter a detailed description..."
              v-model="officialDataForm.description"
              rows="4"
              class="resize-y border border-gray-200 rounded-md px-4 py-2"
            ></textarea>
          </div>

          <div class="flex flex-col gap-2">
            <label for="term" class="text-sm font-semibold text-gray-600">Term</label>
            <input
              id="term"
              type="number"
              placeholder="Term"
              v-model="officialDataForm.term"
              class="col-span-1 border border-gray-200 rounded-md px-4 py-2"
            />
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
