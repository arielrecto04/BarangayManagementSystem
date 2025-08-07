<script setup>
import { AuthLayout } from "@/Layouts";
import { useBlotterStore } from "@/Stores";
import { storeToRefs } from "pinia";
import { onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import {
  FolderIcon,
  CheckCircleIcon,
  ChartPieIcon,
  ArrowPathIcon,
  PlusIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const blotterStore = useBlotterStore();
const { blotters } = storeToRefs(blotterStore);

// Navigate to list view with filter
const filterByStatus = async (status) => {
  await blotterStore.getBlotters(); // Refresh blotters
  router.push({ path: "/blotter/list-blotter", query: { status } });
};

onMounted(() => {
  blotterStore.getBlotters();
});

// Blotter statistics
const totalCases = computed(() => blotters.value.length);
const inProgress = computed(() => blotters.value.filter(b => b.status === 'In Progress').length);
const openCases = computed(() => blotters.value.filter(b => b.status === 'Open').length);
const resolved = computed(() => blotters.value.filter(b => b.status === 'Resolved').length);
</script>

<template>
  <AuthLayout>
    <div class="m-5">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Blotter & Crime Management</h1>
        <router-link 
          to="/blotter/add-blotter"
          class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          <span>New</span>
        </router-link>
      </div>

      <!-- Cards -->
      <div class="bg-gray-100 p-5 rounded-lg grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total Cases -->
        <div class="bg-white rounded-xl p-5 shadow flex flex-col gap-2">
          <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
            Total Cases
            <FolderIcon class="w-5 h-5 text-gray-500" />
          </div>
          <div class="text-3xl font-bold">{{ totalCases }}</div>
        </div>

        <!-- In Progress -->
        <div @click="filterByStatus('In Progress')"
          class="cursor-pointer bg-white rounded-xl p-5 shadow hover:bg-gray-50 transition">
          <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
            In Progress
            <ArrowPathIcon class="w-5 h-5 text-gray-500 animate-spin" />
          </div>
          <div class="text-3xl font-bold">{{ inProgress }}</div>
        </div>

        <!-- Open Cases -->
        <div @click="filterByStatus('Open')"
          class="cursor-pointer bg-white rounded-xl p-5 shadow hover:bg-gray-50 transition">
          <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
            Open Cases
            <ChartPieIcon class="w-5 h-5 text-gray-500" />
          </div>
          <div class="text-3xl font-bold">{{ openCases }}</div>
        </div>

        <!-- Resolved -->
        <div @click="filterByStatus('Resolved')"
          class="cursor-pointer bg-white rounded-xl p-5 shadow hover:bg-gray-50 transition">
          <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
            Resolved
            <CheckCircleIcon class="w-5 h-5 text-gray-500" />
          </div>
          <div class="text-3xl font-bold">{{ resolved }}</div>
        </div>
      </div>

      
      

      <!-- Nested ListBlotterView.vue goes here -->
      <router-view :key="$route.fullPath" class="mt-5"/>
    </div>
  </AuthLayout>
</template>