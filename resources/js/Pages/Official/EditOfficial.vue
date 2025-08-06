<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useOfficialStore } from '@/Stores';
import useToast from '@/Utils/useToast';

const router = useRouter();
const { showToast } = useToast();
const officialStore = useOfficialStore();
const { official, isLoading } = storeToRefs(officialStore);
const officialId = router.currentRoute.value.params.id;

// Create local form object
const officialForm = ref({
  firstName: '',
  middleName: '',
  lastName: '',
  position: '',
  termFrom: '',
  termTo: '',
  no_of_per_term: null,
  elected_date: '',
  start_date: '',
  end_date: '',
  description: ''
});

// Watch for official data and split into form fields
watch(() => official.value, (newVal) => {
  if (newVal) {
    // Split full name into components
    const nameParts = newVal.name ? newVal.name.split(' ') : [];
    officialForm.value.firstName = nameParts[0] || '';
    officialForm.value.lastName = nameParts[nameParts.length - 1] || '';
    officialForm.value.middleName = nameParts.slice(1, nameParts.length - 1).join(' ') || '';

    // Split terms string
    if (newVal.terms) {
      const termParts = newVal.terms.split('-');
      officialForm.value.termFrom = termParts[0] || '';
      officialForm.value.termTo = termParts[1] || '';
    }

    // Copy other fields
    officialForm.value.position = newVal.position || '';
    officialForm.value.no_of_per_term = newVal.no_of_per_term || null;
    officialForm.value.elected_date = newVal.elected_date || '';
    officialForm.value.start_date = newVal.start_date || '';
    officialForm.value.end_date = newVal.end_date || '';
    officialForm.value.description = newVal.description || '';
  }
});

const updateOfficialData = async () => {
  try {
    // Compose server-expected fields ONLY
    const payload = {
      name: `${officialForm.value.firstName} ${officialForm.value.middleName} ${officialForm.value.lastName}`.trim(),
      position: officialForm.value.position,
      terms: `${officialForm.value.termFrom}-${officialForm.value.termTo}`,
      no_of_per_term: officialForm.value.no_of_per_term,
      elected_date: officialForm.value.elected_date,
      start_date: officialForm.value.start_date,
      end_date: officialForm.value.end_date,
      description: officialForm.value.description
    };

    await officialStore.updateOfficial(payload);

    showToast({ icon: 'success', title: 'Official updated successfully' });
    router.push('/officials');
  } catch (error) {
    const errorMsg = error.response?.data?.message ||
      error.response?.data?.errors?.join(', ') ||
      error.message;
    showToast({ icon: 'error', title: 'Failed to update official', text: errorMsg });
  }
};


onMounted(() => {
  officialStore.getOfficialById(officialId);
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
    <template v-if="isLoading || !official">
      <div class="animate-spin h-20 w-20 border-4 border-blue-600 border-t-transparent rounded-full"></div>
    </template>

    <template v-else>
      <form @submit.prevent="updateOfficialData" class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
        <h1 class="text-2xl font-bold mb-6">Edit Official</h1>
        <h2 class="text-lg font-semibold mb-4">Official Profile</h2>

        <div class="grid grid-cols-12 gap-4">
          <!-- Avatar Placeholder - Moved to top left -->
          <div class="col-span-4 row-span-2 flex justify-center items-center">
            <div class="w-40 h-40 bg-gray-200 rounded-md"></div>
          </div>

          <!-- First Name -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">First Name</label>
            <input type="text" v-model="officialForm.firstName" placeholder="Juan" required
              class="border border-gray-200 rounded-md px-4 py-2" />
          </div>

          <!-- Middle Name -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Middle Name</label>
            <input type="text" v-model="officialForm.middleName" placeholder="Dela" required
              class="border border-gray-200 rounded-md px-4 py-2" />
          </div>

          <!-- Last Name -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Last Name</label>
            <input type="text" v-model="officialForm.lastName" placeholder="Cruz" required
              class="border border-gray-200 rounded-md px-4 py-2" />
          </div>

          <!-- Position -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Position</label>
            <input type="text" v-model="officialForm.position" placeholder="Barangay Captain" required
              class="border border-gray-200 rounded-md px-4 py-2" />
          </div>

          <!-- Term From -->
          <div class="col-span-2 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Term From (Year)</label>
            <input type="number" v-model="officialForm.termFrom" required min="1900" max="2099" placeholder="22"
              class="border border-gray-200 rounded-md px-4 py-2" />
          </div>

          <!-- Term To -->
          <div class="col-span-2 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Term To (Year)</label>
            <input type="number" v-model="officialForm.termTo" required min="1900" max="2099" placeholder="25"
              class="border border-gray-200 rounded-md px-4 py-2" />
          </div>

          <!-- Number of Term -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Number of Term</label>
            <input type="number" v-model="officialForm.no_of_per_term"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm" />
          </div>

          <!-- Elected Date -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Elected Date</label>
            <input type="date" v-model="officialForm.elected_date"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm" />
          </div>

          <!-- Start Date -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Start Date</label>
            <input type="date" v-model="officialForm.start_date"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm" />
          </div>

          <!-- End Date -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">End Date</label>
            <input type="date" v-model="officialForm.end_date"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm" />
          </div>

          <!-- Description -->
          <div class="col-span-12 flex flex-col gap-2 mt-4">
            <label class="text-sm font-semibold text-gray-600">Description</label>
            <textarea v-model="officialForm.description" placeholder="Enter a detailed description..." rows="4"
              class="resize-y border border-gray-200 rounded-md px-4 py-2"></textarea>
          </div>
        </div>

        <div class="flex justify-center mt-10 gap-4">
          <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md">
            Save
          </button>
          <router-link to="/officials" class="bg-white px-6 py-2 rounded-xl shadow-xl font-bold hover:bg-gray-200">
            Cancel
          </router-link>
        </div>
      </form>
    </template>
  </div>
</template>