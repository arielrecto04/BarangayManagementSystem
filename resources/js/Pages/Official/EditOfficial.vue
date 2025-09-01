<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useOfficialStore, useResidentStore } from '@/Stores';
import useToast from '@/Utils/useToast';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const router = useRouter();
const { showToast } = useToast();
const officialStore = useOfficialStore();
const residentStore = useResidentStore();
const { official, isLoading } = storeToRefs(officialStore);
const officialId = router.currentRoute.value.params.id;

// Residents list and selected resident
const residents = ref([]);
const selectedResident = ref(null);

// Local form fields
const officialForm = ref({
  position: '',
  termFrom: '',
  termTo: '',
  no_of_per_term: null,
  elected_date: '',
  start_date: '',
  end_date: '',
  description: ''
});

// Position options (same as AddOfficial.vue)
const positionOptions = [
  { value: 'Barangay Captain', label: 'Barangay Captain', limit: 1, section: 'Barangay Officials' },
  { value: 'Barangay Secretary', label: 'Barangay Secretary', limit: 1, section: 'Barangay Officials' },
  { value: 'Barangay Treasurer', label: 'Barangay Treasurer', limit: 1, section: 'Barangay Officials' },
  { value: 'Barangay Councilor', label: 'Barangay Councilor', limit: 7, section: 'Barangay Officials' },
  { value: 'SK Chairman', label: 'SK Chairman', limit: 1, section: 'Barangay Officials' },
  { value: 'SK Secretary', label: 'SK Secretary', limit: 1, section: 'Barangay Officials' },
  { value: 'SK Treasurer', label: 'SK Treasurer', limit: 1, section: 'Barangay Officials' },
  { value: 'Youth Councilor', label: 'Youth Councilor', limit: 7, section: 'Barangay Officials' },
  { value: 'Barangay Tanod', label: 'Barangay Tanod', limit: 20, section: 'Volunteer and Functional Personnel' },
  { value: 'Barangay Health Workers', label: 'Barangay Health Workers', limit: 10, section: 'Volunteer and Functional Personnel' },
  { value: 'Barangay Nutrition Scholars', label: 'Barangay Nutrition Scholars', limit: 3, section: 'Volunteer and Functional Personnel' },
  { value: 'Lupon Tagapamayapa', label: 'Lupon Tagapamayapa', limit: 10, section: 'Volunteer and Functional Personnel' },
  { value: 'BADAC Members', label: 'BADAC Members (Barangay Anti-Drug Council)', limit: 999, section: 'Volunteer and Functional Personnel' },
  { value: 'BCPC Members', label: 'BCPC Members (Barangay Council for the Protection of Children)', limit: 999, section: 'Volunteer and Functional Personnel' },
  { value: 'BDRRMC', label: 'BDRRMC (Barangay Disaster Risk Reduction and Management Committee)', limit: 15, section: 'Barangay Program Based-Committees' },
  { value: 'BPOC', label: 'BPOC (Barangay Peace and Order Council)', limit: 15, section: 'Barangay Program Based-Committees' },
  { value: 'Barangay Environment Committee', label: 'Barangay Environment Committee', limit: 10, section: 'Barangay Program Based-Committees' },
  { value: 'GAD Committee', label: 'GAD Committee (Gender and Development)', limit: 8, section: 'Barangay Program Based-Committees' },
  { value: 'VAWC Desk', label: 'VAWC Desk (Anti-Violence Against Women and Children)', limit: 3, section: 'Barangay Program Based-Committees' },
  { value: 'BPLO Section', label: 'BPLO Section (Business Permit and Licensing)', limit: 3, section: 'Barangay Program Based-Committees' }
];

const groupedPositions = computed(() => {
  const groups = {};
  positionOptions.forEach(option => {
    if (!groups[option.section]) groups[option.section] = [];
    groups[option.section].push(option);
  });
  return groups;
});

const checkPositionLimit = (position) => {
  const positionOption = positionOptions.find(opt => opt.value === position);
  if (!positionOption) return { canAdd: true, message: '' };

  const currentCount = officialStore.officials.filter(
    official => official.position && official.position.toLowerCase() === position.toLowerCase()
      && official.id != officialId
  ).length;

  if (positionOption.limit === 999) {
    return { canAdd: true, message: `Current count: ${currentCount} (varies)` };
  }

  const canAdd = currentCount < positionOption.limit;
  const message = canAdd
    ? `Current count: ${currentCount}/${positionOption.limit}`
    : `Limit reached: ${currentCount}/${positionOption.limit}`;

  return { canAdd, message };
};

