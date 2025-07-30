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
    { key: "blotter_no", label: "Blotter ID" },
    { key: "title_case", label: "Title" },
    { key: "resident", label: "Resident" },
    { key: "status", label: "Status" },
    { key: "filing_date", label: "Date" },
    { key: "actions", label: "Actions", class: "text-center" },
];

const deleteBlotter = async (blotterId) => {
    try {
        await blotterStore.deleteBlotter(blotterId);
        showToast({ icon: 'success', title: 'Blotter deleted successfully' });
>>>>>>> cd442b6 (ListBlotter)
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}

<<<<<<< HEAD

onMounted(() => {
    residentStore.getResidents();
})



</script>

<template>

    <div class="flex flex-col gap-2">


        <h1 class="text-xl font-bold text-gray-600">List of Residents</h1>
        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>
        <Table v-else :columns="columns" :rows="residents">

            <template  #actions="{ row }">
                <router-link :to="`/residents/edit-resident/${row.id}`" class="bg-blue-500 text-white px-2 py-1 rounded">Edit</router-link>
                <button @click="deleteResident(row.id)" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
            </template>
        </Table>
    </div>



=======
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
                <div class="flex justify-center gap-2">
                    <router-link :to="`/blotter/edit-blotter/${row.id}`" class="bg-blue-500 text-white px-2 py-1 rounded">Edit</router-link>
                    <button @click="deleteBlotter(row.id)" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
                </div>
            </template>
        </Table>
    </div>
>>>>>>> cd442b6 (ListBlotter)
</template>
