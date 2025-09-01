<script setup>
import { useOfficialStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import useToast from '@/Utils/useToast';
import Modal from '../../Components/Modal.vue'
import OfficialActions from './OfficialActions.vue';

const officialStore = useOfficialStore();
const { officials, isLoading } = storeToRefs(officialStore);
const router = useRouter();
const { showToast } = useToast();

// Modal state
const showModal = ref(false);
const selectedOfficial = ref(null);

// Helper function to format name properly
const formatName = (name) => {
  if (!name) return 'Unknown Official';

  // If the name contains "null", clean it up
  if (name.includes('null')) {
    return name
      .replace(/\s+null\s+/gi, ' ')  // Replace " null " with space
      .replace(/^null\s+/gi, '')     // Remove "null " at the beginning
      .replace(/\s+null$/gi, '')     // Remove " null" at the end
      .trim();
  }

  return name;
};

// Position limits configuration
const positionLimits = {
  'barangay captain': 1,
  'barangay secretary': 1,
  'barangay treasurer': 1,
  'barangay councilor': 7,
  'sk chairman': 1,
  'sk secretary': 1,
  'sk treasurer': 1,
  'youth councilor': 7,
  'barangay tanod': 20,
  'barangay health workers': 10,
  'barangay nutrition scholars': 3,
  'lupon tagapamayapa': 10,
  'badac members': 999, // varies
  'bcpc members': 999, // varies
  'bdrrmc': 15,
  'bpoc': 15,
  'barangay environment committee': 10,
  'gad committee': 8,
  'vawc desk': 3,
  'bplo section': 3
};

// Helper function to get officials by position
const getOfficialsByPosition = (positions) => {
  return officials.value.filter(official =>
    positions.some(pos =>
      official.position && official.position.toLowerCase().includes(pos.toLowerCase())
    )
  );
};

// Computed properties for each section
const barangayOfficials = computed(() => ({
  captain: getOfficialsByPosition(['barangay captain']),
  secretary: getOfficialsByPosition(['barangay secretary']),
  treasurer: getOfficialsByPosition(['barangay treasurer']),
  councilors: getOfficialsByPosition(['barangay councilor', 'kagawad'])
}));

const skOfficials = computed(() => ({
  chairman: getOfficialsByPosition(['sk chairman']),
  secretary: getOfficialsByPosition(['sk secretary']),
  treasurer: getOfficialsByPosition(['sk treasurer']),
  councilors: getOfficialsByPosition(['youth councilor'])
}));

const volunteerPersonnel = computed(() => ({
  tanod: getOfficialsByPosition(['barangay tanod', 'tanod']),
  healthWorkers: getOfficialsByPosition(['barangay health workers', 'health worker']),
  nutritionScholars: getOfficialsByPosition(['barangay nutrition scholars', 'nutrition scholar']),
  lupon: getOfficialsByPosition(['lupon tagapamayapa', 'lupon']),
  badac: getOfficialsByPosition(['badac', 'anti-drug']),
  bcpc: getOfficialsByPosition(['bcpc', 'children protection'])
}));

const programCommittees = computed(() => ({
  bdrrmc: getOfficialsByPosition(['bdrrmc', 'disaster risk']),
  bpoc: getOfficialsByPosition(['bpoc', 'peace and order']),
  environment: getOfficialsByPosition(['environment committee', 'environmental']),
  gad: getOfficialsByPosition(['gad committee', 'gender development']),
  vawc: getOfficialsByPosition(['vawc desk', 'violence against women']),
  bplo: getOfficialsByPosition(['bplo section', 'business permit'])
}));

const deleteOfficial = async (id) => {
  if (!confirm('Are you sure you want to delete this official?')) return;

  try {
    await officialStore.deleteOfficial(id);
    showToast({ icon: 'success', title: 'Official deleted successfully' });

    // Refresh list
    officialStore.getOfficials();

    // 🔑 Close the modal (same as in ListComplaints.vue)
    closeModal();
  } catch (error) {
    showToast({ icon: 'error', title: 'Failed to delete official' });
    console.error(error);
  }
};


const editOfficial = (id) => {
  router.push(`/officials/edit-official/${id}`);
};

const viewOfficial = (official) => {
  selectedOfficial.value = official;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedOfficial.value = null;
};

// Format date for display
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString();
};

