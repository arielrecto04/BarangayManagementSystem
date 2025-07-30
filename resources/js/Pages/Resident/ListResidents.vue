<script setup>
import { AuthLayout } from "@/Layouts";
import { Table, Paginate } from '@/Components'
import { useResidentStore } from '@/Stores'
import { storeToRefs } from 'pinia';
import { onMounted, ref } from "vue";
import useToast from '@/Utils/useToast';
import { useRouter, useRoute } from 'vue-router';
const { showToast } = useToast();
const router = useRouter();
const route = useRoute();
const residentStore = useResidentStore();
const { residents, isLoading, paginate } = storeToRefs(residentStore);


const currentPage = ref(route.query.page || 1);

const handlePageChange = (page) => {

    currentPage.value = page;
    residentStore.getResidents(currentPage.value);

    router.replace({
        query: {
            page: currentPage.value
        }
    })
}


const columns = [
    {
        key: "avatar", label: "Image"
    },
    {
        key: "last_name", label: "Last Name"
    },
    {
        key: "first_name", label: "First Name"
    },
    {
        key: "birthday", label: "Birthday"
    },
    {
        key: "age", label: "Age"
    },
    {
        key: "gender", label: "Gender"
    },
    {
        key: "address", label: "Address"
    },
    {
        key: "contact_number", label: "Contact Number"
    },
    {
        key: "family_member", label: "Family Member"
    },
    {
        key: "emergency_contact", label: "Emergency Contact"
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
        <Table v-else :columns="columns" :rows="residents" :searchable="false" :selectable="false" >
            <template #cell(avatar)="{ row }">
                <img :src="row.avatar" alt="image" srcset="" class="w-10 h-10 rounded-full">
            </template>

            <template #actions="{ row }">
                <router-link :to="`/residents/edit-resident/${row.id}`"
                    class="bg-blue-500 text-white px-2 py-1 rounded">Edit</router-link>
                <button @click="deleteResident(row.id)" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
            </template>
        </Table>
        <Paginate  @page-changed="handlePageChange" :maxVisibleButtons="5" :totalPages="paginate.last_page" :totalItems="paginate.total"
            :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />
    </div>



</template>
