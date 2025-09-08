<script setup>
import { AuthLayout } from "@/Layouts";
import { useRouter } from "vue-router";
import { PlusIcon, PencilIcon } from '@heroicons/vue/24/outline';
const router = useRouter();


</script>

<template>
  <AuthLayout>
    <div class="p-2 sm:p-5 lg:p-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-6 gap-2 sm:gap-0">
        <div>
          <h1 class="text-base sm:text-xl lg:text-2xl font-semibold text-gray-900">
            Official Management
          </h1>
          <p class="text-gray-600">
            Manage and View Official Information
          </p>
        </div>
        <router-link to="/officials/add-official"
          class="bg-green-700 text-white px-3 py-1.5 sm:py-2 rounded-md hover:bg-green-800 transition-colors flex items-center justify-center gap-1.5 text-xs sm:text-sm font-medium">
          <PlusIcon class="w-3 h-3 sm:w-4 sm:h-4" />
          <span>New Official</span>
        </router-link>
      </div>

      <!-- Nested List Official View -->
      <router-view />
    </div>
  </AuthLayout>
</template>
