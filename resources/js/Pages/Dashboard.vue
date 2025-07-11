<script setup>
    import { AuthLayout } from '@/Layouts'
    import { Header} from '@/Components'
</script>

<template>
    <AuthLayout>
    <Header>
    </Header>
    <div class='mt-5'>
        <h1 class='text-2xl uppercase'>Dashboard Overview</h1>
        <h2 class='mt-2'> Welcome back, Admin!</h2> 
    </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 p-4">
    <!-- Card 1 -->
    <div class="flex items-center bg-white rounded-lg shadow-md p-5 space-x-4">
        <div class="text-3xl text-green-700">
            <i class="fas fa-users"></i> <!-- Replace with your own SVG or icon -->
        </div>
        <div>
            <p class="text-sm text-gray-600">Total Residents</p>
            <p class="text-xl font-semibold">{{}}</p>
        </div>
        </div>
        <!-- Card 2 -->
    <div class="flex items-center bg-white rounded-lg shadow-md p-4 space-x-4">
        <div class="text-3xl text-blue-600">
            <i class="fas fa-file-alt"></i>
        </div>
        <div>
            <p class="text-sm text-gray-600">Documents Processed</p>
            <p class="text-xl font-semibold">{{}}</p>
        </div>
        </div>
        <!-- Card 3 -->
    <div class="flex items-center bg-white rounded-lg shadow-md p-4 space-x-4">
        <div class="text-3xl text-red-600">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <p class="text-sm text-gray-600">Active Complaints</p>
            <p class="text-xl font-semibold">{{  }}</p>
        </div>
        </div>
        <!-- Card 4 -->
    <div class="flex items-center bg-white rounded-lg shadow-md p-4 space-x-4">
        <div class="text-3xl text-purple-600">
            <i class="fas fa-project-diagram"></i>
        </div>
        <div>
            <p class="text-sm text-gray-600">Ongoing Projects</p>
            <p class="text-xl font-semibold">{{  }}</p>
        </div>
        </div>
    </div>
    
    
   
    </AuthLayout>
</template>
