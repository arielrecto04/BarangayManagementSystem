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
  </div>
</template>
