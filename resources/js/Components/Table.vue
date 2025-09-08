<script setup>
import { ref, computed, reactive } from 'vue';
import { ChevronUpDownIcon, ChevronUpIcon, ChevronDownIcon, MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/outline';


const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    rows: {
        type: Array,
        required: true,
    },
    page: {
        type: Number,
        default: 1
    },
    total: {
        type: Number,
        default: 0
    },
    perPage: {
        type: Number,
        default: 10
    },

    loading: {
        type: Boolean,
        default: false
    },
    searchable: {
        type: Boolean,
        default: true
    },
    selectable: {
        type: Boolean,
        default: true
    },
    hoverEffect: {
        type: Boolean,
        default: true
    },
    clazz: {
        type: String,
        default: ''
    }

});

const emits = defineEmits(['update:page', 'row-click', 'sort', 'selection-change']);

// --- UTILITY FUNCTION ---
const getObjectValue = (obj, path) => {
    if (!path) return undefined;
    return path.split('.').reduce((acc, part) => acc && acc[part], obj);
};

// --- STATE FOR INTERACTIVITY ---
const sortKey = ref('');
const sortDirection = ref('asc');
const selectedRows = ref([]);
const searchQuery = ref('');
const showFilters = ref(false);

// Initialize filters
const filters = reactive({});
props.columns.forEach(col => {
    if (col.filterable !== false) {
        filters[col.key] = '';
    }
});

// --- COMPUTED PROPERTIES ---
const filteredRows = computed(() => {
    if (!props.rows?.length) return [];

    return props.rows.filter(row => {
        // Global search
        if (searchQuery.value) {
            const matches = props.columns.some(col => {
                const cellValue = String(getObjectValue(row, col.key) ?? '').toLowerCase();
                return cellValue.includes(searchQuery.value.toLowerCase());
            });
            if (!matches) return false;
        }

        // Column filters
        return props.columns.every(col => {
            if (col.filterable === false) return true;
            const filterValue = filters[col.key];
            if (!filterValue) return true;
            const cellValue = String(getObjectValue(row, col.key) ?? '').toLowerCase();
            return cellValue.includes(filterValue.toLowerCase());
        });
    });
});

const sortedRows = computed(() => {
    if (!sortKey.value) return filteredRows.value;

    const sorted = [...filteredRows.value];
    sorted.sort((a, b) => {
        const valA = getObjectValue(a, sortKey.value);
        const valB = getObjectValue(b, sortKey.value);
        if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
    });
    return sorted;
});

const paginatedRows = computed(() => {
    const start = (props.page - 1) * props.perPage;
    const end = start + props.perPage;
    return sortedRows.value.slice(start, end);
});

const hasRows = computed(() => props.rows && props.rows.length > 0);

const isAllSelected = computed({
    get: () => {
        if (!props.selectable) return false;
        const pageRowIds = paginatedRows.value.map(r => r.id);
        return pageRowIds.length > 0 && pageRowIds.every(id => selectedRows.value.includes(id));
    },
    set: (value) => {
        if (!props.selectable) return;
        const pageRowIds = paginatedRows.value.map(r => r.id);
        if (value) {
            pageRowIds.forEach(id => {
                if (!selectedRows.value.includes(id)) {
                    selectedRows.value.push(id);
                }
            });
        } else {
            selectedRows.value = selectedRows.value.filter(id => !pageRowIds.includes(id));
        }
        emits('selection-change', selectedRows.value);
    }
});

// --- METHODS ---
const handleSort = (key) => {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDirection.value = 'asc';
    }
    emits('sort', { key: sortKey.value, direction: sortDirection.value });
};

const handleRowClick = (row, event) => {
    // Don't emit if clicking on a button or link
    if (event.target.tagName === 'BUTTON' || event.target.tagName === 'A' || event.target.closest('button, a')) {
        return;
    }

    emits('row-click', row);
};

const clearFilters = () => {
    Object.keys(filters).forEach(key => {
        filters[key] = '';
    });
    searchQuery.value = '';
};
</script>

