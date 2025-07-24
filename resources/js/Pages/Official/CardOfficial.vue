<!-- src/Pages/Official/CardOfficial.vue -->
<script setup>
import { PlusIcon, PencilIcon } from '@heroicons/vue/24/outline';

// Static officials data
const officials = [
  { name: 'ADAM S. APPLE', position: 'Barangay Captain', image: '/avatar1.svg' },
  { name: 'Marion Santos', position: 'Kagawad', image: '/avatar2.svg' },
  { name: 'Cynthia Lee', position: 'Secretary', image: '/avatar3.svg' },
  { name: 'Pedro Reyes', position: 'Treasurer', image: '/avatar4.svg' },
  { name: 'Mark Tanos', position: 'SK Kagawad', image: '/avatar5.svg' },
];
</script>

<template>
  <!-- Org Chart -->
  <div class="flex flex-col items-center px-4 py-6 space-y-6 bg-gray-100">
    <!-- Top Official -->
    <div class="bg-white rounded-xl shadow-md px-6 py-4 w-64 text-center" v-if="officials.length">
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
  </div>
</template>
