<script setup>
import { AuthLayout } from "@/Layouts";
import { useRouter } from "vue-router";
import { PlusIcon, PencilIcon } from '@heroicons/vue/24/outline';
const router = useRouter();


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

    <router-view></router-view>
  </AuthLayout>
</template>
