<script setup>
import { AuthLayout } from "@/Layouts";
import { Table } from '@/Components'
import { useBlotterStore } from '@/Stores'
import { storeToRefs } from 'pinia';
import { onMounted } from "vue";
import useToast from '@/Utils/useToast';

const { showToast } = useToast();

const blotterStore = useBlotterStore();
const { blotters, isLoading } = storeToRefs(blotterStore);

const columns = [
    { key: "blotter_no", label: "Blotter No" },
    { key: "title_case", label: "Title Case" },
    { key: "complainants", label: "Complainants" },
    { key: "respondents", label: "Respondents" },
    { key: "filing_date", label: "Filing Date" },
];

const deleteBlotter = async (blotterId) => {
    try {
        await blotterStore.deleteBlotter(blotterId);
        showToast({ icon: 'success', title: 'Blotter deleted successfully' });
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}

onMounted(() => {
    blotterStore.getBlotters();
})
</script>

<template>
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold text-gray-600">List of Blotters</h1>
        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>
        <Table v-else :columns="columns" :rows="blotters">
            <template #actions="{ row }">
                <router-link :to="`/blotters/edit-blotter/${row.id}`" class="bg-blue-500 text-white px-2 py-1 rounded">Edit</router-link>
                <button @click="deleteBlotter(row.id)" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
            </template>
        </Table>
    </div>
</template>