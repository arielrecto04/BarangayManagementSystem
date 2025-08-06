<script setup>
import { AuthLayout } from "@/Layouts";
import { Table, Paginate } from '@/Components'
import { useResidentStore } from '@/Stores'
import { storeToRefs } from 'pinia';
import { onMounted, ref, watch } from "vue";
import useToast from '@/Utils/useToast';
import { useRouter, useRoute } from 'vue-router';

const { showToast } = useToast();
const router = useRouter();
const route = useRoute();
const residentStore = useResidentStore();
const { residents, isLoading, paginate } = storeToRefs(residentStore);

// Parse the page from route query, ensuring it's a number and defaults to 1
const currentPage = ref(parseInt(route.query.page) || 1);

const handlePageChange = (page) => {
    currentPage.value = page;

    // Get current filters from route
    const filters = {
        search: route.query.search,
        age_range: route.query.age_range,
        gender: route.query.gender
    };

    residentStore.getResidents(currentPage.value, filters);

    router.replace({
        query: {
            ...route.query,
            page: currentPage.value
        }
    });
}

const columns = [
    {
        key: "avatar", label: "Image"
    },
    {
        key: "resident_number", label: "Resident Number"
    },
    {
        key: "last_name", label: "Last Name"
    },
    {
        key: "first_name", label: "First Name"
    },
    {
        key: "middle_name", label: "Middle Name"
    },
    {
        key: "last_name", label: "Last Name"
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
        key: "family_member", label: "Family Member"
    },
    {
        key: "emergency_contact", label: "Emergency Contact"
    },
    {
        key: "email", label: "Email"
    }
]



const deleteResident = async (residentId) => {
    try {
        await residentStore.deleteResident(residentId);
        showToast({ icon: 'success', title: 'Resident deleted successfully' });

        // After deletion, check if we need to go to previous page
        // if current page becomes empty
        if (residents.value.length === 0 && currentPage.value > 1) {
            handlePageChange(currentPage.value - 1);
        }
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}

// Watch for route query changes (e.g., browser back/forward, filters)
watch(() => route.query, (newQuery) => {
    const page = parseInt(newQuery.page) || 1;
    const filters = {
        search: newQuery.search,
        age_range: newQuery.age_range,
        gender: newQuery.gender
    };

    if (page !== currentPage.value) {
        currentPage.value = page;
    }

    residentStore.getResidents(currentPage.value, filters);
}, { deep: true });

onMounted(() => {
    // Get filters from route
    const filters = {
        search: route.query.search,
        age_range: route.query.age_range,
        gender: route.query.gender
    };

    // Load the residents for the current page from the route
    residentStore.getResidents(currentPage.value, filters);
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold text-gray-600">List of Residents</h1>

        <div v-if="isLoading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
        </div>
        <Table v-else :columns="columns" :rows="residents" :searchable="false" :selectable="false">
            <template #cell(avatar)="{ row }">
                <img :src="row.avatar" alt="image" srcset="" class="w-10 h-10 rounded-full">
            </template>
            <template #cell(middle_name)="{ row }">
                {{ row.middle_name }}
            </template>
            <template #cell(contact_person)="{ row }">
                {{ row.contact_person }}
            </template>
            <template #actions="{ row }">
                <router-link :to="`/residents/edit-resident/${row.id}`"
                    class="bg-blue-500 text-white px-2 py-1 rounded">Edit</router-link>
                <button @click="deleteResident(row.id)" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
            </template>
        </Table>
        <Paginate @page-changed="handlePageChange" :maxVisibleButtons="5" :totalPages="paginate.last_page"
            :totalItems="paginate.total" :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />
    </div>
</template>
