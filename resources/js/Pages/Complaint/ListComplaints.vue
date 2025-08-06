<script setup>
import { AuthLayout } from "@/Layouts";
import { Table, Paginate } from '@/Components';
import { useComplaintStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, ref } from "vue";
import useToast from '@/Utils/useToast';
import { useRoute, useRouter } from 'vue-router';
import { watch } from 'vue';
// Import the print template component
import ComplaintPrintTemplate from './ComplaintPrintTemplate.vue';

// Format date and time
const formatDateTime = (isoString) => {
  if (!isoString) return 'N/A';

  const options = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  };

  return new Date(isoString).toLocaleString('en-US', options);
};

// Helper function to check if supporting documents exist and are valid
const getSupportingDocuments = (complaint) => {
  if (!complaint?.supporting_documents) return [];

  // Handle both array and string cases
  if (Array.isArray(complaint.supporting_documents)) {
    // New format: array of objects with 'path' and 'name'
    return complaint.supporting_documents.filter(doc => {
      // Handle new format (objects with path and name)
      if (typeof doc === 'object' && doc.path && doc.name) {
        return true;
      }
      // Handle old format (just strings)
      if (typeof doc === 'string') {
        return true;
      }
      return false;
    });
  }

  // If it's a string (JSON), try to parse it
  if (typeof complaint.supporting_documents === 'string') {
    try {
      const parsed = JSON.parse(complaint.supporting_documents);
      return Array.isArray(parsed) ? parsed.filter(doc => {
        if (typeof doc === 'object' && doc.path && doc.name) {
          return true;
        }
        if (typeof doc === 'string') {
          return true;
        }
        return false;
      }) : [];
    } catch (e) {
      console.warn('Failed to parse supporting documents:', e);
      return [];
    }
  }

  return [];
};

// Helper function to get filename - handles both old and new formats
const getFileName = (doc) => {
  // New format: object with name property
  if (typeof doc === 'object' && doc.name) {
    return doc.name;
  }
  // Old format: just file path string
  if (typeof doc === 'string') {
    return doc.split('/').pop() || 'Unknown file';
  }
  return 'Unknown file';
};

// Helper function to get file path - handles both old and new formats
const getFilePath = (doc) => {
  // New format: object with path property
  if (typeof doc === 'object' && doc.path) {
    return doc.path;
  }
  // Old format: just file path string
  if (typeof doc === 'string') {
    return doc;
  }
  return '';
};

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

// Print modal states
const showPrintModal = ref(false);
const selectedPrintComplaint = ref(null);

const openModal = (complaint) => {
  selectedComplaint.value = complaint;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedComplaint.value = null;
};

// Print modal functions
const openPrintModal = (complaint) => {
  selectedPrintComplaint.value = complaint;
  showPrintModal.value = true;
};

const closePrintModal = () => {
  showPrintModal.value = false;
  selectedPrintComplaint.value = null;
};

const handlePrint = () => {
  showToast({ icon: 'success', title: 'Print dialog opened successfully' });
};

const emit = defineEmits(['status-updated']);

const updateStatus = async (complaintId) => {
  const updatedStatus = statusUpdates.value[complaintId];
  if (!updatedStatus) {
    showToast({ icon: 'warning', title: 'Please select a status.' });
    return;
  }
  try {
    await complaintStore.updateComplaint(complaintId, { status: updatedStatus });
    const index = complaints.value.findIndex(c => c.id === complaintId);
    if (index !== -1) {
      complaints.value[index].status = updatedStatus;
    }
    showToast({ icon: 'success', title: 'Status updated successfully' });
  } catch (error) {
    showToast({ icon: 'error', title: error.message });
  }
};

const deleteComplaint = async (complaintId) => {
  if (confirm('Are you sure you want to delete this complaint?')) {
    try {
      await complaintStore.deleteComplaint(complaintId);
      showToast({ icon: 'success', title: 'Complaint deleted successfully' });
      complaintStore.getComplaints(currentPage.value);
    } catch (error) {
      showToast({ icon: 'error', title: error.message });
    }
  }
};

const columns = [
  { key: "complainant_name", label: "Complainant" },
  { key: "respondent_name", label: "Respondent" },
  { key: "case_no", label: "Case No" },
  { key: "title", label: "Title" },
  { key: "formatted_filing_date", label: "Filing Date" },
  { key: "status", label: "Status" }
];

