<script setup>
import { ref } from 'vue';
// --- Hardcoded data for the dashboard is now part of this component ---
const stats = ref({
    lowStockItems: 4,
    profilesCreated: 128,
    consultationsLastMonth: 82, // Data for July 2025
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
    <div class="mx-3 -mt-10 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-5 shadow-lg rounded-lg">
                <div class="text-3xl font-bold text-red-600">{{ stats.lowStockItems }}</div>
                <div class="text-gray-500 font-semibold">Medicines Low on Stock</div>
            </div>
            <div class="bg-white p-5 shadow-lg rounded-lg">
                <div class="text-3xl font-bold text-blue-600">{{ stats.profilesCreated }}</div>
                <div class="text-gray-500 font-semibold">Resident Health Profiles</div>
            </div>
            <div class="bg-white p-5 shadow-lg rounded-lg">
                <div class="text-3xl font-bold text-green-600">{{ stats.consultationsLastMonth }}</div>
                <div class="text-gray-500 font-semibold">Consultations (July)</div>
            </div>
            <div class="bg-white p-5 shadow-lg rounded-lg">
                <div class="text-3xl font-bold text-purple-600">{{ stats.immunizationRate }}</div>
                <div class="text-gray-500 font-semibold">Immunization Coverage</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-5 shadow-lg rounded-lg">
                <h3 class="text-xl font-bold border-b pb-2 mb-4">Medicines to Re-stock</h3>
                <ul>
                    <li v-for="med in lowStockMedicines" :key="med.id"
                        class="flex justify-between items-center py-2 border-b last:border-0">
                        <span class="font-semibold">{{ med.name }}</span>
                        <span class="text-sm font-bold text-red-600">Stock: {{ med.stock }}</span>
                    </li>
                </ul>
            </div>
            <div class="bg-white p-5 shadow-lg rounded-lg">
                <h3 class="text-xl font-bold border-b pb-2 mb-4">Recent Activity</h3>
                <ul>
                    <li v-for="activity in recentActivities" :key="activity.id"
                        class="py-2 text-gray-700 border-b last:border-0">
                        {{ activity.text }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>