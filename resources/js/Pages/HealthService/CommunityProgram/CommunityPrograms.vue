<script setup>
</script>

<template>
    <div class="bg-white p-6 shadow-lg rounded-lg">
        <div class="flex items-center border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Community Programs</h2>
            <div class="ml-6 flex items-center space-x-1 bg-gray-100 p-1 rounded-lg">
                <router-link :to="{ name: 'Family Planning' }"
                    class="px-4 py-1.5 font-semibold text-gray-600 rounded-md transition-colors"
                    active-class="bg-white text-green-800 shadow-sm">
                    Family Planning
                </router-link>
                <router-link :to="{ name: 'Nutrition Status' }"
                    class="px-4 py-1.5 font-semibold text-gray-600 rounded-md transition-colors"
                    active-class="bg-white text-green-800 shadow-sm">
                    Nutrition Status
                </router-link>
            </div>
        </div>

        <router-view></router-view>
    </div>
</template>

<!-- <style scoped>
.sub-tab-link {
    @apply px-4 py-1.5 font-semibold text-gray-600 rounded-md transition-colors;
}
.active-sub-tab {
    @apply bg-white text-green-700 shadow-sm;
}
</style> -->