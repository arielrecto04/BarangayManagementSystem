<!-- @/Pages/HealthService/Environmental/EnvironmentalHealthMonitoring.vue -->
<script setup>
import { ref } from 'vue';
import { PlusIcon, MagnifyingGlassIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

// Sample Environmental Monitoring Logs
const envLogs = ref([
    {
        id: 1,
        issueType: 'Dengue Hotspot',
        date: '2025-08-10',
        location: 'Purok 2',
        status: 'Ongoing',
        severity: 'High',
        inspector: 'Health Worker A',
        description: 'Multiple mosquito breeding sites identified near drainage'
    },
    {
        id: 2,
        issueType: 'Water Contamination',
        date: '2025-08-08',
        location: 'Purok 5, Deep Well',
        status: 'Resolved',
        severity: 'Critical',
        inspector: 'Sanitation Team',
        description: 'Water tested positive for coliform bacteria, chlorination performed'
    },
    {
        id: 3,
        issueType: 'Garbage Accumulation',
        date: '2025-08-06',
        location: 'Barangay Market Area',
        status: 'Ongoing',
        severity: 'Medium',
        inspector: 'Environmental Officer',
        description: 'Uncollected waste causing foul odor and pests'
    },
    {
        id: 4,
        issueType: 'Air Quality',
        date: '2025-08-05',
        location: 'Main Road',
        status: 'Under Control',
        severity: 'Low',
        inspector: 'Barangay Health Worker',
        description: 'Vehicle smoke emission monitored, advised traffic rerouting'
    }
]);

const searchQuery = ref('');
const selectedStatus = ref('All');
const selectedIssueType = ref('All');

const statusOptions = ['All', 'Ongoing', 'Resolved', 'Under Control'];
const issueTypeOptions = ['All', 'Dengue Hotspot', 'Water Contamination', 'Garbage Accumulation', 'Air Quality', 'Other'];

const filteredLogs = ref([...envLogs.value]);

const filterLogs = () => {
    filteredLogs.value = envLogs.value.filter(log => {
        const matchesSearch = log.location.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.issueType.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.description.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value === 'All' || log.status === selectedStatus.value;
        const matchesIssueType = selectedIssueType.value === 'All' || log.issueType === selectedIssueType.value;
        return matchesSearch && matchesStatus && matchesIssueType;
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
        <!-- Header -->
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center border-b pb-4 space-y-4 lg:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Environmental Health Monitoring</h2>

            <!-- Controls -->
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <input v-model="searchQuery" @input="filterLogs" type="text"
                        placeholder="Search environmental issues..."
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

                <!-- Issue Type Filter -->
                <select v-model="selectedIssueType" @change="filterLogs"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="All">All Types</option>
                    <option v-for="type in issueTypeOptions.filter(t => t !== 'All')" :key="type" :value="type">
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

        <!-- Alert -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <ExclamationTriangleIcon class="w-6 h-6 text-yellow-600 mt-0.5" />
                <div>
                    <h3 class="font-semibold text-yellow-800">Environmental Alert</h3>
                    <p class="text-yellow-700 text-sm mt-1">
                        {{envLogs.filter(log => log.status === 'Ongoing').length}} ongoing environmental issue(s)
                    </p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Issue Type</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Location</th>
                        <th class="p-3">Inspector</th>
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
                        <td class="p-3 font-semibold">{{ log.issueType }}</td>
                        <td class="p-3">{{ log.date }}</td>
                        <td class="p-3">{{ log.location }}</td>
                        <td class="p-3">{{ log.inspector }}</td>
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
                No environmental issues found matching your criteria.
            </div>
        </div>
    </div>
</template>
