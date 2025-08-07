<script setup>
import { ref } from 'vue';
import { PlusIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

// --- Hardcoded data for visualization ---
const immunizations = ref([
    { id: 1, date: '2025-08-05', residentName: 'Jose Rizal Jr.', vaccine: 'BCG', dose: '1 of 1', administeredBy: 'BHW Maria' },
    { id: 2, date: '2025-08-04', residentName: 'Ana Santos', vaccine: 'COVID-19 Booster', dose: 'Booster', administeredBy: 'BHW Pedro' },
    { id: 3, date: '2025-08-02', residentName: 'Pedro Garcia', vaccine: 'Tetanus Toxoid', dose: '1 of 2', administeredBy: 'BHW Maria' },
    { id: 4, date: '2025-07-28', residentName: 'Maria Clara', vaccine: 'Hepatitis B', dose: '2 of 3', administeredBy: 'BHW Pedro' },
    { id: 5, date: '2025-07-25', residentName: 'Juan Dela Cruz', vaccine: 'Influenza', dose: 'Annual', administeredBy: 'BHW Maria' },
]);
</script>

<template>
    <div class="bg-white p-6 shadow-lg rounded-lg">
        <div class="flex flex-col md:flex-row justify-between items-center border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Immunization Log</h2>
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <input type="text" placeholder="Search by resident name..."
                    class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200 w-48">
                <select class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200">
                    <option value="">Filter by Vaccine</option>
                    <option>BCG</option>
                    <option>Hepatitis B</option>
                    <option>COVID-19 Booster</option>
                </select>
                <button
                    class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2">
                    <PlusIcon class="w-5 h-5" />
                    Log New Immunization
                </button>
            </div>
        </div>

        <table class="w-full text-left">
            <thead class="text-sm text-gray-600 uppercase">
                <tr>
                    <th class="p-3">Date Administered</th>
                    <th class="p-3">Resident Name</th>
                    <th class="p-3">Vaccine</th>
                    <th class="p-3">Dose</th>
                    <th class="p-3">Administered By</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="record in immunizations" :key="record.id" class="border-t hover:bg-gray-50">
                    <td class="p-3 font-semibold">{{ record.date }}</td>
                    <td class="p-3">{{ record.residentName }}</td>
                    <td class="p-3 font-bold">{{ record.vaccine }}</td>
                    <td class="p-3">{{ record.dose }}</td>
                    <td class="p-3">{{ record.administeredBy }}</td>
                    <td class="p-3 text-right">
                        <button class="text-green-700 font-semibold hover:underline">
                            View History
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<!-- <style scoped>
.form-input {
    @apply rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200;
}

.action-button-green {
    @apply bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2;
}
</style> -->