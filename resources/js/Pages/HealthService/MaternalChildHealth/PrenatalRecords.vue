<script setup>
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const expectantMothers = ref([
    { id: 4, name: 'Clara Santos', aog: '28 weeks', edd: '2025-11-05', last_checkup: '2025-07-30' },
    { id: 12, name: 'Isabel Garcia', aog: '16 weeks', edd: '2026-02-14', last_checkup: '2025-08-01' },
    { id: 25, name: 'Teresa Reyes', aog: '34 weeks', edd: '2025-09-20', last_checkup: '2025-08-04' },
]);
</script>

<template>
    <div>
        <div class="flex justify-end mb-4">
            <button
                class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2">
                <PlusIcon class="w-5 h-5" />
                Log New Prenatal Record
            </button>
        </div>
        <table class="w-full text-left">
            <thead class="text-sm text-gray-600 uppercase">
                <tr>
                    <th class="p-3">Resident Name</th>
                    <th class="p-3">Age of Gestation (AOG)</th>
                    <th class="p-3">Est. Due Date (EDD)</th>
                    <th class="p-3">Last Checkup</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="mother in expectantMothers" :key="mother.id" class="border-t hover:bg-gray-50">
                    <td class="p-3 font-semibold">{{ mother.name }}</td>
                    <td class="p-3">{{ mother.aog }}</td>
                    <td class="p-3">{{ mother.edd }}</td>
                    <td class="p-3">{{ mother.last_checkup }}</td>
                    <td class="p-3 text-right">
                        <button class="text-green-700 font-semibold hover:underline">View Full Chart</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>