<script setup>
import { AuthLayout } from "@/Layouts";
import { useRouter } from "vue-router";
import { PlusIcon, PencilIcon } from '@heroicons/vue/24/outline';
const router = useRouter();


</script>

<template>
  <AuthLayout>
    <!-- Header Row -->
    <div class="m-4 flex items-center justify-between bg-white p-6 shadow-md rounded-lg">
      <!-- Official Manager Info (LEFT) -->
      <div>
        <h1 class="uppercase font-bold text-xl mb-1">Official Manager</h1>
        <p class="text-gray-600">Manage and View Official Information</p>
      </div>

      <!-- Add Official Button (RIGHT) -->
      <div>
        <router-link to="/officials/add-official">
          <button
            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-5 rounded-lg shadow transition-colors flex items-center gap-2">
            <PlusIcon class="w-6 h-6" />
            Add Official
          </button>
        </router-link>
      </div>
    </div>

    <!-- Page Content -->
    <router-view></router-view>
  </AuthLayout>
</template>