<script setup>
import { AuthLayout } from '@/Layouts'

// bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition
</script>

<template>
    <AuthLayout>
    <div class="flex justify-between items-center m-5">
        <h1 class="text-2xl font-semibold text-gray-800">Blotter & Crime Management</h1>
        <router-link to="/blotter/add-blotter" class="block">
            <div class="bg-black text-white px-7 py-3 rounded-md hover:bg-gray-800 transition">
                <div class="flex items-center gap-2 text-lg font-bold text-center text-white"><PlusIcon class="w-6 h-6 " />Add Blotter</div>
            </div>    
        </router-link>
  </div>
    </AuthLayout>
</template>
