<script setup>
import { onMounted, ref } from 'vue';
import { useClinicVisitStore } from '@/Stores';
import { Table, Paginate } from '@/Components';
import { storeToRefs } from 'pinia';
import useToast from '@/Utils/useToast';
import { useRouter, useRoute } from 'vue-router';
import { PlusIcon } from '@heroicons/vue/24/outline';

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

const handleSearch = () => handlePageChange(1);

onMounted(() => {
    clinicVisitStore.getVisits(currentPage.value);
});
</script>

<template>
    <div class="space-y-4 bg-white p-4 sm:p-6 shadow-lg rounded-lg">

        <!-- Header & Actions -->
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-4 mb-4 space-y-3 sm:space-y-0">
            <h2 class="text-2xl font-bold text-gray-800">Clinic Visits Log</h2>
            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2 space-y-2 sm:space-y-0 w-full sm:w-auto">
                <form @submit.prevent="handleSearch" class="flex w-full sm:w-auto">
                    <input v-model="searchQuery" type="text" placeholder="Search by resident..."
                        class="rounded-lg shadow-inner p-2 bg-gray-50 border border-gray-200 w-full sm:w-48" />
                </form>
                <router-link to="/health/clinic-visits/add"
                    class="bg-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-800 flex items-center gap-2 justify-center w-full sm:w-auto">
                    <PlusIcon class="w-5 h-5" />
                    Log New Visit
                </router-link>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div v-if="isLoading" class="flex justify-center items-center py-10">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-green-700"></div>
        </div>

        <!-- Mobile Cards -->
        <div v-else class="space-y-4 sm:hidden">
            <div v-for="visit in visits" :key="visit.id"
                class="bg-gray-50 p-4 rounded-lg shadow hover:shadow-md flex flex-col space-y-2">
                <p class="text-sm font-semibold text-gray-700">Date: <span class="font-normal">{{ new
                    Date(visit.visit_date).toLocaleDateString() }}</span></p>
                <p class="text-sm font-semibold text-gray-700">Resident: <span class="font-normal">{{ visit.resident ?
                    `${visit.resident.last_name}, ${visit.resident.first_name}` : 'N/A' }}</span></p>
                <p class="text-sm font-semibold text-gray-700">Reason: <span class="font-normal">{{
                    visit.reason_for_visit }}</span></p>
                <p class="text-sm font-semibold text-gray-700">Diagnosis: <span class="font-normal">{{ visit.diagnosis
                }}</span></p>
                <router-link :to="`/health/clinic-visits/edit/${visit.id}`"
                    class="text-green-700 font-semibold hover:underline text-sm mt-2">
                    View Details
                </router-link>
            </div>
        </div>

        <!-- Table for larger screens -->
        <div class="hidden sm:block">
            <Table :columns="[
                { key: 'visit_date', label: 'Date of Visit' },
                { key: 'resident', label: 'Resident Name' },
                { key: 'reason_for_visit', label: 'Reason' },
                { key: 'diagnosis', label: 'Diagnosis' }
            ]" :rows="visits">
                <template #cell(resident)="{ row }">
                    <span v-if="row.resident">{{ row.resident.last_name }}, {{ row.resident.first_name }}</span>
                    <span v-else class="text-gray-400">N/A</span>
                </template>

                <template #cell(visit_date)="{ row }">
                    {{ new Date(row.visit_date).toLocaleDateString("en-US", {
                        year: 'numeric', month: 'long', day:
                            'numeric'
                    }) }}
                </template>

                <template #actions="{ row }">
                    <router-link :to="`/health/clinic-visits/edit/${row.id}`"
                        class="text-green-700 px-2 py-1 rounded text-sm hover:underline">
                        View Details
                    </router-link>
                </template>
            </Table>
        </div>

        <!-- Pagination -->
        <Paginate @page-changed="handlePageChange" :totalPages="paginate.last_page" :currentPage="paginate.current_page"
            class="mt-4" />
    </div>
</template>
