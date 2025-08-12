<script setup>
import { AuthLayout } from "@/Layouts";
import { Table, Paginate } from '@/Components';
import { useComplaintStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref, nextTick } from "vue";
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
const menuPosition = ref({ top: 0, left: 0 });
const teleportMenuRowId = ref(null);

// Burger menu state
const openMenus = ref({});

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

// FIXED: Better menu toggle with proper close functionality
const toggleMenu = async (event, complaintId) => {
  // If clicking the same menu that's already open, close it
  if (teleportMenuRowId.value === complaintId) {
    teleportMenuRowId.value = null;
    return;
  }

  // Close any existing menu first
  if (teleportMenuRowId.value !== null) {
    teleportMenuRowId.value = null;
    // Wait for DOM to update before opening new menu
    await nextTick();
  }

  const rect = event.currentTarget.getBoundingClientRect();
  const dropdownWidth = 192; // Tailwind 'w-48' approx width in px
  const viewportWidth = window.innerWidth;
  const scrollX = window.scrollX;

  let leftPosition = rect.left + scrollX;

  // Check if dropdown will overflow right edge
  if (rect.left + dropdownWidth > viewportWidth) {
    // Open dropdown to the left of the button
    leftPosition = rect.left + scrollX - dropdownWidth + rect.width;
  } else {
    // Open dropdown aligned to button's left edge (right side)
    leftPosition = rect.left + scrollX;
  }
  leftPosition = Math.max(leftPosition, 0);

  menuPosition.value = {
    top: rect.bottom + window.scrollY,
    left: leftPosition,
  };

  // Set after position is calculated
  await nextTick();
  teleportMenuRowId.value = complaintId;
};

const closeMenu = (complaintId) => {
  openMenus.value[complaintId] = false;
  teleportMenuRowId.value = null;
};

const closeAllMenus = () => {
  Object.keys(openMenus.value).forEach(id => {
    openMenus.value[id] = false;
  });
  teleportMenuRowId.value = null;
};

const openModal = (complaint) => {
  selectedComplaint.value = complaint;
  showModal.value = true;
  closeAllMenus();
};

const closeModal = () => {
  showModal.value = false;
  selectedComplaint.value = null;
};

// Print modal functions
const openPrintModal = (complaint) => {
  selectedPrintComplaint.value = complaint;
  showPrintModal.value = true;
  closeAllMenus();
};

const closePrintModal = () => {
  showPrintModal.value = false;
  selectedPrintComplaint.value = null;
};

const handlePrint = () => {
  showToast({ icon: 'success', title: 'Print dialog opened successfully' });
};

const emit = defineEmits(['status-updated']);

const updateStatus = async (complaintId, newStatus) => {
  if (!newStatus) {
    showToast({ icon: 'warning', title: 'Please select a status.' });
    return;
  }
  try {
    await complaintStore.updateComplaint(complaintId, { status: newStatus });
    const index = complaints.value.findIndex(c => c.id === complaintId);
    if (index !== -1) {
      complaints.value[index].status = newStatus;
    }
    statusUpdates.value[complaintId] = newStatus;
    showToast({ icon: 'success', title: 'Status updated successfully' });
    closeMenu(complaintId);
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
      closeMenu(complaintId);
    } catch (error) {
      showToast({ icon: 'error', title: error.message });
    }
  }
};

const editComplaint = (complaintId) => {
  router.push(`/complaints/edit-complaint/${complaintId}`);
  closeMenu(complaintId);
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

  // FIXED: Better click outside handler
  const handleClickOutside = (event) => {
    const target = event.target;
    if (!target.closest('.burger-menu-container') &&
      !target.closest('[data-teleport-menu]') &&
      teleportMenuRowId.value !== null) {
      teleportMenuRowId.value = null;
    }
  };

  document.addEventListener('click', handleClickOutside);

  // Cleanup listener on unmount
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
  });
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
        <div class="relative burger-menu-container">
          <!-- Burger Menu Button -->
          <button @click="(e) => toggleMenu(e, row.id)"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'bg-gray-100': teleportMenuRowId === row.id }">
            <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
              <path d="M10 4a2 2 0 100-4 2 2 0 000 4z" />
              <path d="M10 20a2 2 0 100-4 2 2 0 000 4z" />
            </svg>
          </button>
        </div>
      </template>
    </Table>

    <!-- FIXED: Moved Teleport outside of Table template and added unique container -->
    <Teleport to="body">
      <div v-if="teleportMenuRowId !== null" data-teleport-menu :style="{
        position: 'absolute',
        top: menuPosition.top + 'px',
        left: menuPosition.left + 'px',
        zIndex: 9999
      }" class="bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-48">

        <!-- Edit Option -->
        <button @click="editComplaint(teleportMenuRowId)"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit
        </button>

        <!-- View Option -->
        <button @click="openModal(complaints.find(c => c.id === teleportMenuRowId))"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          View
        </button>

        <!-- Print Option -->
        <button @click="openPrintModal(complaints.find(c => c.id === teleportMenuRowId))"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          Print
        </button>

        <!-- Divider -->
        <hr class="my-2 border-gray-200">

        <!-- Delete Option -->
        <button @click="deleteComplaint(teleportMenuRowId)"
          class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Delete
        </button>
      </div>
    </Teleport>

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

          <!-- Description -->
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