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

const officialId = router.currentRoute.value.params.id;

const updateOfficialData = async () => {
  try {
    await officialStore.updateOfficial();
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
      <form @submit.prevent="updateOfficialData" class="bg-white p-10 rounded-xl shadow-lg max-w-5xl w-full">
        <h1 class="text-2xl font-bold mb-6">Edit Official</h1>
        <h2 class="text-lg font-semibold mb-4">Official Profile</h2>

        <div class="grid grid-cols-12 gap-4">
          <!-- Full Name -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Full Name</label>
            <input
              type="text"
              v-model="official.name"
              class="border border-gray-200 rounded-md px-4 py-2"
              required
            />
          </div>

          <!-- Position -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Position</label>
            <input
              type="text"
              v-model="official.position"
              class="border border-gray-200 rounded-md px-4 py-2"
              required
            />
          </div>

          <!-- Placeholder -->
          <div class="col-span-4 row-span-2 flex justify-center items-center">
            <div class="w-40 h-40 bg-gray-200 rounded-md"></div>
          </div>

          <!-- Terms -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Terms</label>
            <input
              type="text"
              v-model="official.terms"
              class="border border-gray-200 rounded-md px-4 py-2"
            />
          </div>

          <!-- Resident ID -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Resident ID</label>
            <input
              type="text"
              v-model="official.resident_id"
              class="border border-gray-200 rounded-md px-4 py-2"
            />
          </div>

          <!-- Number of Term -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Number of Term</label>
            <input
              type="number"
              v-model="official.no_of_per_term"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm"
            />
          </div>

          <!-- Elected Date -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Elected Date</label>
            <input
              type="date"
              v-model="official.elected_date"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm"
            />
          </div>

          <!-- Start Date -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">Start Date</label>
            <input
              type="date"
              v-model="official.start_date"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm"
            />
          </div>

          <!-- End Date -->
          <div class="col-span-4 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-600">End Date</label>
            <input
              type="date"
              v-model="official.end_date"
              class="border border-gray-300 rounded-md px-4 py-2 text-sm"
            />
          </div>

          <!-- Description -->
          <div class="col-span-12 flex flex-col gap-2 mt-4">
            <label class="text-sm font-semibold text-gray-600">Description</label>
            <textarea
              v-model="official.description"
              rows="4"
              class="resize-y border border-gray-200 rounded-md px-4 py-2"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-center mt-10 gap-4">
          <button
            type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-xl"
          >
            Save
          </button>
          <router-link
            to="/officials"
            class="bg-gray-200 px-6 py-2 rounded-xl font-bold hover:bg-gray-300"
          >
            Cancel
          </router-link>
        </div>
      </form>
    </template>
  </div>
</template>
