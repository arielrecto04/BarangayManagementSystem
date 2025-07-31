<script setup>
import { AuthLayout } from "@/Layouts";
import { Table } from '@/Components'
import { useBlotterStore } from '@/Stores'
import { storeToRefs } from 'pinia';
import { onMounted, ref } from "vue";
import useToast from '@/Utils/useToast';

const { showToast } = useToast();

const blotterStore = useBlotterStore();
const { blotters, isLoading } = storeToRefs(blotterStore);

// Format date to "Month Day, Year"
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const columns = [
    { key: "blotter_no", label: "Blotter ID" },
    { key: "title_case", label: "Title" },
    { key: "complainants_id", label: "Complainant" },
    { key: "respondents_id", label: "Respondent" },
    { key: "status", label: "Status" },
    {
        key: "filing_date",
        label: "Date",
        formatter: (value) => formatDate(value)
    },
];

// Delete a blotter
const deleteBlotter = async (blotterId) => {
    try {
        await blotterStore.deleteBlotter(blotterId);
        showToast({ icon: 'success', title: 'Blotter deleted successfully' });
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
};

// View modal logic
const showModal = ref(false);
const selectedBlotter = ref(null);

const openModal = (blotter) => {
    selectedBlotter.value = blotter;
    showModal.value = true;
};

const closeModal = () => {
    selectedBlotter.value = null;
    showModal.value = false;
};

onMounted(() => {
    blotterStore.getBlotters();
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold text-gray-600">List of Blotters</h1>

        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>

        <Table v-else :columns="columns" :rows="blotters">
            <template #actions="{ row }">
                <div class="flex justify-center gap-2">
                    <router-link :to="`/blotter/edit-blotter/${row.id}`" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">Edit</router-link>
                    <button @click="deleteBlotter(row.id)" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Delete</button>
                    <button @click="openModal(row)" class="bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600">View</button>
                </div>
            </template>
        </Table>

        <!-- View Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/20"
        >
            <div class="relative bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl">
                <button
                    @click="closeModal"
                    class="absolute top-2 right-3 text-gray-600 hover:text-black text-2xl"
                >
                    &times;
                </button>
                <h2 class="text-lg font-bold mb-4 text-gray-700">Blotter Details</h2>
                <!-- View Modal -->
<div
    v-if="showModal"
    class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/20"
>
    <div class="relative bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <button
            @click="closeModal"
            class="absolute top-2 right-3 text-gray-600 hover:text-black text-2xl"
        >
            &times;
        </button>

        <h2 class="text-lg font-bold mb-4 text-gray-700">Blotter Details</h2>

        <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
            <div><strong>Title:</strong> {{ selectedBlotter?.title_case }}</div>
            <div><strong>Blotter ID:</strong> {{ selectedBlotter?.blotter_no }}</div>
            <div><strong>Blotter Type:</strong> {{ selectedBlotter?.blotter_type }}</div>
            <div><strong>Nature of Case:</strong> {{ selectedBlotter?.nature_of_case }}</div>
            <div><strong>Filing Date:</strong> {{ formatDate(selectedBlotter?.filing_date) }}</div>
            <div><strong>Place:</strong> {{ selectedBlotter?.place }}</div>
            <div><strong>Date of Incident:</strong> {{ formatDate(selectedBlotter?.datetime_of_incident) }}</div>
            <div><strong>Complainant:</strong> {{ selectedBlotter?.complainants_id }}</div>
            <div><strong>Respondent:</strong> {{ selectedBlotter?.respondents_id }}</div>
            <div><strong>Status:</strong> {{ selectedBlotter?.status }}</div>
            <div><strong>Date:</strong> {{ formatDate(selectedBlotter?.filing_date) }}</div>

            <!-- Description Full Width and Scrollable -->
            <div class="col-span-2">
                <strong>Description:</strong>
                <div
                    class="mt-1 border border-gray-300 rounded-md p-3 bg-gray-50 text-gray-700 break-words overflow-y-auto"
                    style="height: 8rem; white-space: pre-wrap;"
                >
                    {{ selectedBlotter?.description }}
                </div>

            </div>
        </div>
    </div>
</div>
   </div> 
        </div>
    </div>
</template>
