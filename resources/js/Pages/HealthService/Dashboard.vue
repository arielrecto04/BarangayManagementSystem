<script setup>
import { ref } from 'vue';

// --- Hardcoded data for the dashboard ---
const stats = ref({
    lowStockItems: 4,
    profilesCreated: 128,
    consultationsLastMonth: 82,
    immunizationRate: '88%',
});

const lowStockMedicines = ref([
    { id: 1, name: 'Paracetamol 500mg', stock: 8 },
    { id: 2, name: 'Amoxicillin 250mg', stock: 5 },
    { id: 3, name: 'Lagundi Syrup', stock: 3 },
]);

const recentActivities = ref([
    { id: 1, text: 'New Health Profile created for Maria Clara.' },
    { id: 2, text: 'Logged clinic visit for Jose Rizal (Fever).' },
    { id: 3, text: 'Recorded COVID-19 booster for Ana Santos.' },
]);
</script>

<template>
    <div class="mx-3 mt-4 space-y-6">
        <!-- Stats Cards: 2 columns, 2 rows -->
        <div class="grid grid-cols-2 gap-4 sm:gap-6">
            <div class="bg-white p-4 sm:p-5 shadow-lg rounded-lg flex flex-col items-start">
                <div class="text-2xl sm:text-3xl font-bold text-red-600">{{ stats.lowStockItems }}</div>
                <div class="text-gray-500 text-sm sm:text-base font-semibold mt-1">Medicines Low on Stock</div>
            </div>
            <div class="bg-white p-4 sm:p-5 shadow-lg rounded-lg flex flex-col items-start">
                <div class="text-2xl sm:text-3xl font-bold text-blue-600">{{ stats.profilesCreated }}</div>
                <div class="text-gray-500 text-sm sm:text-base font-semibold mt-1">Resident Health Profiles</div>
            </div>
            <div class="bg-white p-4 sm:p-5 shadow-lg rounded-lg flex flex-col items-start">
                <div class="text-2xl sm:text-3xl font-bold text-green-600">{{ stats.consultationsLastMonth }}</div>
                <div class="text-gray-500 text-sm sm:text-base font-semibold mt-1">Consultations (July)</div>
            </div>
            <div class="bg-white p-4 sm:p-5 shadow-lg rounded-lg flex flex-col items-start">
                <div class="text-2xl sm:text-3xl font-bold text-purple-600">{{ stats.immunizationRate }}</div>
                <div class="text-gray-500 text-sm sm:text-base font-semibold mt-1">Immunization Coverage</div>
            </div>
        </div>

        <!-- Medicines and Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Low Stock Medicines -->
            <div class="bg-white p-4 sm:p-5 shadow-lg rounded-lg">
                <h3 class="text-lg sm:text-xl font-bold border-b pb-2 mb-4">Medicines to Re-stock</h3>
                <ul>
                    <li v-for="med in lowStockMedicines" :key="med.id"
                        class="flex justify-between items-center py-2 border-b last:border-0">
                        <span class="font-semibold text-sm sm:text-base">{{ med.name }}</span>
                        <span class="text-sm font-bold text-red-600">Stock: {{ med.stock }}</span>
                    </li>
                </ul>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white p-4 sm:p-5 shadow-lg rounded-lg">
                <h3 class="text-lg sm:text-xl font-bold border-b pb-2 mb-4">Recent Activity</h3>
                <ul>
                    <li v-for="activity in recentActivities" :key="activity.id"
                        class="py-2 text-gray-700 text-sm sm:text-base border-b last:border-0">
                        {{ activity.text }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
