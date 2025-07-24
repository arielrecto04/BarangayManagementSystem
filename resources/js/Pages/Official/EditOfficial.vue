<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useOfficialStore } from '@/Stores';
import useToast from '@/Utils/useToast';

const router = useRouter();
const { showToast } = useToast();
const officialStore = useOfficialStore();
const { official, isLoading } = storeToRefs(officialStore);

// Get ID from the route
const officialId = router.currentRoute.value.params.id;

// Save/update action
const updateOfficialData = async () => {
  try {
    await officialStore.updateOfficial();
    showToast({ icon: 'success', title: 'Official updated successfully' });
    router.push('/officials');
  } catch (error) {
    showToast({ icon: 'error', title: error.message || 'Failed to update official' });
  }
};

// Fetch data on load
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
      <form @submit.prevent="updateOfficialData" class="bg-white p-10 rounded-xl shadow-lg max-w-3xl w-full">
        <h1 class="text-2xl font-bold mb-6">Edit Official</h1>

        <div class="grid gap-4 grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">First Name</label>
            <input v-model="official.first_name" type="text" required class="mt-1 w-full border rounded p-2" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Position</label>
            <input v-model="official.position" type="text" required class="mt-1 w-full border rounded p-2" />
          </div>

          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea v-model="official.description" rows="4" class="w-full border rounded p-2"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Term</label>
            <input v-model="official.term" type="number" class="w-full border rounded p-2" />
          </div>
        </div>

        <div class="mt-6 flex justify-center gap-4">
          <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">Save</button>
          <router-link to="/officials" class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Cancel</router-link>
        </div>
      </form>
    </template>
  </div>
</template>
