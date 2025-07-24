<script setup>
    import { AuthLayout } from '@/Layouts'
  
</script>

<template>
    <AuthLayout>
  
    <div class='mt-5'>
        <h1 class='text-2xl uppercase'>Dashboard Overview</h1>
        <!-- USE MESSAGE -->
        <h2 class='mt-2'> Welcome back, Admin!</h2> 
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 p-4">
        <!-- Card 1 -->
        <div class="flex items-center bg-white rounded-lg shadow-md p-5 space-x-4">
            <div class="text-3xl text-green-700">
                <i class="fas fa-users"></i> 

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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-5">
    <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-md p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Recent Activities:</h2>
                <a href="{{  }}" class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
        </div>

    <!-- Latest Projects -->
        <div class="bg-white rounded-lg shadow-md p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Latest Projects</h2>
                <a href="{{  }}" class="text-sm text-blue-600 hover:underline">View All</a>
    </div>
        </div>
    </div>
    
    
   
    </AuthLayout>
</template>
