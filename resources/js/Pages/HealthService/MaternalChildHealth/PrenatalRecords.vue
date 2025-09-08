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
    <div class="space-y-4">
        <!-- Add Record Button -->
        <div class="flex justify-end">
            <button
                class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 w-full sm:w-auto justify-center">
                <PlusIcon class="w-5 h-5" />
                Log New Prenatal Record
            </button>
        </div>

        <!-- Records: stacked cards on mobile -->
        <div class="space-y-4">
            <div v-for="mother in expectantMothers" :key="mother.id"
                class="bg-gray-50 p-4 rounded-lg shadow hover:shadow-md flex flex-col sm:flex-row justify-between sm:items-center">
                <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-1 sm:space-y-0">
                    <p class="text-sm font-semibold text-gray-700">Name: <span class="font-normal">{{ mother.name
                    }}</span></p>
                    <p class="text-sm font-semibold text-gray-700">AOG: <span class="font-normal">{{ mother.aog
                    }}</span></p>
                    <p class="text-sm font-semibold text-gray-700">EDD: <span class="font-normal">{{ mother.edd
                    }}</span></p>
                    <p class="text-sm font-semibold text-gray-700">Last Checkup: <span class="font-normal">{{
                        mother.last_checkup }}</span></p>
                </div>
                <div class="mt-2 sm:mt-0 flex justify-end">
                    <button class="text-green-700 font-semibold hover:underline text-sm sm:text-base">View Full
                        Chart</button>
                </div>
            </div>
        </div>
    </div>
</template>