<template>
    <div class="space-y-4">
        <!-- Search and Filter Bar -->
        <div class="flex flex-col sm:flex-row justify-between gap-4">
            <!-- Search Input -->
            <div v-if="searchable" class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                </div>
                <input v-model="searchQuery" type="text"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                    placeholder="Search..." />
            </div>

            <!-- Filter Toggle Button -->
            <div class="flex items-center space-x-2">
                <button @click="showFilters = !showFilters"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <FunnelIcon class="h-4 w-4 mr-2" />
                    {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                </button>
                <button v-if="showFilters" @click="clearFilters"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Filters Panel -->
        <div v-if="showFilters" class="bg-base-200 p-4 rounded-lg border-base-300">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="column in columns.filter(c => c.filterable !== false)" :key="`filter-${column.key}`"
                    class=" w-full flex items-center gap-2">
                    <!-- <label :for="`filter-${column.key}`" class="label">
                        <span class="label-text">{{ column.label }}</span>
                    </label> -->
                    <input :id="`filter-${column.key}`" v-model="filters[column.key]" type="text"
                        :placeholder="`Filter ${column.label.toLowerCase()}`"
                        class="p-2 bg-gray-50 rounded-lg focus:outline-blue-500 w-full border-gray-500" />
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <!-- Loading State -->
                <div v-if="loading" class="p-8 text-center">
                    <div
                        class="inline-flex items-center px-4 py-2 text-sm font-medium leading-6 text-gray-700 transition duration-150 ease-in-out">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Loading data...
                    </div>
                </div>

                <!-- Table -->
                <table v-else class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- Checkbox Header -->
                            <th v-if="selectable" scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                <input type="checkbox" v-model="isAllSelected"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            </th>

                            <!-- Column Headers -->
                            <th v-for="column in columns" :key="column.key" scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                :class="{
                                    'cursor-pointer hover:bg-gray-100': column.sortable !== false,
                                    'w-1/6': column.width === '1/6',
                                    'w-1/4': column.width === '1/4',
                                    'w-1/3': column.width === '1/3',
                                    'w-1/2': column.width === '1/2',
                                    'w-full': column.width === 'full'
                                }" @click="column.sortable !== false ? handleSort(column.key) : null">
                                <div class="flex items-center justify-between">
                                    <span>{{ column.label }}</span>
                                    <span v-if="column.sortable !== false" class="ml-2 flex-none">
                                        <ChevronUpDownIcon v-if="sortKey !== column.key" class="h-4 w-4 text-gray-400"
                                            aria-hidden="true" />
                                        <ChevronUpIcon v-else-if="sortDirection === 'asc'" class="h-4 w-4 text-blue-500"
                                            aria-hidden="true" />
                                        <ChevronDownIcon v-else class="h-4 w-4 text-blue-500" aria-hidden="true" />
                                    </span>
                                </div>
                            </th>

                            <!-- Actions Header -->
                            <!-- <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th> -->
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Rows -->
                        <tr v-for="row in paginatedRows" :key="row.id" @click="handleRowClick(row, $event)"
                            class="transition-colors duration-150" :class="{
                                clazz,
                                'bg-blue-50': selectedRows.includes(row.id),
                                'hover:bg-gray-50': hoverEffect && !selectedRows.includes(row.id)
                            }">
                            <!-- Row Checkbox -->
                            <td v-if="selectable" class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" :value="row.id" v-model="selectedRows"
                                    @change="$emit('selection-change', selectedRows)"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    @click.stop />
                            </td>

                            <!-- Data Cells -->
                            <td v-for="column in columns" :key="`${row.id}-${column.key}`"
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" :class="{
                                    'font-medium': column.bold,
                                    'text-right': column.align === 'right',
                                    'text-center': column.align === 'center'
                                }">
                                <slot :name="`cell(${column.key})`" :row="row" :value="getObjectValue(row, column.key)">
                                    {{ column.formatter ? column.formatter(getObjectValue(row, column.key)) :
                                        getObjectValue(row, column.key) }}

                                </slot>
                            </td>

                            <!-- Actions Cell -->
                            <td v-if="$slots.actions"
                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex justify-center space-x-2" @click.stop>
                                    <slot name="actions" :row="row"></slot>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="!loading && paginatedRows.length === 0">
                            <td :colspan="columns.length + (selectable ? 2 : 1)" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">No results found</h3>
                                    <p class="text-gray-500">
                                        {{searchQuery || Object.values(filters).some(f => f) ?
                                            'Try adjusting your earch or filter to find what youre looking for.'
                                            : 'No data available.'}}
                                    </p>
                                    <button v-if="searchQuery || Object.values(filters).some(f => f)"
                                        @click="clearFilters"
                                        class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Clear all filters
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination and Selection Info -->
        <!-- <div class="flex flex-col sm:flex-row items-center justify-between px-1 py-3">
            <div v-if="selectable" class="text-sm text-gray-700 mb-4 sm:mb-0">
                <span v-if="selectedRows.length > 0">
                    {{ selectedRows.length }} of {{ sortedRows.length }} row(s) selected.
                </span>
                <span v-else>
                    Showing {{ Math.min((page - 1) * perPage + 1, sortedRows.length) }} to {{ Math.min(page * perPage,
                        sortedRows.length) }} of {{ sortedRows.length }} results
                </span>
            </div>
            <div v-else class="text-sm text-gray-700 mb-4 sm:mb-0">
                Showing {{ Math.min((page - 1) * perPage + 1, sortedRows.length) }} to {{ Math.min(page * perPage,
                    sortedRows.length) }} of {{ sortedRows.length }} results
            </div>

        </div> -->
    </div>
</template>

<style scoped>
/* Custom scrollbar for the table */
::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Smooth transitions */
tr {
    transition: background-color 0.15s ease-in-out;
}

/* Responsive table */
@media (max-width: 640px) {
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
}
</style>
