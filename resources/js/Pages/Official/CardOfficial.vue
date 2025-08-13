<script setup>
import { useOfficialStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import useToast from '@/Utils/useToast';

const officialStore = useOfficialStore();
const { officials, isLoading } = storeToRefs(officialStore);
const router = useRouter();
const { showToast } = useToast();

// Modal state
const showModal = ref(false);
const selectedOfficial = ref(null);

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
  if (confirm('Are you sure you want to delete this official?')) {
    try {
      await officialStore.deleteOfficial(id);
      showToast({ icon: 'success', title: 'Official deleted successfully' });
    } catch (error) {
      showToast({ icon: 'error', title: 'Failed to delete official' });
    }
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
  <div class="min-h-screen bg-gray-100 p-6">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center">
      <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
    </div>

    <!-- Officials Organization Chart -->
    <template v-else>
      <div v-if="officials.length === 0" class="text-gray-500 text-center text-lg">No officials found.</div>

      <div v-else class="space-y-12">

        <!-- BARANGAY OFFICIALS SECTION -->
        <section>
          <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Barangay Officials</h1>

          <!-- Row 1: Barangay Captain -->
          <div v-if="barangayOfficials.captain.length" class="mb-8">
            <div class="flex justify-center">
              <div v-for="official in barangayOfficials.captain" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-6 py-4 text-center w-80 relative group mx-2">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full mx-auto mb-4"></div>
                <h2 class="text-xl font-bold truncate text-gray-800">{{ official.name }}</h2>
                <p class="text-base text-blue-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-sm text-gray-500 mt-3 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-sm text-gray-600 break-words mt-2">Term: {{ official.terms }}</p>

                <!-- Action Buttons -->
                <div class="absolute top-3 right-3 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)"
                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs" title="View"> <svg
                      class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg></button>
                  <button @click="editOfficial(official.id)"
                    class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs" title="Edit"> <svg
                      class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)"
                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs" title="Delete"> <svg
                      class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 2: Secretary & Treasurer -->
          <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
              <!-- Secretary -->
              <div v-for="official in barangayOfficials.secretary" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-6 py-4 text-center relative group">
                <div class="w-20 h-20 bg-gradient-to-br from-green-100 to-green-200 rounded-full mx-auto mb-4"></div>
                <h2 class="text-lg font-bold truncate">{{ official.name }}</h2>
                <p class="text-sm text-green-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">Term: {{ official.terms }}</p>

                <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)"
                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs">👁️</button>
                  <button @click="editOfficial(official.id)"
                    class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)"
                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>

              <!-- Treasurer -->
              <div v-for="official in barangayOfficials.treasurer" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-6 py-4 text-center relative group">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-purple-200 rounded-full mx-auto mb-4"></div>
                <h2 class="text-lg font-bold truncate">{{ official.name }}</h2>
                <p class="text-sm text-purple-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">Term: {{ official.terms }}</p>

                <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)"
                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)"
                    class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)"
                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 3: Barangay Councilors (max 7) -->
          <div v-if="barangayOfficials.councilors.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">Barangay Councilors</h3>
            <div
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-4 max-w-7xl mx-auto">
              <div v-for="official in barangayOfficials.councilors.slice(0, 7)" :key="official.id"
                class="bg-white rounded-xl shadow-md px-4 py-3 text-center relative group">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full mx-auto mb-3"></div>
                <h2 class="text-base font-bold truncate">{{ official.name }}</h2>
                <p class="text-xs text-orange-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">{{ official.terms }}</p>

                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)"
                    class="bg-green-500 text-white px-1 py-1 rounded hover:bg-green-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)"
                    class="bg-blue-500 text-white px-1 py-1 rounded hover:bg-blue-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)"
                    class="bg-red-500 text-white px-1 py-1 rounded hover:bg-red-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 4: SK Chairman -->
          <div v-if="skOfficials.chairman.length" class="mb-8">
            <div class="flex justify-center">
              <div v-for="official in skOfficials.chairman" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-6 py-4 text-center w-80 relative group mx-2">
                <div class="w-24 h-24 bg-gradient-to-br from-red-100 to-red-200 rounded-full mx-auto mb-4"></div>
                <h2 class="text-xl font-bold truncate text-gray-800">{{ official.name }}</h2>
                <p class="text-base text-red-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-sm text-gray-500 mt-3 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-sm text-gray-600 break-words mt-2">Term: {{ official.terms }}</p>

                <div class="absolute top-3 right-3 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)"
                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)"
                    class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)"
                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 5: SK Secretary & Treasurer -->
          <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
              <div v-for="official in skOfficials.secretary" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-6 py-4 text-center relative group">
                <div class="w-20 h-20 bg-gradient-to-br from-teal-100 to-teal-200 rounded-full mx-auto mb-4"></div>
                <h2 class="text-lg font-bold truncate">{{ official.name }}</h2>
                <p class="text-sm text-teal-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">Term: {{ official.terms }}</p>

                <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)"
                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)"
                    class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)"
                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>

              <div v-for="official in skOfficials.treasurer" :key="official.id"
                class="bg-white rounded-xl shadow-lg px-6 py-4 text-center relative group">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-full mx-auto mb-4"></div>
                <h2 class="text-lg font-bold truncate">{{ official.name }}</h2>
                <p class="text-sm text-indigo-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">Term: {{ official.terms }}</p>

                <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)"
                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)"
                    class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)"
                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 6: Youth Councilors (max 7) -->
          <div v-if="skOfficials.councilors.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">Youth Councilors</h3>
            <div
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-4 max-w-7xl mx-auto">
              <div v-for="official in skOfficials.councilors.slice(0, 7)" :key="official.id"
                class="bg-white rounded-xl shadow-md px-4 py-3 text-center relative group">
                <div class="w-16 h-16 bg-gradient-to-br from-pink-100 to-pink-200 rounded-full mx-auto mb-3"></div>
                <h2 class="text-base font-bold truncate">{{ official.name }}</h2>
                <p class="text-xs text-pink-600 font-semibold truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2 break-words">{{ official.description }}</p>
                <p class="text-xs text-gray-600 break-words">{{ official.terms }}</p>

                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)"
                    class="bg-green-500 text-white px-1 py-1 rounded hover:bg-green-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)"
                    class="bg-blue-500 text-white px-1 py-1 rounded hover:bg-blue-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)"
                    class="bg-red-500 text-white px-1 py-1 rounded hover:bg-red-600 text-xs"> <svg class="w-4 h-4"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- VOLUNTEER AND FUNCTIONAL PERSONNEL SECTION -->
        <section>
          <h1 class="text-4xl font-bold text-center text-gray-800 mb-8 border-t-4 border-gray-300 pt-8">Volunteer and
            Functional Personnel</h1>

          <!-- Row 7: Barangay Tanod (max 20, 10 columns) -->
          <div v-if="volunteerPersonnel.tanod.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">Barangay Tanod</h3>
            <div
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-10 gap-3 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.tanod.slice(0, 20)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-yellow-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Continue with other volunteer personnel sections... -->
          <!-- For brevity, I'll show the pattern for the remaining sections -->

          <!-- Row 8: Barangay Health Workers -->
          <div v-if="volunteerPersonnel.healthWorkers.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">Barangay Health Workers</h3>
            <div
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-10 gap-3 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.healthWorkers.slice(0, 10)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-green-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>

                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Additional sections would follow the same pattern... -->
          <!-- Row 9: Barangay Nutrition Scholars -->
          <div v-if="volunteerPersonnel.nutritionScholars.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">Barangay Nutrition Scholars</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-w-4xl mx-auto">
              <div v-for="official in volunteerPersonnel.nutritionScholars.slice(0, 3)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-rose-100 to-rose-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-rose-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 10: Lupon Tagapamayapa -->
          <div v-if="volunteerPersonnel.lupon.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">Lupon Tagapamayapa</h3>
            <div
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-10 gap-3 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.lupon.slice(0, 10)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-indigo-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 11: BADAC Members -->
          <div v-if="volunteerPersonnel.badac.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">BADAC Members</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.badac" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-lime-100 to-lime-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-lime-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 12: BCPC Members -->
          <div v-if="volunteerPersonnel.bcpc.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">BCPC Members</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 max-w-7xl mx-auto">
              <div v-for="official in volunteerPersonnel.bcpc" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-purple-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

        </section>

        <!-- BARANGAY PROGRAM BASED-COMMITTEES SECTION -->
        <section>
          <h1 class="text-4xl font-bold text-center text-gray-800 mb-8 border-t-4 border-gray-300 pt-8">Barangay Program
            Based-Committees</h1>
          <!-- Example: Row 13 BDRRMC -->
          <div v-if="programCommittees.bdrrmc.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">BDRRMC</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.bdrrmc.slice(0, 15)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-sky-100 to-sky-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-sky-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 14 BPOC -->
          <div v-if="programCommittees.bpoc.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">BPOC</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.bpoc.slice(0, 15)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-sky-100 to-sky-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-sky-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 15 Barangay Environment Committee -->
          <div v-if="programCommittees.environment.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">environment</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.environment.slice(0, 15)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-sky-100 to-sky-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-sky-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 16 GAD Committee (Gender and Development) -->
          <div v-if="programCommittees.gad.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">gad</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.gad.slice(0, 15)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-sky-100 to-sky-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-sky-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 17 VAWC Desk (Anti-Violence Against Women and Children) -->
          <div v-if="programCommittees.vawc.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">vawc</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.vawc.slice(0, 15)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-sky-100 to-sky-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-sky-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 18 BPLO Section (Business Permit and Licensing) -->
          <div v-if="programCommittees.bplo.length" class="mb-8">
            <h3 class="text-xl font-semibold text-center text-gray-700 mb-4">bplo</h3>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 max-w-7xl mx-auto">
              <div v-for="official in programCommittees.bplo.slice(0, 15)" :key="official.id"
                class="bg-white rounded-lg shadow-md px-3 py-2 text-center relative group">
                <div class="w-12 h-12 bg-gradient-to-br from-sky-100 to-sky-200 rounded-full mx-auto mb-2"></div>
                <h2 class="text-sm font-semibold truncate">{{ official.name }}</h2>
                <p class="text-xs text-sky-600 font-medium truncate">{{ official.position }}</p>
                <p class="text-xs text-gray-500 truncate">{{ official.terms }}</p>
                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewOfficial(official)" class="bg-green-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editOfficial(official.id)" class="bg-blue-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg></button>
                  <button @click="deleteOfficial(official.id)" class="bg-red-500 text-white px-1 py-1 rounded text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></button>
                </div>
              </div>
            </div>
          </div>


          <!-- Committee sections would follow similar pattern -->
          <!-- ... -->
        </section>
      </div>
    </template>

    <!-- View Modal (keeping the same modal from previous version) -->
    <div v-if="showModal"
      class="fixed inset-0 bg-white/30 backdrop-blur-md flex items-center justify-center z-50 p-4 animate-fade-in"
      @click="closeModal">
      <div
        class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform scale-95 animate-slide-up"
        @click.stop>
        <!-- Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 tracking-tight">
            Official Details
          </h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors text-2xl font-bold">
            ×
          </button>
        </div>

        <!-- Content -->
        <div v-if="selectedOfficial" class="p-6">
          <!-- Profile -->
          <div class="flex flex-col items-center mb-6 text-center">
            <div class="w-32 h-32 bg-gradient-to-tr from-gray-200 to-gray-300 rounded-full mb-4 shadow-inner"></div>
            <h3 class="text-3xl font-bold text-gray-900">
              {{ selectedOfficial.name }}
            </h3>
            <p class="text-lg text-blue-600 font-semibold">
              {{ selectedOfficial.position }}
            </p>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Info -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">
                Basic Information
              </h4>
              <div>
                <label class="text-sm font-medium text-gray-500">Full Name:</label>
                <p class="text-gray-900">{{ selectedOfficial.name || 'N/A' }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Position:</label>
                <p class="text-gray-900">{{ selectedOfficial.position || 'N/A' }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Terms:</label>
                <p class="text-gray-900">{{ selectedOfficial.terms || 'N/A' }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Number of Terms:</label>
                <p class="text-gray-900">
                  {{ selectedOfficial.no_of_per_term || 'N/A' }}
                </p>
              </div>
            </div>

            <!-- Important Dates -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">
                Important Dates
              </h4>
              <div>
                <label class="text-sm font-medium text-gray-500">Elected Date:</label>
                <p class="text-gray-900">
                  {{ formatDate(selectedOfficial.elected_date) }}
                </p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Start Date:</label>
                <p class="text-gray-900">
                  {{ formatDate(selectedOfficial.start_date) }}
                </p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">End Date:</label>
                <p class="text-gray-900">
                  {{ formatDate(selectedOfficial.end_date) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">
              Description
            </h4>
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <p class="text-gray-700 leading-relaxed">
                {{ selectedOfficial.description || 'No description available.' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
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