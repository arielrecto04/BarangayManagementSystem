<script setup>
import { AuthLayout } from "@/Layouts";
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const totalCases = ref(0);
const openCases = ref(0);
const inProgressCases = ref(0);
const resolvedCases = ref(0);

onMounted(() => {
  // Placeholder: Fetch data from the database and update the counts
  // Example:
  // axios.get('/api/blotter-stats').then(response => {
  //   totalCases.value = response.data.totalCases;
  //   openCases.value = response.data.openCases;
  //   inProgressCases.value = response.data.inProgressCases;
  //   resolvedCases.value = response.data.resolvedCases;
  // });
});

const navigateToNewBlotter = () => {
  router.push("/blotter/new");
};
</script>

<template>
  <AuthLayout>
    <div class="m-3 flex flex-col relative">
      <!-- Blotter & Crime Management Header -->
      <div class="bg-gray-100 p-5 shadow-lg rounded-lg">
        <h1 class="text-xl font-bold">Blotter & Crime Management</h1>
      </div>

      <!-- "New" Button -->
      <button
        @click="navigateToNewBlotter"
        class="absolute top-5 right-5 bg-black text-white px-4 py-2 rounded-lg shadow-md hover:bg-gray-800"
      >
        + New
      </button>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mt-5">
        <!-- Total Cases -->
        <div class="bg-white shadow-md rounded-lg p-5 flex items-center gap-4">
          <div class="text-3xl font-bold">{{ totalCases }}</div>
          <div class="flex items-center gap-2">
            <span class="text-sm font-semibold">Total Cases</span>
            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Open Cases -->
        <div class="bg-white shadow-md rounded-lg p-5 flex items-center gap-4">
          <div class="text-3xl font-bold">{{ openCases }}</div>
          <div class="flex items-center gap-2">
            <span class="text-sm font-semibold">Open Cases</span>
            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
              </svg>
            </div>
          </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white shadow-md rounded-lg p-5 flex items-center gap-4">
          <div class="text-3xl font-bold">{{ inProgressCases }}</div>
          <div class="flex items-center gap-2">
            <span class="text-sm font-semibold">In Progress</span>
            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Resolved -->
        <div class="bg-white shadow-md rounded-lg p-5 flex items-center gap-4">
          <div class="text-3xl font-bold">{{ resolvedCases }}</div>
          <div class="flex items-center gap-2">
            <span class="text-sm font-semibold">Resolved</span>
            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex items-center gap-4 mt-5">
        <!-- Search Bar -->
        <input
          type="text"
          placeholder="Search complaints..."
          class="w-full sm:w-1/2 rounded-lg border border-gray-300 px-4 py-2 shadow-md"
        />

        <!-- Category Filter -->
        <select class="rounded-lg border border-gray-300 px-4 py-2 shadow-md">
          <option value="" disabled selected hidden>All Categories</option>
          <option>Category 1</option>
          <option>Category 2</option>
          <option>Category 3</option>
        </select>

        <!-- Status Filter -->
        <select class="rounded-lg border border-gray-300 px-4 py-2 shadow-md">
          <option value="" disabled selected hidden>All Status</option>
          <option>Open</option>
          <option>In Progress</option>
          <option>Resolved</option>
        </select>

        <!-- More Filters -->
        <button class="bg-gray-200 rounded-lg px-4 py-2 shadow-md flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 6h18" />
          </svg>
          More Filters
        </button>
      </div>
    </div>
  </AuthLayout>
</template>