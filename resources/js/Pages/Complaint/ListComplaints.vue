<script setup>
import { Table, Paginate } from '@/Components';
import Modal from '../../Components/Modal.vue'; // Import the Modal component
import { useComplaintStore, useResidentStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref, nextTick } from "vue";
import useToast from '@/Utils/useToast';
import { useRoute, useRouter } from 'vue-router';
import ComplaintPrintTemplate from './ComplaintPrintTemplate.vue';

// Initialize resident store
const residentStore = useResidentStore();
const { residents } = storeToRefs(residentStore);

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

const getResidentNumber = (id) => {
  if (!id) return 'N/A';
  const resident = residents.value.find(r => r.id == id);
  return resident ? resident.resident_number : 'N/A';
};

const getResidentName = (id) => {
  if (!id) return 'N/A';
  const resident = residents.value.find(r => r.id == id);
  return resident ? `${resident.first_name} ${resident.last_name}` : 'N/A';
};

const getSupportingDocuments = (complaint) => {
  if (!complaint?.supporting_documents) return [];
  if (Array.isArray(complaint.supporting_documents)) {
    return complaint.supporting_documents.filter(doc => {
      if (typeof doc === 'object' && doc.path && doc.name) return true;
      if (typeof doc === 'string') return true;
      return false;
    });
  }
  if (typeof complaint.supporting_documents === 'string') {
    try {
      const parsed = JSON.parse(complaint.supporting_documents);
      return Array.isArray(parsed) ? parsed.filter(doc => {
        if (typeof doc === 'object' && doc.path && doc.name) return true;
        if (typeof doc === 'string') return true;
        return false;
      }) : [];
    } catch (e) {
      return [];
    }
  }
  return [];
};

const getFileName = (doc) => {
  if (typeof doc === 'object' && doc.name) return doc.name;
  if (typeof doc === 'string') return doc.split('/').pop() || 'Unknown file';
  return 'Unknown file';
};

const getFilePath = (doc) => {
  if (typeof doc === 'object' && doc.path) return doc.path;
  if (typeof doc === 'string') return doc;
  return '';
};

const { showToast } = useToast();
const route = useRoute();
const router = useRouter();

const complaintStore = useComplaintStore();
const { complaints, isLoading, paginate } = storeToRefs(complaintStore);

const currentPage = ref(route.query.page || 1);
const menuPosition = ref({ top: 0, left: 0 });
const teleportMenuRowId = ref(null);

const showModal = ref(false);
const selectedComplaint = ref(null);
const showPrintModal = ref(false);
const selectedPrintComplaint = ref(null);

const handlePageChange = (page) => {
  currentPage.value = page;
  complaintStore.getComplaints(page);
  router.replace({ query: { page: page } });
};

const toggleMenu = async (event, complaintId) => {
  if (teleportMenuRowId.value === complaintId) {
    teleportMenuRowId.value = null;
    return;
  }
  if (teleportMenuRowId.value !== null) {
    teleportMenuRowId.value = null;
    await nextTick();
  }

  const rect = event.currentTarget.getBoundingClientRect();
  const dropdownWidth = 192;
  const viewportWidth = window.innerWidth;
  const scrollX = window.scrollX;

  let leftPosition = rect.left + scrollX;
  if (rect.left + dropdownWidth > viewportWidth) {
    leftPosition = rect.left + scrollX - dropdownWidth + rect.width;
  }
  leftPosition = Math.max(leftPosition, 0);

  menuPosition.value = {
    top: rect.bottom + window.scrollY,
    left: leftPosition,
  };

  await nextTick();
  teleportMenuRowId.value = complaintId;
};

const closeMenu = () => {
  teleportMenuRowId.value = null;
};

const handleClickOutside = (event) => {
  const target = event.target;
  if (!target.closest('.burger-menu-container') &&
    !target.closest('[data-teleport-menu]') &&
    teleportMenuRowId.value !== null) {
    teleportMenuRowId.value = null;
  }
};

const openModal = (complaint) => {
  selectedComplaint.value = complaint;
  showModal.value = true;
  closeMenu();
};

