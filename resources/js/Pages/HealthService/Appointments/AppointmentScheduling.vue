<!-- Appointments/AppointmentScheduling.vue -->
<script setup>
import { ref } from 'vue';
import { PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const appointments = ref([
    { id: 1, patient: 'Maria Clara', doctor: 'Dr. Santos', date: '2025-08-10', time: '10:00 AM', status: 'Confirmed' },
    { id: 2, patient: 'Jose Rizal', doctor: 'Dr. Dela Cruz', date: '2025-08-10', time: '11:30 AM', status: 'Pending' },
    { id: 3, patient: 'Andres Bonifacio', doctor: 'Dr. Reyes', date: '2025-08-11', time: '9:00 AM', status: 'Confirmed' },
]);

const searchQuery = ref('');
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-6">
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center border-b pb-4 space-y-4 lg:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Appointment & Scheduling</h2>
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                <div class="relative w-full sm:w-64">
                    <input v-model="searchQuery" type="text" placeholder="Search appointments..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                </div>
                <button
                    class="w-full sm:w-auto bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center">
                    <PlusIcon class="w-5 h-5" />
                    New Appointment
                </button>
            </div>
        </div>

        <!-- Mobile View -->
        <div class="lg:hidden space-y-4">
            <div v-for="appointment in appointments" :key="appointment.id" class="bg-gray-50 p-4 rounded-lg shadow">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ appointment.patient }}</h3>
                        <p class="text-sm text-gray-600">with {{ appointment.doctor }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-bold rounded-full" :class="{
                        'bg-green-100 text-green-800': appointment.status === 'Confirmed',
                        'bg-yellow-100 text-yellow-800': appointment.status === 'Pending'
                    }">
                        {{ appointment.status }}
                    </span>
                </div>
                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div>
                        <p class="text-gray-500">Date & Time:</p>
                        <p class="font-medium">{{ appointment.date }} at {{ appointment.time }}</p>
                    </div>
                </div>
                <div class="mt-3 flex justify-end space-x-2">
                    <button class="text-blue-600 hover:underline text-sm">Edit</button>
                    <button class="text-green-600 hover:underline text-sm">Confirm</button>
                </div>
            </div>
        </div>

        <!-- Desktop View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Patient</th>
                        <th class="p-3">Doctor</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Time</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="appointment in appointments" :key="appointment.id" class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ appointment.patient }}</td>
                        <td class="p-3">{{ appointment.doctor }}</td>
                        <td class="p-3">{{ appointment.date }}</td>
                        <td class="p-3">{{ appointment.time }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full" :class="{
                                'bg-green-100 text-green-800': appointment.status === 'Confirmed',
                                'bg-yellow-100 text-yellow-800': appointment.status === 'Pending'
                            }">
                                {{ appointment.status }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex justify-end space-x-2">
                                <button class="text-blue-600 hover:underline text-sm">Edit</button>
                                <button class="text-green-600 hover:underline text-sm">Confirm</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>