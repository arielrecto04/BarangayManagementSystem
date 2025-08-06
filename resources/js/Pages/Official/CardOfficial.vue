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

// Computed properties to organize officials
const barangayCaptain = computed(() =>
  officials.value.find(
    (official) => official.position && official.position.toLowerCase() === 'barangay captain'
  )
);

const otherOfficials = computed(() =>
  officials.value.filter(
    (official) => official.position && official.position.toLowerCase() !== 'barangay captain'
  )
);

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

onMounted(() => {
  officialStore.getOfficials();
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-10">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center">
      <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
    </div>

    <!-- Organizational Chart -->
    <template v-else>
      <div v-if="officials.length === 0" class="text-gray-500 text-center">No officials found.</div>
      <div v-else>
        <!-- Top Official (Barangay Captain) -->
        <div v-if="barangayCaptain" class="flex justify-center mb-10">
          <div class="bg-white rounded-xl shadow-md px-6 py-4 text-center w-64 relative group">
            <div class="w-20 h-20 bg-gray-200 rounded-full mx-auto mb-3"></div>
            <h2 class="text-lg font-bold truncate">{{ barangayCaptain.name }}</h2>
            <p class="text-sm text-gray-600 truncate">{{ barangayCaptain.position }}</p>
            <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ barangayCaptain.description }}</p>
            <p class="text-xs text-gray-500 break-words">Term: {{ barangayCaptain.terms }}</p>

            <!-- Action Buttons (Visible on Hover) -->
            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <button @click="viewOfficial(barangayCaptain)"
                class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs" title="View Details">
                👁️
              </button>
              <button @click="editOfficial(barangayCaptain.id)"
                class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs" title="Edit">
                ✏️
              </button>
              <button @click="deleteOfficial(barangayCaptain.id)"
                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs" title="Delete">
                🗑️
              </button>
            </div>
          </div>
        </div>

        <!-- Other Officials -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <div v-for="official in otherOfficials" :key="official.id"
            class="bg-white rounded-xl shadow-md px-6 py-4 text-center relative group">
            <div class="w-16 h-16 bg-gray-200 rounded-full mx-auto mb-3"></div>
            <h2 class="text-lg font-bold truncate">{{ official.name }}</h2>
            <p class="text-sm text-gray-600 truncate">{{ official.position }}</p>
            <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
            <p class="text-xs text-gray-500 break-words">Term: {{ official.terms }}</p>

            <!-- Action Buttons (Visible on Hover) -->
            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <button @click="viewOfficial(official)"
                class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs" title="View Details">
                👁️
              </button>
              <button @click="editOfficial(official.id)"
                class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs" title="Edit">
                ✏️
              </button>
              <button @click="deleteOfficial(official.id)"
                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs" title="Delete">
                🗑️
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- View Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="closeModal">
      <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b">
          <h2 class="text-2xl font-bold text-gray-800">Official Details</h2>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">
            ×
          </button>
        </div>

        <!-- Modal Content -->
        <div v-if="selectedOfficial" class="p-6">
          <div class="flex flex-col items-center mb-6">
            <!-- Profile Picture Placeholder -->
            <div class="w-32 h-32 bg-gray-200 rounded-full mb-4"></div>
            <h3 class="text-3xl font-bold text-gray-800">{{ selectedOfficial.name }}</h3>
            <p class="text-xl text-blue-600 font-semibold">{{ selectedOfficial.position }}</p>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-gray-700 border-b pb-2">Basic Information</h4>

              <div>
                <label class="text-sm font-medium text-gray-600">Full Name:</label>
                <p class="text-gray-800">{{ selectedOfficial.name || 'N/A' }}</p>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-600">Position:</label>
                <p class="text-gray-800">{{ selectedOfficial.position || 'N/A' }}</p>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-600">Terms:</label>
                <p class="text-gray-800">{{ selectedOfficial.terms || 'N/A' }}</p>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-600">Number of Terms:</label>
                <p class="text-gray-800">{{ selectedOfficial.no_of_per_term || 'N/A' }}</p>
              </div>
            </div>

            <!-- Dates Information -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-gray-700 border-b pb-2">Important Dates</h4>

              <div>
                <label class="text-sm font-medium text-gray-600">Elected Date:</label>
                <p class="text-gray-800">{{ formatDate(selectedOfficial.elected_date) }}</p>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-600">Start Date:</label>
                <p class="text-gray-800">{{ formatDate(selectedOfficial.start_date) }}</p>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-600">End Date:</label>
                <p class="text-gray-800">{{ formatDate(selectedOfficial.end_date) }}</p>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-3">Description</h4>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-gray-700 leading-relaxed">
                {{ selectedOfficial.description || 'No description available.' }}
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
            <button @click="editOfficial(selectedOfficial.id)"
              class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
              Edit Official
            </button>
            <button @click="closeModal"
              class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
              Close
            </button>
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
</style>