<script setup>
import { useOfficialStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import useToast from '@/Utils/useToast';

const officialStore = useOfficialStore();
const { officials, isLoading } = storeToRefs(officialStore);
const router = useRouter();
const { showToast } = useToast();

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
            <p class="text-xs text-gray-500 break-words">Term: {{ barangayCaptain.term }}</p>

            <!-- Action Buttons (Visible on Hover) -->
            <div class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button
                @click="editOfficial(barangayCaptain.id)"
                class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600"
              >
                Edit
              </button>
              <button
                @click="deleteOfficial(barangayCaptain.id)"
                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
              >
                Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Other Officials -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <div
            v-for="official in otherOfficials"
            :key="official.id"
            class="bg-white rounded-xl shadow-md px-6 py-4 text-center relative group"
          >
            <div class="w-16 h-16 bg-gray-200 rounded-full mx-auto mb-3"></div>
            <h2 class="text-lg font-bold truncate">{{ official.name }}</h2>
            <p class="text-sm text-gray-600 truncate">{{ official.position }}</p>
            <p class="text-xs text-gray-500 mt-2 line-clamp-3 break-words">{{ official.description }}</p>
            <p class="text-xs text-gray-500 break-words">Term: {{ official.term }}</p>

            <!-- Action Buttons (Visible on Hover) -->
            <div class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button
                @click="editOfficial(official.id)"
                class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600"
              >
                Edit
              </button>
              <button
                @click="deleteOfficial(official.id)"
                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>