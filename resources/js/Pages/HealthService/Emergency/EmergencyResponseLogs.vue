<!-- Emergency/EmergencyResponseLogs.vue -->
<script setup>
import { ref } from 'vue';
import { PlusIcon, MagnifyingGlassIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const emergencyLogs = ref([
    {
        id: 1,
        incidentType: 'Medical Emergency',
        date: '2025-08-10',
        time: '14:30',
        location: 'Barangay Hall',
        status: 'Resolved',
        responder: 'Emergency Team A',
        severity: 'High',
        description: 'Resident experienced chest pain and difficulty breathing'
    },
    {
        id: 2,
        incidentType: 'Fire Incident',
        date: '2025-08-09',
        time: '09:15',
        location: 'Purok 3, House #25',
        status: 'Under Control',
        responder: 'Fire Response Team',
        severity: 'Critical',
        description: 'Kitchen fire due to unattended cooking'
    },
    {
        id: 3,
        incidentType: 'Natural Disaster',
        date: '2025-08-08',
        time: '16:45',
        location: 'Evacuation Center',
        status: 'Ongoing',
        responder: 'Disaster Response Team',
        severity: 'Critical',
        description: 'Flash floods in low-lying areas, evacuation in progress'
    },
    {
        id: 4,
        incidentType: 'Traffic Accident',
        date: '2025-08-07',
        time: '11:20',
        location: 'Main Road, Near Market',
        status: 'Resolved',
        responder: 'Emergency Team B',
        severity: 'Medium',
        description: 'Motorcycle collision, minor injuries treated on site'
    },
    {
        id: 5,
        incidentType: 'Medical Emergency',
        date: '2025-08-06',
        time: '18:10',
        location: 'Purok 5, House #12',
        status: 'Resolved',
        responder: 'Emergency Team A',
        severity: 'Medium',
        description: 'Elderly resident fell, treated for minor bruises'
    }
]);

const searchQuery = ref('');
const selectedStatus = ref('');
const selectedIncidentType = ref('');

const statusOptions = ['All', 'Ongoing', 'Resolved', 'Under Control'];
const incidentTypeOptions = ['All', 'Medical Emergency', 'Fire Incident', 'Natural Disaster', 'Traffic Accident', 'Other'];

const filteredLogs = ref([...emergencyLogs.value]);

const filterLogs = () => {
    filteredLogs.value = emergencyLogs.value.filter(log => {
        const matchesSearch = log.location.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.incidentType.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.description.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value === '' || selectedStatus.value === 'All' ||
            log.status === selectedStatus.value;
        const matchesIncidentType = selectedIncidentType.value === '' || selectedIncidentType.value === 'All' ||
            log.incidentType === selectedIncidentType.value;
        return matchesSearch && matchesStatus && matchesIncidentType;
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Resolved': return 'bg-green-100 text-green-800';
        case 'Under Control': return 'bg-blue-100 text-blue-800';
        case 'Ongoing': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getSeverityClass = (severity) => {
    switch (severity) {
        case 'Critical': return 'bg-red-100 text-red-800';
        case 'High': return 'bg-orange-100 text-orange-800';
        case 'Medium': return 'bg-yellow-100 text-yellow-800';
        case 'Low': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-6">
        <!-- Header & Controls -->
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center border-b pb-4 space-y-4 lg:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Emergency Response Logs</h2>

            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <input v-model="searchQuery" @input="filterLogs" type="text" placeholder="Search incidents..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                </div>

                <!-- Status Filter -->
                <select v-model="selectedStatus" @change="filterLogs"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="All">All Statuses</option>
                    <option v-for="status in statusOptions.filter(s => s !== 'All')" :key="status" :value="status">
                        {{ status }}
                    </option>
                </select>

                <!-- Incident Type Filter -->
                <select v-model="selectedIncidentType" @change="filterLogs"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="All">All Types</option>
                    <option v-for="type in incidentTypeOptions.filter(t => t !== 'All')" :key="type" :value="type">
                        {{ type }}
                    </option>
                </select>

                <!-- Add Log Button -->
                <button
                    class="w-full sm:w-auto bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center">
                    <PlusIcon class="w-5 h-5" />
                    New Log
                </button>
            </div>
        </div>

        <!-- Emergency Alert Banner -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <ExclamationTriangleIcon class="w-6 h-6 text-red-600 mt-0.5" />
                <div>
                    <h3 class="font-semibold text-red-800">Emergency Alert</h3>
                    <p class="text-red-700 text-sm mt-1">
                        {{emergencyLogs.filter(log => log.status === 'Ongoing').length}} ongoing emergency situation(s)
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ emergencyLogs.length }}</div>
                <div class="text-blue-700 text-sm font-semibold">Total Incidents</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{emergencyLogs.filter(log => log.status ===
                    'Ongoing').length}}</div>
                <div class="text-red-700 text-sm font-semibold">Ongoing</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{emergencyLogs.filter(log => log.status ===
                    'Resolved').length}}</div>
                <div class="text-green-700 text-sm font-semibold">Resolved</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">{{emergencyLogs.filter(log => log.severity ===
                    'Critical').length}}</div>
                <div class="text-orange-700 text-sm font-semibold">Critical Severity</div>
            </div>
        </div>

        <!-- Mobile Cards View -->
        <div class="lg:hidden space-y-4">
            <div v-for="log in filteredLogs" :key="log.id" class="bg-gray-50 p-4 rounded-lg shadow border-l-4" :class="{
                'border-l-red-500': log.severity === 'Critical',
                'border-l-orange-500': log.severity === 'High',
                'border-l-yellow-500': log.severity === 'Medium',
                'border-l-green-500': log.severity === 'Low'
            }">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ log.incidentType }}</h3>
                        <p class="text-sm text-gray-600">{{ log.date }} at {{ log.time }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(log.status)">
                        {{ log.status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div>
                        <p class="text-gray-500">Location:</p>
                        <p class="font-medium">{{ log.location }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Responder:</p>
                        <p class="font-medium">{{ log.responder }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Severity:</p>
                        <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getSeverityClass(log.severity)">
                            {{ log.severity }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-500">Description:</p>
                        <p class="font-medium">{{ log.description }}</p>
                    </div>
                </div>

                <div class="mt-3 flex justify-end space-x-2">
                    <button class="text-blue-600 hover:underline text-sm">Update</button>
                    <button class="text-green-600 hover:underline text-sm">View Details</button>
                </div>
            </div>

            <div v-if="filteredLogs.length === 0" class="text-center py-8 text-gray-500">
                No emergency logs found matching your criteria.
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Incident Type</th>
                        <th class="p-3">Date & Time</th>
                        <th class="p-3">Location</th>
                        <th class="p-3">Responder</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Severity</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in filteredLogs" :key="log.id" class="border-t hover:bg-gray-50" :class="{
                        'bg-red-50': log.severity === 'Critical',
                        'bg-orange-50': log.severity === 'High',
                        'bg-yellow-50': log.severity === 'Medium'
                    }">
                        <td class="p-3 font-semibold">{{ log.incidentType }}</td>
                        <td class="p-3">{{ log.date }}<br><span class="text-sm text-gray-500">{{ log.time }}</span></td>
                        <td class="p-3">{{ log.location }}</td>
                        <td class="p-3">{{ log.responder }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(log.status)">
                                {{ log.status }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full"
                                :class="getSeverityClass(log.severity)">
                                {{ log.severity }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex justify-end space-x-2">
                                <button class="text-blue-600 hover:underline text-sm">Update</button>
                                <button class="text-green-600 hover:underline text-sm">Details</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="filteredLogs.length === 0" class="text-center py-8 text-gray-500">
                No emergency logs found matching your criteria.
            </div>
        </div>

        <!-- Emergency Contacts Section -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Emergency Contacts</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700">Local Emergency Services</h4>
                    <ul class="mt-2 space-y-2 text-sm">
                        <li class="flex justify-between">
                            <span>Barangay Emergency Hotline:</span>
                            <span class="font-medium">(02) 8888-9999</span>
                        </li>
                        <li class="flex justify-between">
                            <span>Fire Department:</span>
                            <span class="font-medium">(02) 8888-7777</span>
                        </li>
                        <li class="flex justify-between">
                            <span>Police Station:</span>
                            <span class="font-medium">(02) 8888-6666</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700">Medical Emergency</h4>
                    <ul class="mt-2 space-y-2 text-sm">
                        <li class="flex justify-between">
                            <span>Nearest Hospital:</span>
                            <span class="font-medium">(02) 8888-5555</span>
                        </li>
                        <li class="flex justify-between">
                            <span>Ambulance Service:</span>
                            <span class="font-medium">(02) 8888-4444</span>
                        </li>
                        <li class="flex justify-between">
                            <span>Rescue Unit:</span>
                            <span class="font-medium">(02) 8888-3333</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>