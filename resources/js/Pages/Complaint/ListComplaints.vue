<script setup>
import { AuthLayout } from "@/Layouts";
import { Table, Paginate } from '@/Components';
import { useComplaintStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, ref } from "vue";
import useToast from '@/Utils/useToast';
import { useRoute, useRouter } from 'vue-router';
import { watch } from 'vue';

const { showToast } = useToast();
const route = useRoute();
const router = useRouter();

const complaintStore = useComplaintStore();
const { complaints, isLoading, paginate } = storeToRefs(complaintStore);

const currentPage = ref(route.query.page || 1);
const resolutionUpdates = ref({});

const handlePageChange = (page) => {
  currentPage.value = page;
  complaintStore.getComplaints(page);
  router.replace({ query: { page: page } });
};

const showModal = ref(false);
const selectedComplaint = ref(null);

const openModal = (complaint) => {
  selectedComplaint.value = complaint;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedComplaint.value = null;
};


const updateResolution = async (complaintId) => {
  const updatedResolution = resolutionUpdates.value[complaintId];
  if (!updatedResolution) {
    showToast({ icon: 'warning', title: 'Please select a resolution status.' });
    return;
  }
  try {
    await complaintStore.updateComplaint(complaintId, { resolution: updatedResolution });
    showToast({ icon: 'success', title: 'Resolution updated successfully' });
    complaintStore.getComplaints(currentPage.value);
  } catch (error) {
    showToast({ icon: 'error', title: error.message });
  }
};

const deleteComplaint = async (complaintId) => {
  try {
    await complaintStore.deleteComplaint(complaintId);
    showToast({ icon: 'success', title: 'Complaint deleted successfully' });
    complaintStore.getComplaints(currentPage.value);
  } catch (error) {
    showToast({ icon: 'error', title: error.message });
  }
};

const columns = [
  { key: "complainant_name", label: "Complainant" },
  { key: "respondent_name", label: "Respondent" },
  { key: "case_no", label: "Case No" },
  { key: "title", label: "Title" },
  { key: "description", label: "Description" },
  { key: "resolution", label: "Resolution" },
  { key: "date", label: "Date" },
  { key: "filing_date", label: "Filing Date" }
];

onMounted(() => {
  complaintStore.getComplaints(currentPage.value);
});

watch(complaints, (newComplaints) => {
  newComplaints.forEach(complaint => {
    if (!(complaint.id in resolutionUpdates.value)) {
      resolutionUpdates.value[complaint.id] = complaint.resolution || "";
    }
  });
});
</script>

<template>
  <div class="flex flex-col gap-2">
    <h1 class="text-xl font-bold text-gray-600">List of Complaints</h1>

    <div v-if="isLoading" class="flex justify-center items-center">
      <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
    </div>

    <Table v-else :columns="columns" :rows="complaints">
      <template #actions="{ row }">
        <router-link :to="`/complaints/edit-complaint/${row.id}`" class="bg-blue-500 text-white px-2 py-1 rounded">
          Edit
        </router-link>

        <button @click="deleteComplaint(row.id)" class="bg-red-500 text-white px-2 py-1 rounded ml-2">
          Delete
        </button>

        <select v-model="resolutionUpdates[row.id]" class="ml-2 border rounded px-2 py-1 text-sm">
          <option disabled value="">Update</option>
          <option value="Open">Open</option>
          <option value="In Progress">In Progress</option>
          <option value="Resolved">Resolved</option>
        </select>

        <button
          @click="updateResolution(row.id)"
          class="bg-green-500 text-white px-2 py-1 rounded ml-2">
          Apply
        </button>

        <!-- View Button -->
<button
  @click="openModal(row)"
  class="bg-gray-500 text-white px-2 py-1 rounded ml-2"
>
  View
</button>

      </template>
    </Table>

    <Paginate
      v-if="paginate && !isLoading"
      @page-changed="handlePageChange"
      :maxVisibleButtons="5"
      :totalPages="paginate.last_page"
      :totalItems="paginate.total"
      :currentPage="paginate.current_page"
      :itemsPerPage="paginate.per_page"
    />

    <!-- Modal -->
<div
  v-if="showModal"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
>
  <div class="relative z-60 bg-white rounded-xl p-6 w-full max-w-2xl shadow-xl">
    <button
      @click="closeModal"
      class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl"
    >
      &times;
    </button>
    <h2 class="text-lg font-bold mb-4 text-gray-700">Complaint Details</h2>

    <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
      <div><strong>Complainant:</strong> {{ selectedComplaint?.complainant_name }}</div>
      <div><strong>Respondent:</strong> {{ selectedComplaint?.respondent_name }}</div>
      <div><strong>Case No:</strong> {{ selectedComplaint?.case_no }}</div>
      <div><strong>Title:</strong> {{ selectedComplaint?.title }}</div>
      <div class="col-span-2 max-h-40 overflow-x-auto whitespace-pre-line"><strong>Description:</strong><br />{{ selectedComplaint?.description }}</div>
      <div><strong>Resolution:</strong> {{ selectedComplaint?.resolution || 'N/A' }}</div>
      <div><strong>Date:</strong> {{ selectedComplaint?.date }}</div>
      <div><strong>Filing Date:</strong> {{ selectedComplaint?.filing_date }}</div>
      <div><strong>Complainant ID:</strong> (ID: {{ selectedComplaint?.complainant_id }})</div>
      <div><strong>Respondent ID:</strong> (ID: {{ selectedComplaint?.respondent_id }})</div>

    </div>
  </div>
</div>

  </div>
</template>
