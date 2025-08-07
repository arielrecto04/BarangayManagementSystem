<script setup>
import { ref, computed, onMounted, watch } from 'vue';
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
const officials = officialStore.officials || [];
const officialId = router.currentRoute.value.params.id;

const selectedResident = ref(null);
const residents = ref([]);
const residentSearch = ref('');

const filteredResidents = computed(() => {
  return residents.value.filter(resident => {
    const fullName = `${resident.first_name} ${resident.middle_name ?? ''} ${resident.last_name}`.toLowerCase();
    return fullName.includes(residentSearch.value.toLowerCase());
  });
});

const positionOptions = [
  { value: 'Barangay Captain', label: 'Barangay Captain', limit: 1, section: 'Barangay Officials' },
  { value: 'Barangay Secretary', label: 'Barangay Secretary', limit: 1, section: 'Barangay Officials' },
  // ... (include all position options, same as AddOfficial.vue)
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
  const currentCount = officials.filter(o =>
    o.position && o.position.toLowerCase() === position.toLowerCase() && o.id !== official.value?.id // exclude current official
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
  description: '',
  resident_id: null,
});

const positionValidation = computed(() => {
  if (!officialForm.value.position) return { canAdd: true, message: '' };
  return checkPositionLimit(officialForm.value.position);
});

watch(selectedResident, (val) => {
  officialForm.value.resident_id = val?.id ?? null;
  officialForm.value.firstName = val?.first_name ?? '';
  officialForm.value.middleName = val?.middle_name ?? '';
  officialForm.value.lastName = val?.last_name ?? '';
});

watch(() => official.value, (newVal) => {
  if (newVal) {
    const nameParts = newVal.name ? newVal.name.split(' ') : [];
    officialForm.value.firstName = nameParts[0] || '';
    officialForm.value.lastName = nameParts[nameParts.length - 1] || '';
    officialForm.value.middleName = nameParts.slice(1, nameParts.length - 1).join(' ') || '';

    if (newVal.terms) {
      const termParts = newVal.terms.split('-');
      officialForm.value.termFrom = termParts[0] || '';
      officialForm.value.termTo = termParts[1] || '';
    }

    officialForm.value.position = newVal.position || '';
    officialForm.value.no_of_per_term = newVal.no_of_per_term || null;
    officialForm.value.elected_date = newVal.elected_date || '';
    officialForm.value.start_date = newVal.start_date || '';
    officialForm.value.end_date = newVal.end_date || '';
    officialForm.value.description = newVal.description || '';

    if (newVal.resident_id && residents.value.length > 0) {
      const found = residents.value.find(r => r.id === newVal.resident_id);
      if (found) selectedResident.value = found;
    }
  }
});

const validateForm = () => {
  const requiredFields = [
    officialForm.value.firstName,
    officialForm.value.lastName,
    officialForm.value.position,
  ];

  if (!requiredFields.every(field => field !== '' && field !== null)) {
    showToast({ icon: 'error', title: 'Please fill all required fields before submitting.' });
    return false;
  }
  if (!positionValidation.value.canAdd) {
    showToast({ icon: 'error', title: 'Position limit reached', text: positionValidation.value.message });
    return false;
  }
  return true;
};

const updateOfficialData = async () => {
  if (!validateForm()) return;

  const payload = {
    name: `${officialForm.value.firstName} ${officialForm.value.middleName} ${officialForm.value.lastName}`.trim(),
    position: officialForm.value.position,
    terms: `${officialForm.value.termFrom}-${officialForm.value.termTo}`,
    no_of_per_term: officialForm.value.no_of_per_term,
    elected_date: officialForm.value.elected_date,
    start_date: officialForm.value.start_date,
    end_date: officialForm.value.end_date,
    description: officialForm.value.description,
    resident_id: officialForm.value.resident_id,
  };

  try {
    await officialStore.updateOfficial(payload);
    showToast({ icon: 'success', title: 'Official updated successfully' });
    router.push('/officials');
  } catch (error) {
    const errorMsg = error.response?.data?.message || error.response?.data?.errors?.join(', ') || error.message;
    showToast({ icon: 'error', title: 'Failed to update official', text: errorMsg });
  }
};