const positionValidation = computed(() => {
  if (!officialForm.value.position) return { canAdd: true, message: '' };
  return checkPositionLimit(officialForm.value.position);
});


// When residents load or change, update selectedResident if official data exists and selectedResident is not set
watch([residents, official], ([newResidents, newOfficial]) => {
  if (newResidents.length > 0 && newOfficial && !selectedResident.value) {
    // Try to find resident by resident_id
    if (newOfficial.resident_id) {
      selectedResident.value = newResidents.find(r => r.id === newOfficial.resident_id) || null;
    } else if (newOfficial.name) {
      // fallback: case-insensitive trimmed full name match
      const nameToFind = newOfficial.name.trim().toLowerCase();
      selectedResident.value = newResidents.find(r => {
        const fullName = `${r.first_name} ${r.middle_name || ''} ${r.last_name}`.trim().toLowerCase();
        return fullName === nameToFind;
      }) || null;
    }
  }
});

watch(official, (newVal) => {
  if (newVal) {
    const matchedOption = positionOptions.find(
      opt => opt.value.toLowerCase() === (newVal.position || '').toLowerCase()
    );
    officialForm.value.position = matchedOption?.value || '';

    if (newVal.terms) {
      const termParts = newVal.terms.split('-');
      officialForm.value.termFrom = termParts[0] || '';
      officialForm.value.termTo = termParts[1] || '';
    }

    officialForm.value.no_of_per_term = newVal.no_of_per_term || null;
    officialForm.value.elected_date = newVal.elected_date || '';
    officialForm.value.start_date = newVal.start_date || '';
    officialForm.value.end_date = newVal.end_date || '';
    officialForm.value.description = newVal.description || '';
  }
});

const validateForm = () => {
  if (!selectedResident.value) {
    showToast({ icon: 'error', title: 'Please select a resident.' });
    return false;
  }
  if (!officialForm.value.position) {
    showToast({ icon: 'error', title: 'Please select a position.' });
    return false;
  }
  if (!positionValidation.value.canAdd) {
    const currentPosition = official.value?.position || '';
    if (currentPosition.toLowerCase() !== officialForm.value.position.toLowerCase()) {
      showToast({ icon: 'error', title: 'Position limit reached', text: positionValidation.value.message });
      return false;
    }
  }
  return true;
};

const updateOfficialData = async () => {
  if (!validateForm()) return;

  const payload = {
    name: `${selectedResident.value.first_name} ${selectedResident.value.middle_name || ''} ${selectedResident.value.last_name}`.trim(),
    position: officialForm.value.position,
    terms: (officialForm.value.termFrom && officialForm.value.termTo)
      ? `${officialForm.value.termFrom}-${officialForm.value.termTo}`
      : null,
    no_of_per_term: officialForm.value.no_of_per_term || null,
    elected_date: officialForm.value.elected_date || null,
    start_date: officialForm.value.start_date || null,
    end_date: officialForm.value.end_date || null,
    description: officialForm.value.description || null,
    resident_id: selectedResident.value.id  // keep resident_id for backend
  };

  try {
    console.log('Submitting update payload:', payload);
    await officialStore.updateOfficial(officialId, payload);
    showToast({ icon: 'success', title: 'Official updated successfully' });
    router.push('/officials');
  } catch (error) {
    const errorMsg = error.response?.data?.message
      || error.response?.data?.errors?.join(', ')
      || error.message;
    showToast({ icon: 'error', title: 'Failed to update official', text: errorMsg });
  }
};

onMounted(async () => {
  await residentStore.getResidents();
  residents.value = residentStore.residents;
  await officialStore.getOfficialById(officialId);
});
</script>


