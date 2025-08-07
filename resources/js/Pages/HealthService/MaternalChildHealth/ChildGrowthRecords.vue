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
    <div>
        <div class="flex justify-end mb-4">
            <button
                class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2">
                <PlusIcon class="w-5 h-5" />
                Log New Measurement
            </button>
        </div>
        <table class="w-full text-left">
            <thead class="text-sm text-gray-600 uppercase">
                <tr>
                    <th class="p-3">Child's Name</th>
                    <th class="p-3">Age</th>
                    <th class="p-3">Parent/Guardian</th>
                    <th class="p-3">Last Weight</th>
                    <th class="p-3">Last Height</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="child in children" :key="child.id" class="border-t hover:bg-gray-50">
                    <td class="p-3 font-semibold">{{ child.name }}</td>
                    <td class="p-3">{{ child.age }}</td>
                    <td class="p-3">{{ child.parent }}</td>
                    <td class="p-3">{{ child.last_weight }}</td>
                    <td class="p-3">{{ child.last_height }}</td>
                    <td class="p-3 text-right">
                        <button class="text-green-700 font-semibold hover:underline">View Growth Chart</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<!-- 
<style scoped>
.action-button-green {
    @apply bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2;
}
</style> -->