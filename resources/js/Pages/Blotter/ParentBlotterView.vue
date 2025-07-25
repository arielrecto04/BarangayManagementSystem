<script setup>
import { AuthLayout } from '@/Layouts'

// bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition
</script>

<template>
    <AuthLayout>
    <div class="flex justify-between items-center m-5">
        <h1 class="text-2xl font-semibold text-gray-800">Blotter & Crime Management</h1>
        <div class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition">
            <router-link to="/blotter/add-blotter" class="block">
            
            </router-link>
        </div>    
  </div>
    </AuthLayout>
</template>