onMounted(async () => {
  await residentStore.getResidents();
  residents.value = residentStore.residents;

  if (official.value?.resident_id) {
    const found = residents.value.find(r => r.id === official.value.resident_id);
    if (found) selectedResident.value = found;
  }

  await officialStore.getOfficialById(officialId);
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex justify-center items-center p-5">
    <template v-if="isLoading || !official">
      <div class="animate-spin h-20 w-20 border-4 border-blue-600 border-t-transparent rounded-full"></div>
    </template>
    <template v-else>
      <form @submit.prevent="updateOfficialData"
        class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl p-0 overflow-hidden">
        <div class="flex flex-col md:flex-row">
          <!-- Left Panel -->
          <div
            class="md:w-1/3 bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <div
              class="w-40 h-40 mb-8 grid place-items-center bg-white rounded-xl border-2 border-dashed border-gray-300 shadow">
              <div class="text-center">
                <div class="text-4xl text-gray-400 mb-2">👤</div>
                <p class="text-sm text-gray-500">Upload Photo</p>
              </div>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1 text-center">Edit Official</h1>
            <h2 class="text-md font-medium text-gray-600 text-center">Official Profile Information</h2>
          </div>

          <!-- Right Panel -->
          <div class="md:w-2/3 p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- Resident Multiselect -->
              <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">Select Resident <span
                    class="text-red-500">*</span></label>
                <Multiselect v-model="selectedResident" :options="filteredResidents"
                  :custom-label="resident => `${resident.first_name} ${resident.middle_name ? resident.middle_name + ' ' : ''}${resident.last_name}`"
                  track-by="id" placeholder="Search or select resident" :searchable="true" :show-labels="false"
                  class="rounded-lg border border-gray-300 ring-0 focus:ring-2 focus:ring-green-400" />
              </div>

              <!-- Position Select -->
              <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">Position <span
                    class="text-red-500">*</span></label>
                <select v-model="officialForm.position" required
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent"
                  :class="{ 'border-red-500': !positionValidation.canAdd }">
                  <option value="">Select Position</option>
                  <optgroup v-for="(positions, section) in groupedPositions" :key="section" :label="section">
                    <option v-for="position in positions" :key="position.value" :value="position.value">
                      {{ position.label }} (Max: {{ position.limit === 999 ? 'Varies' : position.limit }})
                    </option>
                  </optgroup>
                </select>
                <div v-if="positionValidation.message"
                  :class="positionValidation.canAdd ? 'text-green-600' : 'text-red-600'" class="text-xs mt-2">
                  {{ positionValidation.message }}
                </div>
              </div>

              <!-- Term From -->
              <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">Term From (Year) <span
                    class="text-red-500">*</span></label>
                <input type="number" v-model="officialForm.termFrom" min="1900" max="2099" placeholder="22" required
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
              </div>

              <!-- Term To -->
              <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">Term To (Year) <span
                    class="text-red-500">*</span></label>
                <input type="number" v-model="officialForm.termTo" min="1900" max="2099" placeholder="25" required
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
              </div>

              <!-- Number of Terms -->
              <div class="sm:col-span-1">
                <label class="block text-sm font-semibold mb-2 text-gray-700">Number of Terms</label>
                <input type="number" v-model="officialForm.no_of_per_term" min="1" placeholder="1"
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
              </div>

              <!-- Elected Date -->
              <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">Elected Date</label>
                <input type="date" v-model="officialForm.elected_date"
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
              </div>

              <!-- Start Date -->
              <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">Start Date</label>
                <input type="date" v-model="officialForm.start_date"
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
              </div>

              <!-- End Date -->
              <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">End Date</label>
                <input type="date" v-model="officialForm.end_date"
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent" />
              </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
              <label class="block text-sm font-semibold mb-2 text-gray-700">Description</label>
              <textarea v-model="officialForm.description" rows="4"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-400 focus:border-transparent resize-y"
                placeholder="Enter a detailed description..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-4 mt-8">
              <button type="submit" :disabled="!positionValidation.canAdd"
                :class="positionValidation.canAdd ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed'"
                class="w-full sm:w-auto text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save
              </button>
              <router-link to="/officials"
                class="w-full sm:w-auto bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
              </router-link>
            </div>
          </div>
        </div>
      </form>
    </template>
  </div>
</template>
