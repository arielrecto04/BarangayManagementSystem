<script setup>
import { ref, computed } from 'vue';
import { DocumentChartBarIcon, PrinterIcon } from '@heroicons/vue/24/outline';

// --- Hardcoded data for visualization ---
const reportData = ref({
    consultations: {
        total: 134,
        trend: [30, 45, 25, 34],
    },
    common_illnesses: [
        { name: 'Common Cold / URI', count: 45 },
        { name: 'Hypertension', count: 28 },
        { name: 'Gastritis', count: 18 },
        { name: 'Minor Wounds', count: 15 },
        { name: 'Fever', count: 12 },
    ],
    immunization: {
        target: 250,
        completed: 220,
    },
    mch: {
        live_births: 5,
        active_prenatal: 12,
        malnourished_children: 8,
    }
});

const immunizationPercentage = computed(() => {
    const { target, completed } = reportData.value.immunization;
    return target > 0 ? Math.round((completed / target) * 100) : 0;
});
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-6">
        <!-- Header & Controls -->
        <div
            class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-4 mb-6 space-y-3 md:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Health Statistics & Reports</h2>
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 w-full md:w-auto">
                <select class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200 w-full sm:w-auto">
                    <option>Last 30 Days</option>
                    <option>This Quarter</option>
                    <option>Year-to-Date</option>
                </select>
                <button
                    class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 w-full sm:w-auto justify-center">
                    <PrinterIcon class="w-5 h-5" />
                    Print / Export Report
                </button>
            </div>
        </div>

        <!-- Reports Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
            <!-- Clinic Visit Trends -->
            <div class="bg-gray-50 p-4 rounded-lg border xl:col-span-2">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Clinic Visit Trends</h3>
                <p class="text-sm text-gray-500 mb-2">Total Consultations: <span class="font-bold">{{
                    reportData.consultations.total }}</span></p>
                <div class="flex items-end justify-around p-2 h-40 sm:h-48">
                    <div v-for="(val, index) in reportData.consultations.trend" :key="index"
                        class="w-1/5 bg-green-700 rounded-t-lg" :style="{ height: `${val * 2}%` }"></div>
                </div>
            </div>

            <!-- Immunization Coverage -->
            <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-center h-40 sm:h-48">
                <div class="relative w-28 h-28 sm:w-36 sm:h-36">
                    <svg class="w-full h-full" viewBox="0 0 36 36">
                        <path class="text-gray-200" stroke-width="4" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-green-600" stroke-width="4" fill="none"
                            :stroke-dasharray="`${immunizationPercentage}, 100`"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center text-2xl sm:text-3xl font-bold">{{
                        immunizationPercentage }}%</div>
                </div>
            </div>

            <!-- Top 5 Common Illnesses -->
            <div class="bg-gray-50 p-4 rounded-lg border">
                <h3 class="text-lg font-bold text-gray-800">Top 5 Common Illnesses</h3>
                <ul class="space-y-2 mt-3">
                    <li v-for="illness in reportData.common_illnesses" :key="illness.name"
                        class="flex justify-between items-center">
                        <span class="text-gray-700 text-sm sm:text-base">{{ illness.name }}</span>
                        <span class="font-bold text-base sm:text-lg">{{ illness.count }}</span>
                    </li>
                </ul>
            </div>

            <!-- Maternal, Child & Nutrition Stats -->
            <div class="bg-gray-50 p-4 rounded-lg border xl:col-span-2">
                <h3 class="text-lg font-bold text-gray-800">Maternal, Child & Nutrition Stats</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 text-center">
                    <div>
                        <p class="text-3xl sm:text-4xl font-bold text-blue-600">{{ reportData.mch.live_births }}</p>
                        <p class="text-gray-500 font-semibold text-sm sm:text-base">Live Births Recorded</p>
                    </div>
                    <div>
                        <p class="text-3xl sm:text-4xl font-bold text-purple-600">{{ reportData.mch.active_prenatal }}
                        </p>
                        <p class="text-gray-500 font-semibold text-sm sm:text-base">Active Prenatal Cases</p>
                    </div>
                    <div>
                        <p class="text-3xl sm:text-4xl font-bold text-red-600">{{ reportData.mch.malnourished_children
                        }}</p>
                        <p class="text-gray-500 font-semibold text-sm sm:text-base">Malnourished Children</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
