<script setup>
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 sm:mb-0">Community Programs</h2>

            <!-- Sub-tabs -->
            <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0 w-full sm:w-auto">
                <router-link :to="{ name: 'Family Planning' }"
                    class="px-4 py-2 font-semibold text-gray-600 rounded-md text-center sm:text-left w-full sm:w-auto transition-colors"
                    active-class="bg-white text-green-800 shadow-sm">
                    Family Planning
                </router-link>
                <router-link :to="{ name: 'Nutrition Status' }"
                    class="px-4 py-2 font-semibold text-gray-600 rounded-md text-center sm:text-left w-full sm:w-auto transition-colors"
                    active-class="bg-white text-green-800 shadow-sm">
                    Nutrition Status
                </router-link>
            </div>
        </div>

        <!-- Content -->
        <router-view></router-view>
    </div>
</template>
