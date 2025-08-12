<script setup>
import { AuthLayout } from "@/Layouts";
import { useRouter } from "vue-router";
import { PlusIcon, PencilIcon } from '@heroicons/vue/24/outline';
const router = useRouter();


</script>

<template>
  <AuthLayout>
    <!-- Official Info Card -->
    <div class="bg-white p-6 shadow-md rounded-lg w-full max-w-xs">
      <h1 class="uppercase font-bold text-xl mb-1">Official Manager</h1>
      <p class="text-gray-600">Manage and View Official Information</p>
    </div>

    <div class="m-4 flex flex-col items-end max-w-xs">
      <router-link to="/officials/add-official" class="block w-full">
        <button
          class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg shadow transition-colors flex items-center justify-center gap-2">
          <PlusIcon class="w-5 h-5" />
          Add Official
        </button>
      </router-link>
    </div>

    <router-view></router-view>
  </AuthLayout>
</template>
