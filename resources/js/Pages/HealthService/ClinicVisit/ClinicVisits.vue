<script setup>
import { onMounted, ref } from 'vue';
import { useClinicVisitStore } from '@/Stores';
import { Table, Paginate } from '@/Components';
import { storeToRefs } from 'pinia';
import useToast from '@/Utils/useToast';
import { useRouter, useRoute } from 'vue-router';
import { PlusIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';

const { showToast } = useToast();
const router = useRouter();
const route = useRoute();

const clinicVisitStore = useClinicVisitStore();
const { visits, isLoading, paginate } = storeToRefs(clinicVisitStore);

const currentPage = ref(route.query.page || 1);
const searchQuery = ref('');

const handlePageChange = (page) => {
    currentPage.value = page;
    clinicVisitStore.getVisits(currentPage.value, searchQuery.value);
    router.replace({ query: { page: currentPage.value } });
};

const handleSearch = () => {
    handlePageChange(1);
}

// --- 1. ADD THIS DELETE FUNCTION ---
const deleteVisit = async (visitId) => {
    if (!confirm('Are you sure you want to delete this visit record? This action cannot be undone.')) {
        return;
    }

    try {
        await clinicVisitStore.deleteClinicVisit(visitId);
        showToast({ icon: 'success', title: 'Visit record deleted successfully' });
        // Optional: Refetch the current page to ensure list is accurate
        // clinicVisitStore.getVisits(currentPage.value);
    } catch (error) {
        showToast({ icon: 'error', title: error.message || 'Failed to delete record' });
    }
}


onMounted(() => {
    clinicVisitStore.getVisits(currentPage.value);
});

const columns = [
    { key: "visit_date", label: "Date of Visit" },
    { key: "resident", label: "Resident Name" },
    { key: "reason_for_visit", label: "Reason" },
    { key: "diagnosis", label: "Diagnosis" },
];

</script>

<template>
    <div class="bg-white p-6 shadow-lg rounded-lg">
        <div class="flex flex-col md:flex-row justify-between items-center border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Clinic Visits Log</h2>
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <form @submit.prevent="handleSearch">
                    <input v-model="searchQuery" type="text" placeholder="Search and press Enter..."
                        class="form-input w-48 rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200">
                </form>

                <router-link to="/health/clinic-visits/add"
                    class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2">
                    <PlusIcon class="w-5 h-5" />
                    Log New Visit
                </router-link>
            </div>
        </div>
        <div v-if="isLoading" class="flex justify-center items-center py-10">
            <div class="animate-spin rounded-full h-24 w-24 border-b-2 border-green-700"></div>
        </div>
        <Table v-else :columns="columns" :rows="visits">
            <template #cell(resident)="{ row }">
                <span v-if="row.resident">
                    {{ row.resident.last_name }}, {{ row.resident.first_name }}
                </span>
                <span v-else class="text-gray-400">N/A</span>
            </template>

            <template #cell(visit_date)="{ row }">
                {{ new Date(row.visit_date).toLocaleDateString("en-US", {
                    year: 'numeric', month: 'long', day: 'numeric'
                }) }}
            </template>

            <template #actions="{ row }">
                <router-link :to="`/health/clinic-visits/edit/${row.id}`"
                    class="text-green-700 px-2 py-1 rounded text-sm hover:underline">
                    View Details
                </router-link>
                <!-- <button @click="deleteVisit(row.id)" class="bg-red-500 text-white px-2 py-1 rounded text-sm ml-2">
                    Delete
                </button> -->
            </template>
        </Table>
        <Paginate @page-changed="handlePageChange" :totalPages="paginate.last_page" :currentPage="paginate.current_page"
            class="mt-4" />
    </div>
</template>