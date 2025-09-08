<!-- MentalHealth/MentalHealthSupport.vue -->
<script setup>
import { ref } from 'vue';
import { PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const mentalHealthRecords = ref([
    {
        id: 1,
        patient: 'Maria Clara',
        serviceType: 'Individual Counseling',
        date: '2025-08-10',
        status: 'Completed',
        provider: 'Dr. Santos',
        notes: 'Discussed anxiety management techniques'
    },
    {
        id: 2,
        patient: 'Jose Rizal',
        serviceType: 'Crisis Intervention',
        date: '2025-08-15',
        status: 'Scheduled',
        provider: 'Dr. Dela Cruz',
        notes: 'Follow-up from previous session'
    },
    {
        id: 3,
        patient: 'Andres Bonifacio',
        serviceType: 'Group Therapy',
        date: '2025-08-05',
        status: 'Completed',
        provider: 'Dr. Reyes',
        notes: 'Focused on stress management'
    },
    {
        id: 4,
        patient: 'Gabriela Silang',
        serviceType: 'Psychiatric Evaluation',
        date: '2025-08-12',
        status: 'Pending',
        provider: 'Dr. Dela Cruz',
        notes: 'Initial assessment needed'
    },
    {
        id: 5,
        patient: 'Emilio Aguinaldo',
        serviceType: 'Family Counseling',
        date: '2025-08-18',
        status: 'Scheduled',
        provider: 'Dr. Santos',
        notes: 'Family conflict resolution'
    }
]);

const searchQuery = ref('');
const selectedStatus = ref('');
const selectedServiceType = ref('');

const statusOptions = ['All', 'Completed', 'Scheduled', 'Pending'];
const serviceTypeOptions = ['All', 'Individual Counseling', 'Group Therapy', 'Crisis Intervention', 'Psychiatric Evaluation', 'Family Counseling'];

const filteredRecords = ref([...mentalHealthRecords.value]);

const filterRecords = () => {
    filteredRecords.value = mentalHealthRecords.value.filter(record => {
        const matchesSearch = record.patient.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            record.serviceType.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value === '' || selectedStatus.value === 'All' ||
            record.status === selectedStatus.value;
        const matchesServiceType = selectedServiceType.value === '' || selectedServiceType.value === 'All' ||
            record.serviceType === selectedServiceType.value;
        return matchesSearch && matchesStatus && matchesServiceType;
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
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Mental Health Support</h2>

            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <input v-model="searchQuery" @input="filterRecords" type="text"
                        placeholder="Search patients or services..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                </div>

                <!-- Status Filter -->
                <select v-model="selectedStatus" @change="filterRecords"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="All">All Statuses</option>
                    <option v-for="status in statusOptions.filter(s => s !== 'All')" :key="status" :value="status">
                        {{ status }}
                    </option>
                </select>

                <!-- Service Type Filter -->
                <select v-model="selectedServiceType" @change="filterRecords"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="All">All Services</option>
                    <option v-for="service in serviceTypeOptions.filter(s => s !== 'All')" :key="service"
                        :value="service">
                        {{ service }}
                    </option>
                </select>

                <!-- Add Record Button -->
                <button
                    class="w-full sm:w-auto bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center">
                    <PlusIcon class="w-5 h-5" />
                    New Record
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ mentalHealthRecords.length }}</div>
                <div class="text-blue-700 text-sm font-semibold">Total Sessions</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{mentalHealthRecords.filter(r => r.status ===
                    'Completed').length}}</div>
                <div class="text-green-700 text-sm font-semibold">Completed</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{mentalHealthRecords.filter(r => r.status ===
                    'Scheduled').length}}</div>
                <div class="text-yellow-700 text-sm font-semibold">Scheduled</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">{{mentalHealthRecords.filter(r => r.status ===
                    'Pending').length}}</div>
                <div class="text-purple-700 text-sm font-semibold">Pending</div>
            </div>
        </div>

        <!-- Mobile Cards View -->
        <div class="lg:hidden space-y-4">
            <div v-for="record in filteredRecords" :key="record.id" class="bg-gray-50 p-4 rounded-lg shadow">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ record.patient }}</h3>
                        <p class="text-sm text-gray-600">{{ record.serviceType }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(record.status)">
                        {{ record.status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div>
                        <p class="text-gray-500">Date:</p>
                        <p class="font-medium">{{ record.date }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Provider:</p>
                        <p class="font-medium">{{ record.provider }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Notes:</p>
                        <p class="font-medium">{{ record.notes }}</p>
                    </div>
                </div>

                <div class="mt-3 flex justify-end space-x-2">
                    <button class="text-blue-600 hover:underline text-sm">Edit</button>
                    <button class="text-green-600 hover:underline text-sm">View Details</button>
                </div>
            </div>

            <div v-if="filteredRecords.length === 0" class="text-center py-8 text-gray-500">
                No mental health records found matching your criteria.
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Patient</th>
                        <th class="p-3">Service Type</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Provider</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Notes</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="record in filteredRecords" :key="record.id" class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ record.patient }}</td>
                        <td class="p-3">{{ record.serviceType }}</td>
                        <td class="p-3">{{ record.date }}</td>
                        <td class="p-3">{{ record.provider }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full"
                                :class="getStatusClass(record.status)">
                                {{ record.status }}
                            </span>
                        </td>
                        <td class="p-3 max-w-xs truncate">{{ record.notes }}</td>
                        <td class="p-3 text-right">
                            <div class="flex justify-end space-x-2">
                                <button class="text-blue-600 hover:underline text-sm">Edit</button>
                                <button class="text-green-600 hover:underline text-sm">Details</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="filteredRecords.length === 0" class="text-center py-8 text-gray-500">
                No mental health records found matching your criteria.
            </div>
        </div>

        <!-- Resources Section -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Mental Health Resources</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700">Crisis Hotlines</h4>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li>National Center for Mental Health: <span class="font-medium">(02) 8531-9001</span></li>
                        <li>Hopeline Philippines: <span class="font-medium">(02) 8804-4673</span></li>
                        <li>In Touch Community Services: <span class="font-medium">(02) 8893-7603</span></li>
                    </ul>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700">Online Resources</h4>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li>MentalHealthPH - Online support community</li>
                        <li>MindNation - Digital mental health platform</li>
                        <li>Psychological Association of the Philippines</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>