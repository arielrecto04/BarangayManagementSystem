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

const showModal = ref(false);
const selectedBlotter = ref(null);

const openModal = (blotter) => {
    selectedBlotter.value = blotter;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedBlotter.value = null;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const columns = [
    { key: "blotter_no", label: "Blotter ID" },
    { key: "title_case", label: "Title" },
    { key: "complainants_id", label: "Complainant ID" },
    { key: "respondents_id", label: "Respondent ID" },
    { key: "status", label: "Status" },
    { 
        key: "filing_date", 
        label: "Date",
        formatter: (value) => formatDate(value)
    }
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
                <div class="flex justify-center gap-2">
                    <router-link :to="`/blotter/edit-blotter/${row.id}`" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">Edit</router-link>
                    <button @click="deleteBlotter(row.id)" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Delete</button>
                    <button @click="openModal(row)" class="bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600">View</button>
                </div>
            </template>
        </Table>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="relative z-60 bg-white rounded-xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
                <!-- Close Button -->
                <button @click="closeModal" class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">&times;</button>

                <!-- Modal Title -->
                <h2 class="text-lg font-bold mb-6 text-gray-700">Blotter Details</h2>

                <!-- Grid Layout for Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
                    <div>
                        <strong>Blotter No:</strong><br />
                        {{ selectedBlotter?.blotter_no }}
                    </div>
                    <div>
                        <strong>Filing Date:</strong><br />
                        {{ formatDate(selectedBlotter?.filing_date) }}
                    </div>
                    <div>
                        <strong>Title Case:</strong><br />
                        {{ selectedBlotter?.title_case }}
                    </div>
                    <div>
                        <strong>Nature of Case:</strong><br />
                        {{ selectedBlotter?.nature_of_case }}
                    </div>
                    <div>
                        <strong>Complainant ID:</strong><br />
                        {{ String(selectedBlotter?.complainants_id).padStart(4, '0') }}
                    </div>
                    <div>
                        <strong>Respondent ID:</strong><br />
                        {{ String(selectedBlotter?.respondents_id).padStart(4, '0') }}
                    </div>
                    <div>
                        <strong>Place:</strong><br />
                        {{ selectedBlotter?.place }}
                    </div>
                    <div>
                        <strong>Date/Time of Incident:</strong><br />
                        {{ formatDate(selectedBlotter?.datetime_of_incident) }}
                    </div>
                    <div>
                        <strong>Status:</strong><br />
                        {{ selectedBlotter?.status }}
                    </div>
                    <div>
                        <strong>Barangay Case No:</strong><br />
                        {{ selectedBlotter?.barangay_case_no }}
                    </div>
                    <div class="col-span-2">
                        <strong>Description:</strong><br />
                        <textarea
                            readonly
                            class="w-full h-40 mt-1 p-2 border rounded bg-gray-50 resize-none overflow-y-auto text-sm leading-relaxed"
                            :value="selectedBlotter?.description"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.multiselect {
  min-height: auto;
}

.multiselect__tags {
  min-height: 44px;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  padding: 0.5rem;
}

.multiselect__input,
.multiselect__single {
  font-size: 1rem;
  margin-bottom: 0;
  padding: 0;
}

.multiselect__placeholder {
  margin-bottom: 0;
  padding-top: 0;
  padding-left: 0;
}

.multiselect__option--highlight {
  background: #3b82f6;
}

.multiselect__option--highlight::after {
  background: #3b82f6;
}
</style>