// Helper function to render official cards
const renderOfficialCards = (officialsList, maxCols = 12) => {
  if (!officialsList.length) return null;
  const cols = Math.min(officialsList.length, maxCols);
  const colClass = cols <= 1 ? 'justify-center' :
    cols <= 2 ? 'grid-cols-1 md:grid-cols-2' :
      cols <= 3 ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' :
        cols <= 4 ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4' :
          cols <= 6 ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6' :
            'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7';

  return { officialsList, colClass, cols };
};

onMounted(() => {
  officialStore.getOfficials();
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-2 sm:p-4 lg:p-6">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center min-h-[50vh]">
      <div class="animate-spin rounded-full h-16 w-16 sm:h-32 sm:w-32 border-b-2 border-gray-900"></div>
    </div>

    <!-- Officials Organization Chart -->
    <template v-else>
      <div v-if="officials.length === 0" class="text-gray-500 text-center text-lg">No Officials Found.</div>

      <div v-else class="space-y-8 sm:space-y-12">

        <!-- BARANGAY OFFICIALS SECTION -->
        <section>
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-center text-gray-800 mb-4 sm:mb-6 lg:mb-8">
            Barangay Officials
          </h1>

          <!-- Row 1: Barangay Captain -->
          <div v-if="barangayOfficials.captain.length" class="mb-6 sm:mb-8">
            <div class="flex justify-center">
              <div v-for="official in barangayOfficials.captain" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-4 py-3 sm:px-6 sm:py-4 text-center w-full max-w-sm sm:max-w-md lg:w-80 relative group mx-2">
                <div
                  class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full mx-auto mb-3 sm:mb-4">
                </div>
                <h2 class="text-lg sm:text-xl font-bold truncate text-gray-800">{{ formatName(official.name) }}</h2>
                <p class="text-sm sm:text-base text-blue-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs sm:text-sm text-gray-500 mt-2 sm:mt-3 line-clamp-3 break-words">{{
                  official.description }}</p>
                <p class="text-xs sm:text-sm text-gray-600 break-words mt-2">Term: {{ official.terms }}</p>

                <!-- Action Buttons - Always visible on mobile, hover on desktop -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 2: Secretary & Treasurer -->
          <div class="mb-6 sm:mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 max-w-4xl mx-auto">
              <!-- Secretary -->
              <div v-for="official in barangayOfficials.secretary" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-4 py-3 sm:px-6 sm:py-4 text-center relative group">
                <div
                  class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-green-100 to-green-200 rounded-full mx-auto mb-3 sm:mb-4">
                </div>
                <h2 class="text-base sm:text-lg font-bold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-sm text-green-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">Term: {{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>

              <!-- Treasurer -->
              <div v-for="official in barangayOfficials.treasurer" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-4 py-3 sm:px-6 sm:py-4 text-center relative group">
                <div
                  class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-purple-100 to-purple-200 rounded-full mx-auto mb-3 sm:mb-4">
                </div>
                <h2 class="text-base sm:text-lg font-bold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-sm text-purple-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">Term: {{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 3: Barangay Councilors (max 7) -->
          <div v-if="barangayOfficials.councilors.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">Barangay Councilors</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-3 sm:gap-4 max-w-7xl mx-auto">
              <div v-for="official in barangayOfficials.councilors.slice(0, 7)" :key="official.id"
                class="bg-white rounded-xl shadow-md px-3 py-2 sm:px-4 sm:py-3 text-center relative group">
                <div
                  class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full mx-auto mb-2 sm:mb-3">
                </div>
                <h2 class="text-sm sm:text-base font-bold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-orange-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 sm:top-2 sm:right-2 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 4: SK Chairman -->
          <div v-if="skOfficials.chairman.length" class="mb-6 sm:mb-8">
            <div class="flex justify-center">
              <div v-for="official in skOfficials.chairman" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-4 py-3 sm:px-6 sm:py-4 text-center w-full max-w-sm sm:max-w-md lg:w-80 relative group mx-2">
                <div
                  class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 bg-gradient-to-br from-red-100 to-red-200 rounded-full mx-auto mb-3 sm:mb-4">
                </div>
                <h2 class="text-lg sm:text-xl font-bold truncate text-gray-800">{{ formatName(official.name) }}</h2>
                <p class="text-sm sm:text-base text-red-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs sm:text-sm text-gray-500 mt-2 sm:mt-3 line-clamp-3 break-words">{{
                  official.description }}</p>
                <p class="text-xs sm:text-sm text-gray-600 break-words mt-2">Term: {{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 5: SK Secretary & Treasurer -->
          <div class="mb-6 sm:mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 max-w-4xl mx-auto">
              <div v-for="official in skOfficials.secretary" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-4 py-3 sm:px-6 sm:py-4 text-center relative group">
                <div
                  class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-teal-100 to-teal-200 rounded-full mx-auto mb-3 sm:mb-4">
                </div>
                <h2 class="text-base sm:text-lg font-bold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-sm text-teal-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">Term: {{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>

              <div v-for="official in skOfficials.treasurer" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-4 py-3 sm:px-6 sm:py-4 text-center relative group">
                <div
                  class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-full mx-auto mb-3 sm:mb-4">
                </div>
                <h2 class="text-base sm:text-lg font-bold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-sm text-indigo-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">Term: {{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 6: Youth Councilors (max 7) -->
          <div v-if="skOfficials.councilors.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">Youth Councilors</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-3 sm:gap-4 max-w-7xl mx-auto">
              <div v-for="official in skOfficials.councilors.slice(0, 7)" :key="official.id"
                class="bg-white rounded-xl shadow-md px-3 py-2 sm:px-4 sm:py-3 text-center relative group">
                <div
                  class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-pink-100 to-pink-200 rounded-full mx-auto mb-2 sm:mb-3">
                </div>
                <h2 class="text-sm sm:text-base font-bold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-pink-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 sm:top-2 sm:right-2 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- VOLUNTEER AND FUNCTIONAL PERSONNEL SECTION -->
        <section>
          <h1
            class="text-2xl sm:text-3xl lg:text-4xl font-bold text-center text-gray-800 mb-4 sm:mb-6 lg:mb-8 border-t-2 sm:border-t-4 border-gray-300 pt-4 sm:pt-6 lg:pt-8">
            Volunteer and Functional Personnel
          </h1>

          <!-- Row 7: Barangay Tanod (max 20, responsive grid) -->
          <div v-if="volunteerPersonnel.tanod.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">Barangay Tanod</h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-10 gap-2 sm:gap-3 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.tanod.slice(0, 20)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-yellow-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 8: Barangay Health Workers -->
          <div v-if="volunteerPersonnel.healthWorkers.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">Barangay Health Workers
            </h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-10 gap-2 sm:gap-3 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.healthWorkers.slice(0, 10)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-green-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 9: Barangay Nutrition Scholars -->
          <div v-if="volunteerPersonnel.nutritionScholars.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">Barangay Nutrition
              Scholars</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 max-w-4xl mx-auto">
              <div v-for="official in volunteerPersonnel.nutritionScholars.slice(0, 3)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 sm:px-4 sm:py-3 text-center relative group">
                <div
                  class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-rose-100 to-rose-200 rounded-full mx-auto mb-2 sm:mb-3">
                </div>
                <h2 class="text-sm sm:text-base font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-rose-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 10: Lupon Tagapamayapa -->
          <div v-if="volunteerPersonnel.lupon.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">Lupon Tagapamayapa</h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-10 gap-2 sm:gap-3 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.lupon.slice(0, 10)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-indigo-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 11: BADAC Members -->
          <div v-if="volunteerPersonnel.badac.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">BADAC Members</h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-4 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.badac" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-lime-100 to-lime-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-lime-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <!-- Action Buttons Component -->
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 12: BCPC Members -->
          <div v-if="volunteerPersonnel.bcpc.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">BCPC Members</h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-4 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.bcpc" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-purple-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

        </section>

        <!-- BARANGAY PROGRAM BASED-COMMITTEES SECTION -->
        <section>
          <h1
            class="text-2xl sm:text-3xl lg:text-4xl font-bold text-center text-gray-800 mb-4 sm:mb-6 lg:mb-8 border-t-2 sm:border-t-4 border-gray-300 pt-4 sm:pt-6 lg:pt-8">
            Barangay Program Based-Committees
          </h1>

          <!-- Row 13: BDRRMC -->
          <div v-if="programCommittees.bdrrmc.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">BDRRMC</h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.bdrrmc.slice(0, 15)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-sky-100 to-sky-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-sky-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 14: BPOC -->
          <div v-if="programCommittees.bpoc.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">BPOC</h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.bpoc.slice(0, 15)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-cyan-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 15: Barangay Environment Committee -->
          <div v-if="programCommittees.environment.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">Environment Committee
            </h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.environment.slice(0, 10)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-emerald-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 16: GAD Committee (Gender and Development) -->
          <div v-if="programCommittees.gad.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">GAD Committee</h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.gad.slice(0, 8)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-2 py-2 sm:px-3 sm:py-2 text-center relative group">
                <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-violet-100 to-violet-200 rounded-full mx-auto mb-1 sm:mb-2">
                </div>
                <h2 class="text-xs sm:text-sm font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-violet-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-1 right-1 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 17: VAWC Desk (Anti-Violence Against Women and Children) -->
          <div v-if="programCommittees.vawc.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">VAWC Desk</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 max-w-4xl mx-auto">
              <div v-for="official in programCommittees.vawc.slice(0, 3)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 sm:px-4 sm:py-3 text-center relative group">
                <div
                  class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-pink-100 to-pink-200 rounded-full mx-auto mb-2 sm:mb-3">
                </div>
                <h2 class="text-sm sm:text-base font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-pink-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>

          <!-- Row 18: BPLO Section (Business Permit and Licensing) -->
          <div v-if="programCommittees.bplo.length" class="mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">BPLO Section</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 max-w-4xl mx-auto">
              <div v-for="official in programCommittees.bplo.slice(0, 3)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 sm:px-4 sm:py-3 text-center relative group">
                <div
                  class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-amber-100 to-amber-200 rounded-full mx-auto mb-2 sm:mb-3">
                </div>
                <h2 class="text-sm sm:text-base font-semibold truncate">{{ formatName(official.name) }}</h2>
                <p class="text-xs text-amber-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <!-- Action Buttons -->
                <div
                  class="absolute top-2 right-2 sm:top-3 sm:right-3 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                  <OfficialActions :official="official" @view="viewOfficial" @edit="editOfficial"
                    @delete="deleteOfficial" />
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </template>

    <!-- View Modal (Official Details) -->
    <Modal :show="showModal" title="Official Details" maxWidth="2xl" @close="closeModal">
      <div v-if="selectedOfficial">
        <!-- Profile -->
        <div class="flex flex-col items-center mb-4 sm:mb-6 text-center">
          <div
            class="w-24 h-24 sm:w-32 sm:h-32 bg-gradient-to-tr from-gray-200 to-gray-300 rounded-full mb-3 sm:mb-4 shadow-inner">
          </div>
          <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">
            {{ formatName(selectedOfficial.name) }}
          </h3>
          <p class="text-base sm:text-lg text-blue-600 font-semibold">
            {{ selectedOfficial.position }}
          </p>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
          <!-- Basic Info -->
          <div class="space-y-3 sm:space-y-4">
            <h4 class="text-base sm:text-lg font-semibold text-gray-800 border-b pb-2">
              Basic Information
            </h4>
            <div>
              <label class="text-sm font-medium text-gray-500">Full Name:</label>
              <p class="text-gray-900 text-sm sm:text-base">{{ formatName(selectedOfficial.name) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Position:</label>
              <p class="text-gray-900 text-sm sm:text-base">{{ selectedOfficial.position || 'N/A' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Length of Term:</label>
              <p class="text-gray-900 text-sm sm:text-base">{{ selectedOfficial.terms || 'N/A' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Term Count:</label>
              <p class="text-gray-900 text-sm sm:text-base">{{ selectedOfficial.no_of_per_term || 'N/A' }}</p>
            </div>
          </div>

          <!-- Important Dates -->
          <div class="space-y-3 sm:space-y-4">
            <h4 class="text-base sm:text-lg font-semibold text-gray-800 border-b pb-2">
              Important Dates
            </h4>
            <div>
              <label class="text-sm font-medium text-gray-500">Elected Date:</label>
              <p class="text-gray-900 text-sm sm:text-base">{{ formatDate(selectedOfficial.elected_date) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Start Date:</label>
              <p class="text-gray-900 text-sm sm:text-base">{{ formatDate(selectedOfficial.start_date) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">End Date:</label>
              <p class="text-gray-900 text-sm sm:text-base">{{ formatDate(selectedOfficial.end_date) }}</p>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="mt-4 sm:mt-6">
          <h4 class="text-base sm:text-lg font-semibold text-gray-800 border-b pb-2 mb-3">
            Description
          </h4>
          <div class="bg-gray-50 p-3 sm:p-4 rounded-xl border border-gray-100">
            <p class="text-gray-700 leading-relaxed text-sm sm:text-base">
              {{ selectedOfficial.description || 'No description available.' }}
            </p>
          </div>
        </div>

        <!-- Edit/Delete Buttons -->
        <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4 mt-4 sm:mt-6">
          <button @click="editOfficial(selectedOfficial.id)"
            class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition text-sm sm:text-base">
            Edit
          </button>
          <button @click="deleteOfficial(selectedOfficial.id)"
            class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition text-sm sm:text-base">
            Delete
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>