<template>
  <div class="min-h-screen bg-gray-100 p-4 md:p-10">
    <form v-if="!isLoading && official" @submit.prevent="updateOfficialData" class="w-full max-w-6xl mx-auto">
      <div class="bg-white rounded-xl md:rounded-2xl shadow-lg md:shadow-xl overflow-hidden">

        <!-- Mobile Header -->
        <div class="md:hidden bg-gradient-to-r from-blue-50 to-white p-4 border-b border-gray-200">
          <h1 class="text-xl font-bold text-center text-gray-800">Edit Official</h1>
          <p class="text-sm text-center text-gray-600 mt-1">Update Official Information</p>
        </div>

        <!-- Desktop Two Column Layout -->
        <div class="flex flex-col md:flex-row">

          <!-- Left Column - Hidden on mobile, visible on desktop -->
          <div
            class="hidden md:flex bg-gradient-to-b from-blue-50 to-white p-8 md:w-1/3 flex-col items-center justify-center border-r border-gray-200">
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

          <!-- Form Content -->
          <div class="p-4 md:p-8 md:w-2/3">

            <!-- Mobile Image Upload (visible only on mobile) -->
            <div class="md:hidden mb-6 flex justify-center">
              <div
                class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:bg-gray-50 transition">
                <div class="text-center">
                  <div class="text-3xl text-gray-400 mb-1">📸</div>
                  <p class="text-xs text-gray-500">Upload Photo</p>
                </div>
              </div>
            </div>

            <!-- Resident and Position Selection -->
            <div class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
              <!-- Resident searchable dropdown -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Resident *</label>
                <Multiselect v-model="selectedResident" :options="residents"
                  :custom-label="resident => `${resident.first_name} ${resident.middle_name || ''} ${resident.last_name}`.trim()"
                  placeholder="Search and select resident" track-by="id" :searchable="true" :show-labels="false"
                  :allow-empty="true" />
              </div>

              <!-- Position dropdown -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Position *</label>
                <select v-model="officialForm.position" required
                  class="border border-gray-300 rounded-lg px-3 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm"
                  :class="{ 'border-red-500': !positionValidation.canAdd }">
                  <option value="">Select Position</option>
                  <optgroup v-for="(positions, section) in groupedPositions" :key="section" :label="section">
                    <option v-for="position in positions" :key="position.value" :value="position.value">
                      {{ position.label }} (Max: {{ position.limit === 999 ? 'Varies' : position.limit }})
                    </option>
                  </optgroup>
                </select>
                <div v-if="positionValidation.message"
                  :class="positionValidation.canAdd ? 'text-blue-600' : 'text-red-600'" class="text-xs mt-1">
                  {{ positionValidation.message }}
                </div>
              </div>
            </div>

            <!-- Term Information - Mobile: Stack, Desktop: 4 columns -->
            <div class="mt-6">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Term Information</h3>
              <div class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Term From (Year)</label>
                  <input type="number" v-model="officialForm.termFrom" min="1900" max="2099" placeholder="2022"
                    class="border border-gray-300 rounded-lg px-3 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Term To (Year)</label>
                  <input type="number" v-model="officialForm.termTo" min="1900" max="2099" placeholder="2025"
                    class="border border-gray-300 rounded-lg px-3 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Number of Terms</label>
                  <input type="number" v-model="officialForm.no_of_per_term"
                    class="border border-gray-300 rounded-lg px-3 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Elected Date</label>
                  <input type="date" v-model="officialForm.elected_date"
                    class="border border-gray-300 rounded-lg px-3 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm" />
                </div>
              </div>
            </div>

            <!-- Dates Section -->
            <div class="mt-6">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Service Dates</h3>
              <div class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                  <input type="date" v-model="officialForm.start_date"
                    class="border border-gray-300 rounded-lg px-3 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                  <input type="date" v-model="officialForm.end_date"
                    class="border border-gray-300 rounded-lg px-3 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm" />
                </div>
              </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
              <textarea v-model="officialForm.description" placeholder="Enter details about the official's role..."
                rows="4"
                class="resize-y border border-gray-300 rounded-lg px-3 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm"></textarea>
            </div>

            <!-- Action Buttons - Mobile: Stack, Desktop: Side by side -->
            <div class="mt-8 flex flex-col md:flex-row justify-center gap-3 md:gap-4">
              <button type="submit"
                class="w-full md:w-auto bg-green-500 hover:bg-green-600 text-white px-6 md:px-8 py-3 rounded-lg md:rounded-xl shadow-lg font-semibold transition-all transform active:scale-95 md:hover:scale-105 flex items-center justify-center gap-2">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
              </button>

              <router-link to="/officials"
                class="w-full md:w-auto bg-white border-2 border-gray-300 hover:border-gray-400 px-6 md:px-8 py-3 rounded-lg md:rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform active:scale-95 md:hover:scale-105 flex items-center justify-center gap-2">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </form>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center min-h-[400px]">
      <div class="animate-spin rounded-full h-12 w-12 md:h-16 md:w-16 border-b-2 border-blue-500"></div>
    </div>

    <!-- No Official Found State -->
    <div v-if="!isLoading && !official" class="text-center py-12">
      <div class="text-gray-500 text-lg mb-4">Official not found</div>
      <router-link to="/officials"
        class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Officials
      </router-link>
    </div>
  </div>
</template>