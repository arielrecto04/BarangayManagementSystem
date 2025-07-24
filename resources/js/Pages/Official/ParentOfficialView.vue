<script setup>
import { AuthLayout } from "@/Layouts";
import { useRouter } from "vue-router";
import { PlusIcon, PencilIcon } from '@heroicons/vue/24/outline';
const router = useRouter();

// Data for Officials
const officials = [
  { name: 'ADAM S. APPLE', position: 'Barangay Captain', image: '/avatar1.svg' },
  { name: 'Marion Santos', position: 'Kagawad', image: '/avatar2.svg' },
  { name: 'Cynthia Lee', position: 'Secretary', image: '/avatar3.svg' },
  { name: 'Pedro Reyes', position: 'Treasurer', image: '/avatar4.svg' },
  { name: 'Mark Tanos', position: 'SK Kagawad', image: '/avatar5.svg' },
];
</script>

<template>
  <AuthLayout>
    <!-- Top Action Buttons -->
    <div class="m-3 flex flex-col">
      <div class="flex justify-end bg-white shadow-lg rounded-lg p-7 mt-0 transform -translate-y-10">
        <div class="flex gap-4">
          <router-link to="/officials/add-official" class="block">
            <div class="bg-green-700 shadow-md rounded-lg p-3 cursor-pointer text-white flex items-center gap-2 font-bold">
              <PlusIcon class="w-5 h-5" /> Add Official
            </div>
          </router-link>
          <router-link to="/officials/edit-official" class="block">
            <div class="shadow-md rounded-lg p-3 cursor-pointer flex items-center gap-2 font-bold">
              <PencilIcon class="w-5 h-5" /> Edit Official
            </div>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Org Chart -->
    <div class="flex flex-col items-center px-4 py-6 space-y-6 bg-gray-100">
      <!-- Top Official -->
      <div class="bg-white rounded-xl shadow-md px-6 py-4 w-64 text-center">
        <img :src="officials[0].image" alt="avatar" class="w-16 h-16 rounded-full mx-auto mb-2" />
        <h2 class="text-lg font-bold">{{ officials[0].name }}</h2>
        <p class="text-sm text-gray-600">{{ officials[0].position }}</p>
      </div>

      <!-- Second Row -->
      <div class="flex flex-wrap justify-center gap-6">
        <div
          v-for="(official, index) in officials.slice(1, 4)"
          :key="'mid-' + index"
          class="bg-white rounded-xl shadow-md px-6 py-4 w-60 text-center"
        >
          <img :src="official.image" alt="avatar" class="w-14 h-14 rounded-full mx-auto mb-2" />
          <h2 class="text-base font-bold">{{ official.name }}</h2>
          <p class="text-sm text-gray-600">{{ official.position }}</p>
        </div>
      </div>

      <!-- Bottom Row -->
      <div class="flex flex-wrap justify-center gap-6">
        <div
          v-for="(official, index) in officials.slice(4)"
          :key="'bot-' + index"
          class="bg-white rounded-xl shadow-md px-6 py-4 w-60 text-center"
        >
          <img :src="official.image" alt="avatar" class="w-14 h-14 rounded-full mx-auto mb-2" />
          <h2 class="text-base font-bold">{{ official.name }}</h2>
          <p class="text-sm text-gray-600">{{ official.position }}</p>
        </div>
      </div>

      <!-- Bottom Action Buttons -->
      <div class="flex gap-4 mt-6">
        <button class="flex items-center gap-2 px-4 py-2 bg-gray-300 rounded shadow">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
          </svg>
          Expand All
        </button>
        <button class="flex items-center gap-2 px-4 py-2 bg-gray-300 rounded shadow">
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M4 3a1 1 0 00-1 1v2h2V5h2v2h2V5h2v2h2V5h2v1h2V4a1 1 0 00-1-1H4zM3 9a1 1 0 011-1h12a1 1 0 011 1v7a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
          </svg>
          Adjust Card
        </button>
        <button class="flex items-center gap-2 px-4 py-2 bg-gray-300 rounded shadow">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Export Chart
        </button>
      </div>
    </div>

    <router-view></router-view>
  </AuthLayout>
</template>
