<script setup>
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const records = ref([
    { id: 1, name: 'Jose Rizal Jr.', age: '1 yr, 2 mos', last_assessed: '2025-08-05', status: 'Normal' },
    { id: 2, name: 'Andres Bonifacio Jr.', age: '8 mos', last_assessed: '2025-08-05', status: 'Underweight' },
    { id: 3, name: 'Emilio Aguinaldo II', age: '2 yrs, 5 mos', last_assessed: '2025-07-20', status: 'Normal' },
    { id: 4, name: 'Juan Dela Cruz', age: '45 yrs', last_assessed: '2025-08-01', status: 'Overweight' },
]);

const getStatusClass = (status) => {
    if (status === 'Normal') return 'bg-green-200 text-green-800';
    if (status === 'Underweight') return 'bg-yellow-200 text-yellow-800';
    if (status === 'Overweight' || status === 'Obese') return 'bg-red-200 text-red-800';
    return 'bg-gray-200 text-gray-800';
};
</script>

<template>
    <div class="space-y-4">
        <!-- Action Button -->
        <div class="flex justify-end">
            <button
                class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2">
                <PlusIcon class="w-5 h-5" />
                Log New Assessment
            </button>
        </div>

        <!-- Mobile Cards -->
        <div class="flex flex-col space-y-3 sm:hidden">
            <div v-for="rec in records" :key="rec.id" class="bg-white p-4 shadow-lg rounded-lg border">
                <p class="font-semibold text-gray-800">{{ rec.name }}</p>
                <p class="text-gray-600 text-sm">Age: {{ rec.age }}</p>
                <p class="text-gray-600 text-sm">Last Assessed: {{ rec.last_assessed }}</p>
                <p class="text-gray-600 text-sm">
                    Status:
                    <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(rec.status)">
                        {{ rec.status }}
                    </span>
                </p>
                <button class="mt-2 text-green-700 font-semibold hover:underline">View History</button>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden sm:block">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Resident Name</th>
                        <th class="p-3">Age</th>
                        <th class="p-3">Last Assessed</th>
                        <th class="p-3">Nutritional Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="rec in records" :key="rec.id" class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ rec.name }}</td>
                        <td class="p-3">{{ rec.age }}</td>
                        <td class="p-3">{{ rec.last_assessed }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(rec.status)">
                                {{ rec.status }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <button class="text-green-700 font-semibold hover:underline">View History</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
