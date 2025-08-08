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
    <form v-if="!isLoading && official" @submit.prevent="updateOfficialData" class="w-full max-w-6xl">
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">

        <!-- Left Column -->
        <div
          class="bg-gradient-to-b from-blue-50 to-white p-8 md:w-1/3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
          <h1 class="text-2xl font-bold mb-4 text-center">Edit Official</h1>
          <h2 class="text-base font-medium mb-6 text-center text-gray-600">Update Official Information</h2>

          <!-- Image Placeholder -->
          <div
            class="w-40 h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:bg-gray-50 transition">
            <div class="text-center">
              <div class="text-4xl text-gray-400 mb-2">📸</div>
              <p class="text-sm text-gray-500">Upload Photo</p>
            </div>
          </div>
        </div>

        <!-- Right Column (Form Fields) -->
        <div class="p-8 md:w-2/3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- First Name -->
            <div>
              <label class="text-sm font-semibold text-gray-700">First Name *</label>
              <input type="text" v-model="officialForm.firstName" placeholder="Juan" required
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full transition-all" />
            </div>

            <!-- Middle Name -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Middle Name</label>
              <input type="text" v-model="officialForm.middleName" placeholder="Dela"
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full transition-all" />
            </div>

            <!-- Last Name -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Last Name *</label>
              <input type="text" v-model="officialForm.lastName" placeholder="Cruz" required
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full transition-all" />
            </div>

            <!-- Position -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Position *</label>
              <input type="text" v-model="officialForm.position" placeholder="Barangay Captain" required
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full transition-all" />
            </div>
          </div>

          <!-- Terms -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
            <div>
              <label class="text-sm font-semibold text-gray-700">Term From (Year)</label>
              <input type="number" v-model="officialForm.termFrom" min="1900" max="2099" placeholder="2022"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Term To (Year)</label>
              <input type="number" v-model="officialForm.termTo" min="1900" max="2099" placeholder="2025"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Number of Term</label>
              <input type="number" v-model="officialForm.no_of_per_term"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Elected Date</label>
              <input type="date" v-model="officialForm.elected_date"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
              <label class="text-sm font-semibold text-gray-700">Start Date</label>
              <input type="date" v-model="officialForm.start_date"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">End Date</label>
              <input type="date" v-model="officialForm.end_date"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>

          <!-- Description -->
          <div class="mt-4">
            <label class="text-sm font-semibold text-gray-700">Description</label>
            <textarea v-model="officialForm.description" placeholder="Enter details about the official's role..."
              rows="4"
              class="resize-y border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
          </div>

          <!-- Buttons -->
          <div class="flex justify-center mt-8 gap-4">
            <button type="submit"
              class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all transform hover:scale-105 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Save Changes
            </button>

            <router-link to="/officials"
              class="bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Cancel
            </router-link>


          </div>
        </div>
      </div>
    </form>

    <!-- Loading Spinner -->
    <div v-else class="flex justify-center items-center">
      <div class="animate-spin h-16 w-16 border-4 border-blue-500 border-t-transparent rounded-full"></div>
    </div>
  </div>
</template>
