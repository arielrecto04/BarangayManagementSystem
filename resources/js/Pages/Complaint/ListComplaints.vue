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
const statusUpdates = ref({});


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


const updateStatus = async (complaintId) => {
  const updatedStatus = statusUpdates.value[complaintId];
  if (!updatedStatus) {
    showToast({ icon: 'warning', title: 'Please select a status.' });
    return;
  }
  try {
    await complaintStore.updateComplaint(complaintId, { status: updatedStatus });
    showToast({ icon: 'success', title: 'Status updated successfully' });
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
  { key: "filing_date", label: "Filing Date" },
  { key: "status", label: "Status" }
];

onMounted(() => {
  const page = currentPage.value;
  const status = route.query.status;

  if (status) {
    complaintStore.getComplaints(page, status); // assumes getComplaints supports filter
  } else {
    complaintStore.getComplaints(page);
  }
});

watch(complaints, (newComplaints) => {
  newComplaints.forEach(complaint => {
    if (!(complaint.id in statusUpdates.value)) {
      statusUpdates.value[complaint.id] = complaint.status || "";
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

        <select v-model="statusUpdates[row.id]" class="ml-2 border rounded px-2 py-1 text-sm">
          <option disabled value="">Update</option>
          <option value="Open">Open</option>
          <option value="In Progress">In Progress</option>
          <option value="Resolved">Resolved</option>
        </select>

        <button
          @click="updateStatus(row.id)"
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
  <div
    class="relative z-60 bg-white rounded-xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto"
  >
    <!-- Close Button -->
    <button
      @click="closeModal"
      class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl"
    >
      &times;
    </button>

    <!-- Modal Title -->
    <h2 class="text-lg font-bold mb-6 text-gray-700">Complaint Details</h2>

    <!-- Grid Layout for Fields -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
      <div>
        <strong>Complainant:</strong><br />
        {{ selectedComplaint?.complainant_name }}
      </div>
      <div>
        <strong>Respondent:</strong><br />
        {{ selectedComplaint?.respondent_name }}
      </div>
      <div>
        <strong>Case No:</strong><br />
        {{ selectedComplaint?.case_no }}
      </div>
      <div>
        <strong>Title:</strong><br />
        {{ selectedComplaint?.title }}
      </div>

      <!-- Description with scroll -->
   <div class="md:col-span-2">
  <strong>Description:</strong>
  <textarea
    readonly
    class="w-full h-40 mt-1 p-2 border rounded bg-gray-50 resize-none overflow-y-auto text-sm leading-relaxed"
  >{{ selectedComplaint?.description }}</textarea>
</div>

<!-- Resolution -->
   <div class="md:col-span-2">
  <strong>Resolution:</strong>
  <textarea
    readonly
    class="w-full h-40 mt-1 p-2 border rounded bg-gray-50 resize-none overflow-y-auto text-sm leading-relaxed"
  >{{ selectedComplaint?.resolution }}</textarea>
</div>
<div>
        <strong>Date:</strong><br />
        {{ selectedComplaint?.date }}
      </div>
      <div>
        <strong>Filing Date:</strong><br />
        {{ selectedComplaint?.filing_date }}
      </div>
      <div>
        <strong>Complainant ID:</strong><br />
        ID: {{ selectedComplaint?.complainant_id }}
      </div>
      <div>
        <strong>Respondent ID:</strong><br />
        ID: {{ selectedComplaint?.respondent_id }}
      </div>
      <div>
  <strong>Status:</strong><br />
  {{ selectedComplaint?.status || 'N/A' }}
</div>

    </div>
  </div>
</div>
</div>
</template>
