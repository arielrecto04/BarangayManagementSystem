<script setup>
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const participants = ref([
    { id: 1, name: 'Isabel Garcia', age: 28, method: 'Contraceptive Pills', last_visit: '2025-07-15' },
    { id: 2, name: 'Teresa Reyes', age: 34, method: 'IUD (Intrauterine Device)', last_visit: '2025-06-20' },
    { id: 3, name: 'Gabriela Bonifacio', age: 25, method: 'Implant', last_visit: '2025-08-01' },
]);
</script>

<template>
    <div>
        <div class="flex justify-end mb-4">
            <button
                class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2">
                <PlusIcon class="w-5 h-5" />
                Enroll Resident
            </button>
        </div>
        <table class="w-full text-left">
            <thead class="text-sm text-gray-600 uppercase">
                <tr>
                    <th class="p-3">Participant Name</th>
                    <th class="p-3">Age</th>
                    <th class="p-3">Method</th>
                    <th class="p-3">Last Visit / Supply Date</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="p in participants" :key="p.id" class="border-t hover:bg-gray-50">
                    <td class="p-3 font-semibold">{{ p.name }}</td>
                    <td class="p-3">{{ p.age }}</td>
                    <td class="p-3">{{ p.method }}</td>
                    <td class="p-3">{{ p.last_visit }}</td>
                    <td class="p-3 text-right">
                        <button class="text-green-700 font-semibold hover:underline">View Details</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<!-- <style scoped>
.action-button-green {
    @apply bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2;
}
</style> -->