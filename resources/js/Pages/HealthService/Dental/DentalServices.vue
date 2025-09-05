<!-- Dental/DentalServices.vue -->
<script setup>
import { ref } from 'vue';
import { PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const dentalServices = ref([
    {
        id: 1,
        patient: 'Maria Clara',
        procedure: 'Dental Checkup',
        date: '2025-08-10',
        status: 'Completed',
        dentist: 'Dr. Santos',
        notes: 'Regular checkup, no issues found'
    },
    {
        id: 2,
        patient: 'Jose Rizal',
        procedure: 'Tooth Extraction',
        date: '2025-08-15',
        status: 'Scheduled',
        dentist: 'Dr. Dela Cruz',
        notes: 'Upper left molar extraction'
    },
    {
        id: 3,
        patient: 'Andres Bonifacio',
        procedure: 'Teeth Cleaning',
        date: '2025-08-05',
        status: 'Completed',
        dentist: 'Dr. Reyes',
        notes: 'Routine cleaning, minor plaque removal'
    },
    {
        id: 4,
        patient: 'Gabriela Silang',
        procedure: 'Filling',
        date: '2025-08-12',
        status: 'Pending',
        dentist: 'Dr. Dela Cruz',
        notes: 'Cavity filling on lower right premolar'
    },
    {
        id: 5,
        patient: 'Emilio Aguinaldo',
        procedure: 'Dental Checkup',
        date: '2025-08-18',
        status: 'Scheduled',
        dentist: 'Dr. Santos',
        notes: 'Annual dental examination'
    }
]);

const searchQuery = ref('');
const selectedStatus = ref('');

const statusOptions = ['All', 'Completed', 'Scheduled', 'Pending'];

const filteredServices = ref([...dentalServices.value]);

const filterServices = () => {
    filteredServices.value = dentalServices.value.filter(service => {
        const matchesSearch = service.patient.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            service.procedure.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value === '' || selectedStatus.value === 'All' ||
            service.status === selectedStatus.value;
        return matchesSearch && matchesStatus;
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Completed': return 'bg-green-100 text-green-800';
        case 'Scheduled': return 'bg-blue-100 text-blue-800';
        case 'Pending': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-6">
        <!-- Header & Controls -->
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center border-b pb-4 space-y-4 lg:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Dental Services</h2>

            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <input v-model="searchQuery" @input="filterServices" type="text"
                        placeholder="Search patients or procedures..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                </div>

                <!-- Status Filter -->
                <select v-model="selectedStatus" @change="filterServices"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="All">All Statuses</option>
                    <option v-for="status in statusOptions.filter(s => s !== 'All')" :key="status" :value="status">
                        {{ status }}
                    </option>
                </select>

                <!-- Add Service Button -->
                <button
                    class="w-full sm:w-auto bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center">
                    <PlusIcon class="w-5 h-5" />
                    New Service
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ dentalServices.length }}</div>
                <div class="text-blue-700 text-sm font-semibold">Total Services</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{dentalServices.filter(s => s.status ===
                    'Completed').length}}</div>
                <div class="text-green-700 text-sm font-semibold">Completed</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{dentalServices.filter(s => s.status ===
                    'Scheduled').length}}</div>
                <div class="text-yellow-700 text-sm font-semibold">Scheduled</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">{{dentalServices.filter(s => s.status ===
                    'Pending').length}}</div>
                <div class="text-purple-700 text-sm font-semibold">Pending</div>
            </div>
        </div>

        <!-- Mobile Cards View -->
        <div class="lg:hidden space-y-4">
            <div v-for="service in filteredServices" :key="service.id" class="bg-gray-50 p-4 rounded-lg shadow">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ service.patient }}</h3>
                        <p class="text-sm text-gray-600">{{ service.procedure }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(service.status)">
                        {{ service.status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div>
                        <p class="text-gray-500">Date:</p>
                        <p class="font-medium">{{ service.date }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Dentist:</p>
                        <p class="font-medium">{{ service.dentist }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Notes:</p>
                        <p class="font-medium">{{ service.notes }}</p>
                    </div>
                </div>

                <div class="mt-3 flex justify-end space-x-2">
                    <button class="text-blue-600 hover:underline text-sm">Edit</button>
                    <button class="text-green-600 hover:underline text-sm">View Details</button>
                </div>
            </div>

            <div v-if="filteredServices.length === 0" class="text-center py-8 text-gray-500">
                No dental services found matching your criteria.
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Patient</th>
                        <th class="p-3">Procedure</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Dentist</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Notes</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="service in filteredServices" :key="service.id" class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ service.patient }}</td>
                        <td class="p-3">{{ service.procedure }}</td>
                        <td class="p-3">{{ service.date }}</td>
                        <td class="p-3">{{ service.dentist }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full"
                                :class="getStatusClass(service.status)">
                                {{ service.status }}
                            </span>
                        </td>
                        <td class="p-3 max-w-xs truncate">{{ service.notes }}</td>
                        <td class="p-3 text-right">
                            <div class="flex justify-end space-x-2">
                                <button class="text-blue-600 hover:underline text-sm">Edit</button>
                                <button class="text-green-600 hover:underline text-sm">Details</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="filteredServices.length === 0" class="text-center py-8 text-gray-500">
                No dental services found matching your criteria.
            </div>
        </div>
    </div>
</template>