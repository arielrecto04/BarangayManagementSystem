<script setup>
import { AuthLayout } from "@/Layouts";
import { Table } from '@/Components'
import { useResidentStore } from '@/Stores'
import { storeToRefs } from 'pinia';
import { onMounted } from "vue";
import useToast from '@/Utils/useToast';

const { showToast } = useToast();

const residentStore = useResidentStore();
const { residents , isLoading } = storeToRefs(residentStore);


const columns = [
    {
        key : "last_name" , label : "Last Name"
    },
    {
        key : "first_name" , label : "First Name"
    },
    {
        key : "birthday" , label : "Birthday"
    },
    {
        key : "age" , label : "Age"
    },
    {
        key : "gender" , label : "Gender"
    },
    {
        key : "address" , label : "Address"
    },
    {
        key : "contact_number" , label : "Contact Number"
    },
    {
        key : "family_member" , label : "Family Member"
    },
    {
        key : "emergency_contact" , label : "Emergency Contact"
    },
]



const deleteResident = async (residentId) => {
    try {
        await residentStore.deleteResident(residentId);
        showToast({ icon: 'success', title: 'Resident deleted successfully' });
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}


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



</template>
