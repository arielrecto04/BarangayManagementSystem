<script setup>
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

// --- Hardcoded data ---
const immunizations = ref([
    { id: 1, date: '2025-08-05', residentName: 'Jose Rizal Jr.', vaccine: 'BCG', dose: '1 of 1', administeredBy: 'BHW Maria' },
    { id: 2, date: '2025-08-04', residentName: 'Ana Santos', vaccine: 'COVID-19 Booster', dose: 'Booster', administeredBy: 'BHW Pedro' },
    { id: 3, date: '2025-08-02', residentName: 'Pedro Garcia', vaccine: 'Tetanus Toxoid', dose: '1 of 2', administeredBy: 'BHW Maria' },
    { id: 4, date: '2025-07-28', residentName: 'Maria Clara', vaccine: 'Hepatitis B', dose: '2 of 3', administeredBy: 'BHW Pedro' },
    { id: 5, date: '2025-07-25', residentName: 'Juan Dela Cruz', vaccine: 'Influenza', dose: 'Annual', administeredBy: 'BHW Maria' },
]);
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-4">
        <!-- Header & Controls -->
        <div
            class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-4 space-y-3 md:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Immunization Log</h2>
            <div
                class="flex flex-col md:flex-row items-start md:items-center w-full md:w-auto space-y-2 md:space-y-0 md:space-x-2">
                <input type="text" placeholder="Search by resident name..."
                    class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200 w-full md:w-48">
                <select class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200 w-full md:w-auto">
                    <option value="">Filter by Vaccine</option>
                    <option>BCG</option>
                    <option>Hepatitis B</option>
                    <option>COVID-19 Booster</option>
                </select>
                <button
                    class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 w-full md:w-auto justify-center">
                    <PlusIcon class="w-5 h-5" />
                    Log New Immunization
                </button>
            </div>
        </div>

        <!-- Records: stacked cards on mobile -->
        <div class="space-y-4">
            <div v-for="record in immunizations" :key="record.id"
                class="bg-gray-50 p-4 rounded-lg shadow hover:shadow-md flex flex-col sm:flex-row justify-between sm:items-center">
                <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-1 sm:space-y-0">
                    <p class="text-sm font-semibold text-gray-700">Date: <span class="font-normal">{{ record.date
                    }}</span></p>
                    <p class="text-sm font-semibold text-gray-700">Resident: <span class="font-normal">{{
                        record.residentName }}</span></p>
                    <p class="text-sm font-bold text-green-700">Vaccine: {{ record.vaccine }}</p>
                    <p class="text-sm font-semibold text-gray-700">Dose: <span class="font-normal">{{ record.dose
                    }}</span></p>
                    <p class="text-sm font-semibold text-gray-700">Administered By: <span class="font-normal">{{
                        record.administeredBy }}</span></p>
                </div>
                <div class="mt-2 sm:mt-0 flex justify-end">
                    <button class="text-green-700 font-semibold hover:underline text-sm">View History</button>
                </div>
            </div>
        </div>
    </div>
</template>
