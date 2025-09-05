<script setup>
import { ref } from 'vue';
import { PlusIcon, ExclamationTriangleIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

// Sample inventory data
const inventory = ref([
    {
        id: 1,
        name: 'Paracetamol 500mg',
        category: 'Analgesic',
        stock: 8,
        min_stock: 50,
        unit: 'tablets',
        expiry_date: '2025-12-15',
        supplier: 'ABC Pharma',
        batch_no: 'PCT2024001'
    },
    {
        id: 2,
        name: 'Amoxicillin 250mg',
        category: 'Antibiotic',
        stock: 5,
        min_stock: 30,
        unit: 'capsules',
        expiry_date: '2025-11-20',
        supplier: 'XYZ Medical',
        batch_no: 'AMX2024002'
    },
    {
        id: 3,
        name: 'Lagundi Syrup',
        category: 'Herbal Medicine',
        stock: 3,
        min_stock: 20,
        unit: 'bottles',
        expiry_date: '2025-10-30',
        supplier: 'Local Supplier',
        batch_no: 'LAG2024003'
    },
    {
        id: 4,
        name: 'Ibuprofen 400mg',
        category: 'NSAID',
        stock: 75,
        min_stock: 40,
        unit: 'tablets',
        expiry_date: '2026-03-10',
        supplier: 'ABC Pharma',
        batch_no: 'IBU2024004'
    },
    {
        id: 5,
        name: 'Multivitamins',
        category: 'Supplement',
        stock: 120,
        min_stock: 50,
        unit: 'tablets',
        expiry_date: '2026-01-25',
        supplier: 'Health Plus',
        batch_no: 'MVI2024005'
    }
]);

const searchQuery = ref('');
const selectedCategory = ref('');

const categories = ['Analgesic', 'Antibiotic', 'Herbal Medicine', 'NSAID', 'Supplement'];

const filteredInventory = ref(inventory.value);

const filterInventory = () => {
    filteredInventory.value = inventory.value.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = selectedCategory.value === '' || item.category === selectedCategory.value;
        return matchesSearch && matchesCategory;
    });
};

const getStockStatus = (item) => {
    if (item.stock <= item.min_stock * 0.2) return 'critical';
    if (item.stock <= item.min_stock * 0.5) return 'low';
    return 'normal';
};

const getStockColor = (status) => {
    switch (status) {
        case 'critical': return 'text-red-600 bg-red-100';
        case 'low': return 'text-orange-600 bg-orange-100';
        default: return 'text-green-600 bg-green-100';
    }
};

const isNearExpiry = (expiryDate) => {
    const today = new Date();
    const expiry = new Date(expiryDate);
    const diffTime = expiry - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 90; // Within 3 months
};
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg space-y-6">
        <!-- Header & Controls -->
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center border-b pb-4 space-y-4 lg:space-y-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Medicine Inventory</h2>

            <div
                class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <input v-model="searchQuery" @input="filterInventory" type="text" placeholder="Search medicines..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                </div>

                <!-- Category Filter -->
                <select v-model="selectedCategory" @change="filterInventory"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category" :value="category">
                        {{ category }}
                    </option>
                </select>

                <!-- Add Medicine Button -->
                <button
                    class="w-full sm:w-auto bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center">
                    <PlusIcon class="w-5 h-5" />
                    Add Medicine
                </button>
            </div>
        </div>

        <!-- Critical Stock Alert -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <ExclamationTriangleIcon class="w-6 h-6 text-red-600 mt-0.5" />
                <div>
                    <h3 class="font-semibold text-red-800">Low Stock Alert</h3>
                    <p class="text-red-700 text-sm mt-1">
                        {{inventory.filter(item => getStockStatus(item) === 'critical').length}} medicines are
                        critically low on stock
                    </p>
                </div>
            </div>
        </div>

        <!-- Mobile Cards View -->
        <div class="lg:hidden space-y-4">
            <div v-for="item in filteredInventory" :key="item.id" class="bg-gray-50 p-4 rounded-lg shadow border-l-4"
                :class="{
                    'border-l-red-500': getStockStatus(item) === 'critical',
                    'border-l-orange-500': getStockStatus(item) === 'low',
                    'border-l-green-500': getStockStatus(item) === 'normal'
                }">

                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ item.name }}</h3>
                        <p class="text-sm text-gray-600">{{ item.category }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStockColor(getStockStatus(item))">
                        {{ item.stock }} {{ item.unit }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Min Stock:</p>
                        <p class="font-medium">{{ item.min_stock }} {{ item.unit }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Expiry:</p>
                        <p class="font-medium" :class="{ 'text-red-600': isNearExpiry(item.expiry_date) }">
                            {{ new Date(item.expiry_date).toLocaleDateString() }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Supplier:</p>
                        <p class="font-medium">{{ item.supplier }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Batch:</p>
                        <p class="font-medium">{{ item.batch_no }}</p>
                    </div>
                </div>

                <div class="mt-3 flex justify-end space-x-2">
                    <button class="text-blue-600 hover:underline text-sm">Update Stock</button>
                    <button class="text-green-600 hover:underline text-sm">View Details</button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ inventory.length }}</div>
                <div class="text-blue-700 text-sm font-semibold">Total Medicines</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{inventory.filter(item => getStockStatus(item) ===
                    'normal').length}}</div>
                <div class="text-green-700 text-sm font-semibold">Normal Stock</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">{{inventory.filter(item => getStockStatus(item) ===
                    'low').length}}</div>
                <div class="text-orange-700 text-sm font-semibold">Low Stock</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{inventory.filter(item => getStockStatus(item) ===
                    'critical').length}}</div>
                <div class="text-red-700 text-sm font-semibold">Critical Stock</div>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-sm text-gray-600 uppercase border-b">
                    <tr>
                        <th class="p-3">Medicine Name</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Current Stock</th>
                        <th class="p-3">Min Stock</th>
                        <th class="p-3">Expiry Date</th>
                        <th class="p-3">Supplier</th>
                        <th class="p-3">Batch No</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in filteredInventory" :key="item.id" class="border-t hover:bg-gray-50" :class="{
                        'bg-red-50': getStockStatus(item) === 'critical',
                        'bg-orange-50': getStockStatus(item) === 'low'
                    }">
                        <td class="p-3">
                            <div class="font-semibold">{{ item.name }}</div>
                        </td>
                        <td class="p-3">{{ item.category }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full"
                                :class="getStockColor(getStockStatus(item))">
                                {{ item.stock }} {{ item.unit }}
                            </span>
                        </td>
                        <td class="p-3">{{ item.min_stock }} {{ item.unit }}</td>
                        <td class="p-3" :class="{ 'text-red-600 font-semibold': isNearExpiry(item.expiry_date) }">
                            {{ new Date(item.expiry_date).toLocaleDateString() }}
                            <span v-if="isNearExpiry(item.expiry_date)" class="text-xs block text-red-500">
                                Near Expiry
                            </span>
                        </td>
                        <td class="p-3">{{ item.supplier }}</td>
                        <td class="p-3">{{ item.batch_no }}</td>
                        <td class="p-3 text-right">
                            <div class="flex justify-end space-x-2">
                                <button class="text-blue-600 hover:underline text-sm">Update</button>
                                <button class="text-green-600 hover:underline text-sm">Details</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>