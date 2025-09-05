<!-- @/Pages/HealthService/Certificates/HealthCertificates.vue -->
<script setup>
import { ref } from 'vue';
import { PlusIcon, MagnifyingGlassIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

// Sample Health Certificates Data
const certificates = ref([
    {
        id: 1,
        certificateType: 'Medical Clearance',
        residentName: 'Juan Dela Cruz',
        dateIssued: '2025-08-15',
        status: 'Valid',
        validity: '2026-08-15',
        issuedBy: 'Dr. Santos',
        purpose: 'Employment Requirement'
    },
    {
        id: 2,
        certificateType: 'Sanitary Permit',
        residentName: 'Maria Reyes',
        dateIssued: '2025-07-20',
        status: 'Expired',
        validity: '2025-07-31',
        issuedBy: 'Barangay Health Officer',
        purpose: 'Food Stall Operation'
    },
    {
        id: 3,
        certificateType: 'Health Certificate',
        residentName: 'Pedro Cruz',
        dateIssued: '2025-08-01',
        status: 'Pending',
        validity: '-',
        issuedBy: 'Processing',
        purpose: 'Travel Abroad'
    }
]);

const searchQuery = ref('');
const selectedStatus = ref('All');
const selectedCertificateType = ref('All');

const statusOptions = ['All', 'Valid', 'Expired', 'Pending'];
const certificateTypeOptions = ['All', 'Medical Clearance', 'Sanitary Permit', 'Health Certificate'];

const filteredCertificates = ref([...certificates.value]);

const filterCertificates = () => {
    filteredCertificates.value = certificates.value.filter(cert => {
        const matchesSearch = cert.residentName.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            cert.certificateType.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            cert.purpose.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value === 'All' || cert.status === selectedStatus.value;
        const matchesType = selectedCertificateType.value === 'All' || cert.certificateType === selectedCertificateType.value;
        return matchesSearch && matchesStatus && matchesType;
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Valid': return 'bg-green-100 text-green-800';
        case 'Expired': return 'bg-red-100 text-red-800';
        case 'Pending': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-6">
        <!-- Header -->
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center border-b pb-4 space-y-4 lg:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <DocumentTextIcon class="w-7 h-7 text-green-600" />
                Health Certificates & Permits
            </h2>

            <!-- Controls -->
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <input v-model="searchQuery" @input="filterCertificates" type="text"
                        placeholder="Search certificates..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                </div>

                <!-- Status Filter -->
                <select v-model="selectedStatus" @change="filterCertificates"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="All">All Statuses</option>
                    <option v-for="status in statusOptions.filter(s => s !== 'All')" :key="status" :value="status">
                        {{ status }}
                    </option>
                </select>

                <!-- Certificate Type Filter -->
                <select v-model="selectedCertificateType" @change="filterCertificates"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="All">All Types</option>
                    <option v-for="type in certificateTypeOptions.filter(t => t !== 'All')" :key="type" :value="type">
                        {{ type }}
                    </option>
                </select>

                <!-- Add Certificate Button -->
                <button
                    class="w-full sm:w-auto bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center">
                    <PlusIcon class="w-5 h-5" />
                    New Certificate
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ certificates.length }}</div>
                <div class="text-green-700 text-sm font-semibold">Total Certificates</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{certificates.filter(c => c.status ===
                    'Pending').length}}</div>
                <div class="text-yellow-700 text-sm font-semibold">Pending</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{certificates.filter(c => c.status === 'Expired').length
                    }}</div>
                <div class="text-red-700 text-sm font-semibold">Expired</div>
            </div>
        </div>

        <!-- Certificates Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Certificate Type</th>
                        <th class="p-3">Resident Name</th>
                        <th class="p-3">Date Issued</th>
                        <th class="p-3">Validity</th>
                        <th class="p-3">Issued By</th>
                        <th class="p-3">Purpose</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="cert in filteredCertificates" :key="cert.id" class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ cert.certificateType }}</td>
                        <td class="p-3">{{ cert.residentName }}</td>
                        <td class="p-3">{{ cert.dateIssued }}</td>
                        <td class="p-3">{{ cert.validity }}</td>
                        <td class="p-3">{{ cert.issuedBy }}</td>
                        <td class="p-3">{{ cert.purpose }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(cert.status)">
                                {{ cert.status }}
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

            <div v-if="filteredCertificates.length === 0" class="text-center py-8 text-gray-500">
                No certificates found matching your criteria.
            </div>
        </div>
    </div>
</template>
