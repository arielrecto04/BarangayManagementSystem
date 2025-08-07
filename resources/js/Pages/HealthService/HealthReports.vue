<script setup>
import { ref, computed } from 'vue';
import { DocumentChartBarIcon, PrinterIcon } from '@heroicons/vue/24/outline';

// --- Hardcoded data for visualization ---
const reportData = ref({
    consultations: {
        total: 134,
        trend: [30, 45, 25, 34], // Data points for a small chart
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
    <div class="bg-white p-6 shadow-lg rounded-lg">
        <div class="flex flex-col md:flex-row justify-between items-center border-b pb-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Health Statistics & Reports</h2>
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <select class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200">
                    <option>Last 30 Days</option>
                    <option>This Quarter</option>
                    <option>Year-to-Date</option>
                </select>
                <button
                    class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2">
                    <PrinterIcon class="w-5 h-5" />
                    Print / Export Report
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg border xl:col-span-2">
                <h3 class="text-lg font-bold text-gray-800">Clinic Visit Trends</h3>
                <p class="text-sm text-gray-500">Total Consultations: <span class="font-bold">{{
                    reportData.consultations.total }}</span></p>
                <div class="h-48 flex items-end justify-around p-4">
                    <div v-for="(val, index) in reportData.consultations.trend" :key="index"
                        class="w-1/5 bg-green-700 rounded-t-lg" :style="{ height: `${val * 2}%` }"></div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border">
                <h3 class="text-lg font-bold text-gray-800">Immunization Coverage</h3>
                <div class="flex items-center justify-center h-48">
                    <div class="relative w-36 h-36">
                        <svg class="w-full h-full" viewBox="0 0 36 36">
                            <path class="text-gray-200" stroke-width="4" fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-green-600" stroke-width="4" fill="none"
                                :stroke-dasharray="`${immunizationPercentage}, 100`"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-3xl font-bold">{{
                            immunizationPercentage }}%</div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border">
                <h3 class="text-lg font-bold text-gray-800">Top 5 Common Illnesses</h3>
                <ul class="space-y-3 mt-4">
                    <li v-for="illness in reportData.common_illnesses" :key="illness.name"
                        class="flex justify-between items-center">
                        <span class="text-gray-700">{{ illness.name }}</span>
                        <span class="font-bold text-lg">{{ illness.count }}</span>
                    </li>
                </ul>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border xl:col-span-2">
                <h3 class="text-lg font-bold text-gray-800">Maternal, Child & Nutrition Stats</h3>
                <div class="grid grid-cols-3 gap-4 mt-4 text-center">
                    <div>
                        <p class="text-4xl font-bold text-blue-600">{{ reportData.mch.live_births }}</p>
                        <p class="text-gray-500 font-semibold">Live Births Recorded</p>
                    </div>
                    <div>
                        <p class="text-4xl font-bold text-purple-600">{{ reportData.mch.active_prenatal }}</p>
                        <p class="text-gray-500 font-semibold">Active Prenatal Cases</p>
                    </div>
                    <div>
                        <p class="text-4xl font-bold text-red-600">{{ reportData.mch.malnourished_children }}</p>
                        <p class="text-gray-500 font-semibold">Malnourished Children</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- <style scoped>
.form-input {
    @apply rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200;
}

.action-button-green {
    @apply bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-800 flex items-center gap-2;
}

.report-widget {
    @apply bg-gray-50 p-4 rounded-lg border;
}

.widget-title {
    @apply text-lg font-bold text-gray-800;
}

.widget-subtitle {
    @apply text-sm text-gray-500;
}
</style> -->