<script setup>
import { ref, onMounted } from 'vue';
import { useImmunizationStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { Table, Paginate } from '@/Components'; // Assuming you have these
import { PlusIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';
import { useRouter, useRoute } from 'vue-router';
import useToast from '@/Utils/useToast';

const immunizationStore = useImmunizationStore();
const { records, isLoading, paginate } = storeToRefs(immunizationStore);
const { showToast } = useToast();
const router = useRouter();
const route = useRoute();

const currentPage = ref(route.query.page || 1);
const searchQuery = ref('');
const selectedVaccine = ref('');

const handlePageChange = (page) => {
    currentPage.value = page;
    immunizationStore.getRecords(currentPage.value, searchQuery.value);
    router.replace({ query: { page: currentPage.value } });
};

const handleSearch = () => {
    handlePageChange(1);
};

const deleteRecord = async (recordId) => {
    if (!confirm('Are you sure you want to delete this record?')) return;
    try {
        await immunizationStore.deleteRecord(recordId);
        showToast({ icon: 'success', title: 'Record deleted successfully' });
    } catch (error) {
        showToast({ icon: 'error', title: 'Failed to delete record' });
    }
};

onMounted(() => {
    immunizationStore.getRecords(currentPage.value);
});


const columns = [
    { key: "date_administered", label: "Date" },
    { key: "resident", label: "Resident Name" },
    { key: "vaccine_name", label: "Vaccine" },
    { key: "dose", label: "Dose" },
    { key: "administered_by", label: "Administered By" },
];
</script>

<template>
    <div class="bg-white p-6 shadow-lg rounded-lg">
        <div class="flex flex-col md:flex-row justify-between items-center border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Immunization Log</h2>
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <input type.text v-model="searchQuery" placeholder="Search by resident..."
                    class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200 w-48">
                <select v-model="selectedVaccine" class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200">
                    <option value="">All Vaccines</option>
                    <option>BCG</option>
                    <option>Hepatitis B</option>
                    <option>COVID-19 Booster</option>
                    <option>Influenza</option>
                </select>
                <router-link to="/health/immunizations/add"
                    class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2">
                    <PlusIcon class="w-5 h-5" />
                    Log New
                </router-link>
            </div>
        </div>

        <div v-if="isLoading" class="text-center py-10">Loading records...</div>

        <Table v-else :columns="columns" :rows="records">
            <template #cell(resident)="{ row }">
                <span v-if="row.resident">{{ row.resident.last_name }}, {{ row.resident.first_name }}</span>
            </template>
            <template #actions="{ row }">
                <!-- <button @click="deleteRecord(row.id)" class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                    Delete
                </button> -->
            </template>
        </Table>

        <Paginate @page-changed="handlePageChange" :totalPages="paginate.last_page" :currentPage="paginate.current_page"
            :totalItems="paginate.total" :itemsPerPage="paginate.per_page" class="mt-4" />
    </div>
</template>