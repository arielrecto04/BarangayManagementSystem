<!-- Doctors/DoctorList.vue -->
<script setup>
import { ref } from 'vue';
import { PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const doctors = ref([
    { id: 1, name: 'Dr. Maria Santos', specialty: 'General Medicine', availability: 'Mon-Wed, Fri', contact: '09123456789' },
    { id: 2, name: 'Dr. Juan Dela Cruz', specialty: 'Pediatrics', availability: 'Tue-Thu, Sat', contact: '09123456790' },
    { id: 3, name: 'Dr. Ana Reyes', specialty: 'Ob-Gyne', availability: 'Mon, Wed, Fri', contact: '09123456791' },
]);

const searchQuery = ref('');
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-6">
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center border-b pb-4 space-y-4 lg:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Doctors</h2>
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                <div class="relative w-full sm:w-64">
                    <input v-model="searchQuery" type="text" placeholder="Search doctors..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                </div>
                <button
                    class="w-full sm:w-auto bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center">
                    <PlusIcon class="w-5 h-5" />
                    Add Doctor
                </button>
            </div>
        </div>

        <!-- Mobile View -->
        <div class="lg:hidden space-y-4">
            <div v-for="doctor in doctors" :key="doctor.id" class="bg-gray-50 p-4 rounded-lg shadow">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ doctor.name }}</h3>
                        <p class="text-sm text-gray-600">{{ doctor.specialty }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div>
                        <p class="text-gray-500">Availability:</p>
                        <p class="font-medium">{{ doctor.availability }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Contact:</p>
                        <p class="font-medium">{{ doctor.contact }}</p>
                    </div>
                </div>
                <div class="mt-3 flex justify-end space-x-2">
                    <button class="text-blue-600 hover:underline text-sm">Edit</button>
                    <button class="text-green-600 hover:underline text-sm">View Schedule</button>
                </div>
            </div>
        </div>

        <!-- Desktop View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">Specialty</th>
                        <th class="p-3">Availability</th>
                        <th class="p-3">Contact</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="doctor in doctors" :key="doctor.id" class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ doctor.name }}</td>
                        <td class="p-3">{{ doctor.specialty }}</td>
                        <td class="p-3">{{ doctor.availability }}</td>
                        <td class="p-3">{{ doctor.contact }}</td>
                        <td class="p-3 text-right">
                            <div class="flex justify-end space-x-2">
                                <button class="text-blue-600 hover:underline text-sm">Edit</button>
                                <button class="text-green-600 hover:underline text-sm">Schedule</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>