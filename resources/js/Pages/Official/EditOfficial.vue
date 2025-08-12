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

        <!-- Right Column (Form) -->
        <div class="p-8 md:w-2/3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Resident searchable dropdown -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Select Resident *</label>
              <Multiselect v-model="selectedResident" :options="residents"
                :custom-label="resident => `${resident.first_name} ${resident.middle_name || ''} ${resident.last_name}`.trim()"
                placeholder="Search and select resident" track-by="id" :searchable="true" :show-labels="false"
                :allow-empty="true" />
            </div>

            <!-- Position dropdown -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Position *</label>
              <select v-model="officialForm.position" required
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                :class="{ 'border-red-500': !positionValidation.canAdd }">
                <option value="">Select Position</option>
                <optgroup v-for="(positions, section) in groupedPositions" :key="section" :label="section">
                  <option v-for="position in positions" :key="position.value" :value="position.value">
                    {{ position.label }} (Max: {{ position.limit === 999 ? 'Varies' : position.limit }})
                  </option>
                </optgroup>
              </select>
              <div v-if="positionValidation.message"
                :class="positionValidation.canAdd ? 'text-blue-600' : 'text-red-600'" class="text-sm mt-1">
                {{ positionValidation.message }}
              </div>
            </div>
          </div>

          <!-- Terms inputs and description -->
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
              <label class="text-sm font-semibold text-gray-700">Number of Terms</label>
              <input type="number" v-model="officialForm.no_of_per_term"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Elected Date</label>
              <input type="date" v-model="officialForm.elected_date"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>

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
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Save Changes
            </button>

            <router-link to="/officials"
              class="bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Cancel
            </router-link>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
