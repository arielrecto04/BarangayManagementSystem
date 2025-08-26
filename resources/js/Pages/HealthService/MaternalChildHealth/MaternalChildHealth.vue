<script setup>
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 sm:mb-0">Maternal & Child Health</h2>

            <!-- Sub-tabs -->
            <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                <router-link :to="{ name: 'Prenatal Records' }"
                    class="px-4 py-2 font-semibold text-gray-600 rounded-md text-center sm:text-left transition-colors w-full sm:w-auto"
                    active-class="bg-white text-green-800 shadow-sm">
                    Prenatal
                </router-link>
                <router-link :to="{ name: 'Child Growth Records' }"
                    class="px-4 py-2 font-semibold text-gray-600 rounded-md text-center sm:text-left transition-colors w-full sm:w-auto"
                    active-class="bg-white text-green-800 shadow-sm">
                    Child Growth
                </router-link>
            </div>
        </div>

        <!-- Content -->
        <router-view></router-view>
    </div>
</template>