const closeModal = () => {
  showModal.value = false;
  selectedComplaint.value = null;
};

const openPrintModal = (complaint) => {
  selectedPrintComplaint.value = complaint;
  showPrintModal.value = true;
  closeMenu();
};

const closePrintModal = () => {
  showPrintModal.value = false;
  selectedPrintComplaint.value = null;
};

const handlePrint = () => {
  showToast({ icon: 'success', title: 'Print dialog opened successfully' });
};

const deleteComplaint = async (complaintId) => {
  if (confirm('Are you sure you want to delete this complaint?')) {
    try {
      await complaintStore.deleteComplaint(complaintId);
      showToast({ icon: 'success', title: 'Complaint deleted successfully' });
      complaintStore.getComplaints(currentPage.value);
      closeMenu();
    } catch (error) {
      showToast({ icon: 'error', title: error.message });
    }
  }
};

const editComplaint = (complaintId) => {
  router.push(`/complaints/edit-complaint/${complaintId}`);
  closeMenu();
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
  complaintStore.getComplaints(page);
  residentStore.getResidents(); // Load resident data

  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
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

    <Teleport to="body">
      <div v-if="teleportMenuRowId !== null" data-teleport-menu :style="{
        position: 'absolute',
        top: menuPosition.top + 'px',
        left: menuPosition.left + 'px',
        zIndex: 9999
      }" class="bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-48">
        <button @click="editComplaint(teleportMenuRowId)"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit
        </button>

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

        <button @click="openPrintModal(complaints.find(c => c.id === teleportMenuRowId))"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          Print
        </button>

        <hr class="my-2 border-gray-200">

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

    <!-- Print Template Modal -->
    <ComplaintPrintTemplate v-if="showPrintModal" :complaint="selectedPrintComplaint" @close="closePrintModal"
      @print="handlePrint" />

    <!-- Modal for Complaint Details using Modal.vue component -->
    <Modal :show="showModal" title="Complaint Details" max-width="4xl" @close="closeModal">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-gray-800 text-sm">
        <!-- Complainant Section -->
        <div>
          <h3 class="font-semibold mb-3 text-blue-800">Complainant Information</h3>

          <div class="mb-3">
            <h4 class="font-medium mb-1 text-gray-600">Name:</h4>
            <p class="text-gray-900">{{ getResidentName(selectedComplaint?.complainant_id) || 'N/A' }}</p>
          </div>

          <div class="mb-3">
            <h4 class="font-medium mb-1 text-gray-600">Resident Number:</h4>
            <div class="bg-blue-50 inline-block rounded px-2 py-1 text-blue-700 text-xs font-mono select-all">
              {{ getResidentNumber(selectedComplaint?.complainant_id) || 'N/A' }}
            </div>
          </div>

          <div class="mb-3">
            <h4 class="font-medium mb-1 text-gray-600">Resident ID:</h4>
            <p class="text-gray-500 text-sm">
              {{ selectedComplaint?.complainant_id || 'N/A' }}
            </p>
          </div>
        </div>

        <!-- Respondent Section -->
        <div>
          <h3 class="font-semibold mb-3 text-red-800">Respondent Information</h3>

          <div class="mb-3">
            <h4 class="font-medium mb-1 text-gray-600">Name:</h4>
            <p class="text-gray-900">{{ getResidentName(selectedComplaint?.respondent_id) || 'N/A' }}</p>
          </div>

          <div class="mb-3">
            <h4 class="font-medium mb-1 text-gray-600">Resident Number:</h4>
            <div class="bg-blue-50 inline-block rounded px-2 py-1 text-blue-700 text-xs font-mono select-all">
              {{ getResidentNumber(selectedComplaint?.respondent_id) || 'N/A' }}
            </div>
          </div>

          <div class="mb-3">
            <h4 class="font-medium mb-1 text-gray-600">Resident ID:</h4>
            <p class="text-gray-500 text-sm">
              {{ selectedComplaint?.respondent_id || 'N/A' }}
            </p>
          </div>
        </div>

        <!-- Case Number -->
        <div>
          <h3 class="font-semibold mb-1">Case Number:</h3>
          <p>{{ selectedComplaint?.case_no || 'N/A' }}</p>
        </div>

        <!-- Title -->
        <div>
          <h3 class="font-semibold mb-1">Title:</h3>
          <p>{{ selectedComplaint?.title || 'N/A' }}</p>
        </div>

        <!-- Nature of Complaint -->
        <div>
          <h3 class="font-semibold mb-1">Nature of Complaint:</h3>
          <p>{{ selectedComplaint?.nature_of_complaint || 'N/A' }}</p>
        </div>

        <!-- Status -->
        <div>
          <h3 class="font-semibold mb-1">Status:</h3>
          <p :class="{
            'text-green-600 font-semibold': selectedComplaint?.status === 'Resolved',
            'text-yellow-600 font-semibold': selectedComplaint?.status === 'In Progress',
            'text-red-600 font-semibold': selectedComplaint?.status === 'Open'
          }">
            {{ selectedComplaint?.status || 'N/A' }}
          </p>
        </div>

        <!-- Filing Date -->
        <div>
          <h3 class="font-semibold mb-1">Filing Date:</h3>
          <p>{{ formatDateTime(selectedComplaint?.filing_date) || 'N/A' }}</p>
        </div>

        <!-- Incident Date -->
        <div>
          <h3 class="font-semibold mb-1">Date & Time of Incident:</h3>
          <p>{{ formatDateTime(selectedComplaint?.incident_datetime) || 'N/A' }}</p>
        </div>

        <!-- Location -->
        <div>
          <h3 class="font-semibold mb-1">Location of Incident:</h3>
          <p>{{ selectedComplaint?.incident_location || 'N/A' }}</p>
        </div>

        <!-- Witnesses -->
        <div class="md:col-span">
          <h3 class="font-semibold mb-2">Witness/es:</h3>
          <div v-if="selectedComplaint?.witness"
            class="pl-4 max-h-32 overflow-y-auto bg-gray-50 border border-gray-300 rounded p-2 text-gray-700">
            <ul class="list-disc list-inside space-y-1">
              <li v-for="(witness, index) in selectedComplaint.witness.split('\n').filter(name => name.trim())"
                :key="index">
                {{ witness.trim() }}
              </li>
            </ul>
          </div>
          <p v-else class="italic text-gray-400">No witnesses listed</p>
        </div>

        <!-- Description -->
        <div class="md:col-span-2">
          <h3 class="font-semibold mb-1">Description:</h3>
          <textarea readonly
            class="w-full h-40 p-3 border border-gray-300 rounded resize-none bg-gray-50 text-gray-800 text-sm leading-relaxed focus:outline-none"
            :value="selectedComplaint?.description || 'N/A'"></textarea>
        </div>

        <!-- Resolution -->
        <div class="md:col-span-2">
          <h3 class="font-semibold mb-1">Resolution:</h3>
          <textarea readonly
            class="w-full h-40 p-3 border border-gray-300 rounded resize-none bg-gray-50 text-gray-800 text-sm leading-relaxed focus:outline-none"
            :value="selectedComplaint?.resolution || 'N/A'"></textarea>
        </div>

        <!-- Supporting Documents -->
        <div class="md:col-span-2">
          <h3 class="font-semibold mb-1">Supporting Documents:</h3>
          <div v-if="getSupportingDocuments(selectedComplaint).length > 0">
            <ul class="list-disc list-inside space-y-1">
              <li v-for="(doc, index) in getSupportingDocuments(selectedComplaint)" :key="index">
                <a :href="`/storage/${getFilePath(doc)}`" target="_blank"
                  class="text-blue-600 hover:underline font-medium">
                  {{ getFileName(doc) }}
                </a>
              </li>
            </ul>
          </div>
          <p v-else class="italic text-gray-400">No supporting documents available</p>
        </div>
      </div>
    </Modal>
  </div>
</template>

<style>
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>