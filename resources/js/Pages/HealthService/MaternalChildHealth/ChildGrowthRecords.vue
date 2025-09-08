<script setup>
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const children = ref([
    { id: 1, name: 'Jose Rizal Jr.', age: '1 yr, 2 mos', parent: 'Maria Rizal', last_weight: '9.5 kg', last_height: '78 cm' },
    { id: 2, name: 'Andres Bonifacio Jr.', age: '8 mos', parent: 'Gabriela Bonifacio', last_weight: '8.1 kg', last_height: '70 cm' },
    { id: 3, name: 'Emilio Aguinaldo II', age: '2 yrs, 5 mos', parent: 'Marcela Aguinaldo', last_weight: '12.5 kg', last_height: '88 cm' },
]);
</script>

<template>
    <div class="space-y-4">
        <!-- Add Measurement Button -->
        <div class="flex justify-end">
            <button
                class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 w-full sm:w-auto justify-center">
                <PlusIcon class="w-5 h-5" />
                Log New Measurement
            </button>
        </div>

        <!-- Records: stacked cards on mobile -->
        <div class="space-y-4">
            <div v-for="child in children" :key="child.id"
                class="bg-gray-50 p-4 rounded-lg shadow hover:shadow-md flex flex-col sm:flex-row justify-between sm:items-center">
                <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-1 sm:space-y-0">
                    <p class="text-sm font-semibold text-gray-700">Name: <span class="font-normal">{{ child.name
                    }}</span></p>
                    <p class="text-sm font-semibold text-gray-700">Age: <span class="font-normal">{{ child.age }}</span>
                    </p>
                    <p class="text-sm font-semibold text-gray-700">Parent: <span class="font-normal">{{ child.parent
                    }}</span></p>
                    <p class="text-sm font-semibold text-gray-700">Weight: <span class="font-normal">{{
                        child.last_weight }}</span></p>
                    <p class="text-sm font-semibold text-gray-700">Height: <span class="font-normal">{{
                        child.last_height }}</span></p>
                </div>
                <div class="mt-2 sm:mt-0 flex justify-end">
                    <button class="text-green-700 font-semibold hover:underline text-sm sm:text-base">View Growth
                        Chart</button>
                </div>
            </div>
        </div>
    </div>
</template>