onMounted(() => {
  const page = currentPage.value;
  const status = route.query.status;

  if (status) {
    complaintStore.getComplaints(page, status);
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

    <Table v-else :columns="columns" :rows="complaints.map(c => ({
      ...c,
      formatted_filing_date: formatDateTime(c.filing_date)
    }))">
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

        <button @click="updateStatus(row.id)" class="bg-green-500 text-white px-2 py-1 rounded ml-2">
          Apply
        </button>

        <!-- View Button -->
        <button @click="openModal(row)" class="bg-gray-500 text-white px-2 py-1 rounded ml-2">
          View
        </button>

        <!-- Print Button -->
        <button @click="openPrintModal(row)" class="bg-purple-500 text-white px-2 py-1 rounded ml-2">
          Print
        </button>

      </template>
    </Table>

    <Paginate v-if="paginate && !isLoading" @page-changed="handlePageChange" :maxVisibleButtons="5"
      :totalPages="paginate.last_page" :totalItems="paginate.total" :currentPage="paginate.current_page"
      :itemsPerPage="paginate.per_page" />

    <!-- View Details Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="relative z-60 bg-white rounded-xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
        <!-- Close Button -->
        <button @click="closeModal" class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">
          &times;
        </button>

        <!-- Modal Title -->
        <h2 class="text-lg font-bold mb-6 text-gray-700">Complaint Details</h2>

        <!-- Grid Layout for Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
          <!-- Complaint Name -->
          <div>
            <strong>Complainant:</strong><br />
            {{ selectedComplaint?.complainant_name || 'N/A' }}
          </div>
          <!-- Respondent Name -->
          <div>
            <strong>Respondent:</strong><br />
            {{ selectedComplaint?.respondent_name || 'N/A' }}
          </div>
          <!-- Nature of Complaint -->
          <div>
            <strong>Nature of Complaint:</strong><br />
            {{ selectedComplaint?.nature_of_complaint || 'N/A' }}
          </div>
          <!-- Location of Incident -->
          <div>
            <strong>Location of Incident:</strong><br />
            {{ selectedComplaint?.incident_location || 'N/A' }}
          </div>
          <!-- Case Number -->
          <div>
            <strong>Case Number:</strong><br />
            {{ selectedComplaint?.case_no || 'N/A' }}
          </div>
          <!-- Title -->
          <div>
            <strong>Title:</strong><br />
            {{ selectedComplaint?.title || 'N/A' }}
          </div>

          <!-- Description with scroll -->
          <div class="md:col-span-2">
            <strong>Description:</strong>
            <textarea readonly
              class="w-full h-40 mt-1 p-2 border rounded bg-gray-50 resize-none overflow-y-auto text-sm leading-relaxed">{{
                selectedComplaint?.description || 'N/A' }}</textarea>
          </div>

          <!-- Resolution -->
          <div class="md:col-span-2">
            <strong>Resolution:</strong>
            <textarea readonly
              class="w-full h-40 mt-1 p-2 border rounded bg-gray-50 resize-none overflow-y-auto text-sm leading-relaxed">{{
                selectedComplaint?.resolution || 'N/A' }}</textarea>
          </div>
          <!-- Date and Time of Incident -->
          <div>
            <strong>Date & Time of Incident:</strong><br />
            {{ formatDateTime(selectedComplaint?.incident_datetime) }}
          </div>
          <!-- Filing data and time -->
          <div>
            <strong>Filing Date & Time:</strong><br />
            {{ formatDateTime(selectedComplaint?.filing_date) }}
          </div>
          <!-- Complainant ID -->
          <div>
            <strong>Complainant ID:</strong><br />
            ID: {{ selectedComplaint?.complainant_id || 'N/A' }}
          </div>
          <!-- Respondent ID -->
          <div>
            <strong>Respondent ID:</strong><br />
            ID: {{ selectedComplaint?.respondent_id || 'N/A' }}
          </div>
          <!-- Witness -->
          <div>
            <strong>Witness(es):</strong><br />
            {{ selectedComplaint?.witness || 'N/A' }}
          </div>
          <!-- Status -->
          <div>
            <strong>Status:</strong><br />
            {{ selectedComplaint?.status || 'N/A' }}
          </div>
          <!-- Supporting Documents - FIXED -->
          <div class="md:col-span-2">
            <strong>Supporting Documents:</strong><br />
            <div v-if="getSupportingDocuments(selectedComplaint).length > 0">
              <ul class="list-disc pl-5">
                <li v-for="(doc, index) in getSupportingDocuments(selectedComplaint)" :key="index">
                  <a :href="`/storage/${getFilePath(doc)}`" target="_blank" class="text-blue-500 hover:underline">
                    {{ getFileName(doc) }}
                  </a>
                </li>
              </ul>
            </div>
            <div v-else class="text-gray-500 italic">
              No supporting documents available
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Print Template Modal -->
    <ComplaintPrintTemplate v-if="showPrintModal" :complaint="selectedPrintComplaint" @close="closePrintModal"
      @print="handlePrint" />
  </div>
</template>