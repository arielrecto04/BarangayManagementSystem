<script setup>
import { computed, defineProps, defineEmits } from 'vue';

// Define component props with clear types and defaults
const props = defineProps({
    maxVisibleButtons: { type: Number, default: 5 },
    totalPages: { type: Number, required: true },
    totalItems: { type: Number, required: true },
    currentPage: { type: Number, required: true },
    itemsPerPage: { type: Number, default: 10 },
});

// Define the event that this component will emit
const emit = defineEmits(['page-changed']);

// Computed properties to check boundary conditions
const isFirstPage = computed(() => props.currentPage === 1);
const isLastPage = computed(() => props.currentPage === props.totalPages);

// Computed properties for the item count display
const itemRangeStart = computed(() => (props.currentPage - 1) * props.itemsPerPage + 1);
const itemRangeEnd = computed(() => Math.min(props.currentPage * props.itemsPerPage, props.totalItems));

/**
 * Generates the array of pages to be displayed.
 * This logic is designed to be smart about showing page numbers and ellipses.
 * Example: 1 ... 4 5 6 ... 10
 */
const pages = computed(() => {
    const { totalPages, currentPage, maxVisibleButtons } = props;
    const range = [];

    // Return empty if no pages
    if (totalPages <= 0) return [];

    // If there are not enough pages to require complex logic, show all of them.
    if (totalPages <= maxVisibleButtons) {
        for (let i = 1; i <= totalPages; i++) {
            range.push({ name: i });
        }
        return range;
    }

    const sideWidth = Math.floor((maxVisibleButtons - 2) / 2);
    const leftWidth = sideWidth;
    const rightWidth = totalPages - 1 - sideWidth;

    // Always show the first page
    range.push({ name: 1 });

    if (currentPage > leftWidth + 2) {
        range.push({ name: '...' });
    }

    let start = Math.max(2, currentPage - sideWidth);
    let end = Math.min(totalPages - 1, currentPage + sideWidth);

    if (currentPage < leftWidth + 2) {
        end = Math.max(end, maxVisibleButtons - 2);
    }

    if (currentPage > rightWidth) {
        start = Math.min(start, totalPages - (maxVisibleButtons - 3));
    }

    for (let i = start; i <= end; i++) {
        range.push({ name: i });
    }

    if (currentPage < rightWidth) {
        range.push({ name: '...' });
    }

    // Always show the last page
    range.push({ name: totalPages });

    return range;
});

// Event handler functions
function prevPage() {
    if (!isFirstPage.value) {
        emit('page-changed', props.currentPage - 1);
    }
}

function nextPage() {
    if (!isLastPage.value) {
        emit('page-changed', props.currentPage + 1);
    }
}

function changePage(page) {
    if (page !== props.currentPage) {
        emit('page-changed', page);
    }
}

function handleGoToPage(event) {
    const page = parseInt(event.target.value, 10);
    if (!isNaN(page) && page >= 1 && page <= props.totalPages) {
        emit('page-changed', page);
    } else {
        // Reset input to current page if invalid value is entered
        event.target.value = props.currentPage;
    }
}

// Helper function for styling
function isActive(page) {
    return props.currentPage === page;
}
</script>

<template>
    <nav aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-6 px-4">

        <!-- Left Side: Item Count Display -->
        <div class="text-sm text-gray-600">
            Showing <span class="font-semibold text-gray-800">{{ itemRangeStart }}</span>
            to <span class="font-semibold text-gray-800">{{ itemRangeEnd }}</span>
            of <span class="font-semibold text-gray-800">{{ totalItems }}</span> results
        </div>

        <!-- Right Side: Pagination Controls -->
        <div class="flex items-center gap-2">

            <!-- Previous Button -->
            <button @click="prevPage" :disabled="isFirstPage" :class="{
                'hover:bg-gray-100': !isFirstPage,
                'opacity-50 cursor-not-allowed': isFirstPage
            }" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors"
                aria-label="Go to previous page">
                <!-- Slot for custom "previous" icon/text -->
                <slot name="previous">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                            clip-rule="evenodd" />
                    </svg>
                </slot>
            </button>

            <!-- Page Number Buttons -->
            <div class="hidden sm:flex items-center gap-1">
                <template v-for="(page, index) in pages" :key="`page-${index}`">
                    <span v-if="page.name === '...'" class="px-2 py-2 text-sm font-medium text-gray-500">...</span>
                    <button v-else @click="changePage(page.name)" :class="[
                        'inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors w-10 h-10',
                        isActive(page.name)
                            ? 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
                            : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                    ]" :aria-current="isActive(page.name) ? 'page' : undefined">
                        {{ page.name }}
                    </button>
                </template>
            </div>

            <!-- Next Button -->
            <button @click="nextPage" :disabled="isLastPage" :class="{
                'hover:bg-gray-100': !isLastPage,
                'opacity-50 cursor-not-allowed': isLastPage
            }" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors"
                aria-label="Go to next page">
                <!-- Slot for custom "next" icon/text -->
                <slot name="next">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                            clip-rule="evenodd" />
                    </svg>
                </slot>
            </button>

            <!-- Go to Page Input -->
            <div v-if="totalPages > maxVisibleButtons" class="hidden lg:flex items-center gap-2">
                <span class="text-sm text-gray-600">Go to</span>
                <input type="number" :value="currentPage" @change="handleGoToPage" min="1" :max="totalPages"
                    class="w-16 rounded-md border-gray-300 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    aria-label="Go to page number" />
            </div>

        </div>
    </nav>
</template>
