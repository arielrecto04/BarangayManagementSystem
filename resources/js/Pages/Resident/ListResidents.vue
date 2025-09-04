<script setup>
import { Table, Paginate } from '@/Components';
import Modal from '@/Components/Modal.vue';
import { useResidentStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, ref, watch, computed, nextTick } from "vue";
import useToast from '@/Utils/useToast';
import { useRoute, useRouter } from 'vue-router';
import Indigency from '../../Components/Certificates/Indigency.vue';
import { downloadPdf } from '@/Utils/downloadPdf';
import { Municipal_Information, Barangay_Information } from '@/Lang/en';

// Certificate dropdown options
const certificateOptions = ref([
    { value: 'indigency', label: 'Certificate of Indigency' },
    { value: 'good_moral_character', label: 'Certificate of Good Moral Character' } // Keep for future use
]);

const certificateContent = ref(null); // will reference certificate HTML for pdf generation

const selectedCertificate = ref(certificateOptions.value[0].value);
const showCertificatePreview = ref(false);

const openCertificate = () => {
    showCertificatePreview.value = true;
};

const { showToast, showConfirm } = useToast();
const route = useRoute();
const router = useRouter();

const residentStore = useResidentStore();
const { residents, isLoading, paginate } = storeToRefs(residentStore);

const currentPage = ref(parseInt(route.query.page) || 1);
const showModal = ref(false);
const selectedResident = ref(null);
const searchQuery = ref("");

// Updated downloadCertificate method for ListResidents.vue
const downloadCertificate = async () => {
    if (!selectedResident.value) {
        showToast({ icon: 'error', title: 'No resident selected' });
        return;
    }

    // Show loading state (you can add a loading state to your component)
    const loadingToast = showToast({
        icon: 'info',
        title: 'Generating certificate...',
        timer: false
    });

    try {
        // Wait for the DOM to update to ensure the certificate is rendered
        await nextTick();

        // Small delay to ensure all images are loaded
        await new Promise(resolve => setTimeout(resolve, 500));

        if (!certificateContent.value) {
            throw new Error('Certificate content not found');
        }

        // Ensure images are loaded
        const images = certificateContent.value.querySelectorAll('img');
        const imagePromises = Array.from(images).map(img => {
            return new Promise((resolve) => {
                if (img.complete) {
                    resolve();
                } else {
                    img.onload = resolve;
                    img.onerror = resolve; // Continue even if image fails to load
                    // Timeout fallback
                    setTimeout(resolve, 2000);
                }
            });
        });

        await Promise.all(imagePromises);

        const residentName = `${selectedResident.value.first_name}_${selectedResident.value.last_name}`
            .toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^a-z0-9_]/g, ''); // Remove special characters

        const filename = `certificate_of_indigency_${residentName}.pdf`;

        // Use the enhanced downloadPdf utility with better error handling
        await downloadPdf(
            certificateContent.value,
            filename,
            {
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                    windowWidth: 794, // A4 width in pixels at 96dpi
                    windowHeight: 1123, // A4 height in pixels at 96dpi
                },
                margin: [10, 10, 10, 10],
                pagebreak: { mode: 'avoid-all' }
            }
        );

        // Close loading toast
        if (loadingToast && loadingToast.close) {
            loadingToast.close();
        }

        showToast({ icon: 'success', title: 'Certificate downloaded successfully' });

    } catch (error) {
        console.error('Error downloading certificate:', error);

        // Close loading toast
        if (loadingToast && loadingToast.close) {
            loadingToast.close();
        }

        let errorMessage = 'Error downloading certificate';

        // Provide more specific error messages
        if (error.message.includes('oklch')) {
            errorMessage = 'Color format error. Please try again or contact support.';
        } else if (error.message.includes('canvas')) {
            errorMessage = 'Unable to render certificate. Please check your browser settings.';
        } else if (error.message.includes('network')) {
            errorMessage = 'Network error. Please check your connection and try again.';
        }

        showToast({
            icon: 'error',
            title: errorMessage,
            text: error.message
        });
    }
};
// Add method to force refresh avatar images with cache busting
const refreshAvatar = (url) => {
    if (!url || url.includes('ionicframework.com')) return url;
    return url + (url.includes('?') ? '&' : '?') + 't=' + new Date().getTime();
};

// Add method to handle image loading errors
const handleImageError = (event) => {
    event.target.src = 'https://ionicframework.com/docs/img/demos/avatar.svg';
};

const handlePageChange = async (page) => {
    try {
        currentPage.value = page;
        await residentStore.getResidents(page);
        router.replace({ query: { page: page } });
    } catch (error) {
        console.error('Error changing page:', error);
        showToast({ icon: 'error', title: 'Error loading page' });
    }
};

const openModal = (resident) => {
    selectedResident.value = { ...resident };
    showModal.value = true;
    showCertificatePreview.value = false; // reset preview when opening modal
};

const closeModal = () => {
    showModal.value = false;
    selectedResident.value = null;
    showCertificatePreview.value = false;
};

const deleteResident = async (residentId) => {
    const result = await showConfirm(
        'Are you sure you want to delete this resident?',
        'Delete Confirmation'
    );

    if (!result.isConfirmed) return;

    try {
        await residentStore.deleteResident(residentId);
        showToast({ icon: 'success', title: 'Resident deleted successfully' });
        closeModal();
        await residentStore.getResidents(currentPage.value);
    } catch (error) {
        console.error('Error deleting resident:', error);
        showToast({ icon: 'error', title: error.message || 'Error deleting resident' });
    }
};

// Updated columns to include avatar
const columns = [
    { key: "avatar", label: "Photo", class: "hidden sm:table-cell" },
    { key: "resident_number", label: "Resident Number", class: "hidden sm:table-cell" },
    { key: "household_number", label: "Household ID", class: "hidden sm:table-cell" },
    { key: "first_name", label: "First Name", class: "hidden sm:table-cell" },
    { key: "middle_name", label: "Middle Name", class: "hidden sm:table-cell" },
    { key: "last_name", label: "Last Name", class: "hidden sm:table-cell" },
];

onMounted(async () => {
    try {
        await residentStore.getResidents(currentPage.value);
    } catch (error) {
        console.error('Error loading residents:', error);
        showToast({ icon: 'error', title: 'Error loading residents' });
    }
});

watch(() => route.query.page, (newPage) => {
    if (newPage && parseInt(newPage) !== currentPage.value) {
        currentPage.value = parseInt(newPage);
    }
});

const filteredResidents = computed(() => {
    if (!searchQuery.value) return residents.value;
    return residents.value.filter(r =>
        `${r.first_name} ${r.last_name}`
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase())
    );
});

// Computed property for certificate preview props
const certificatePreviewProps = computed(() => {
    if (!selectedResident.value) return null;

    const currentDate = new Date();
    return {
        resident: {
            name: `${selectedResident.value.first_name} ${selectedResident.value.middle_name ? selectedResident.value.middle_name + ' ' : ''}${selectedResident.value.last_name}`,
            address: selectedResident.value.address || 'N/A',
            barangay: Barangay_Information.title,
            municipality: Municipal_Information.municipality,
            province: Municipal_Information.province,
            lowIncomeGroup: true,
        },
        barangayInfo: {
            name: Barangay_Information.title,
            municipality: Municipal_Information.municipality,
            province: Municipal_Information.province,
            captainName: Barangay_Information.captain_name
        },
        issueDetails: {
            date_issued: currentDate.toISOString().split('T')[0],
            day: currentDate.getDate().toString(),
            month: currentDate.toLocaleDateString('en-US', { month: 'long' }),
            year: currentDate.getFullYear().toString(),
            location: 'Office of the Barangay Captain',
            requestedFor: 'financial assistance or other legitimate purposes'
        },
        leftSealUrl: Municipal_Information.municipal_seal,
        rightSealUrl: Barangay_Information.barangay_seal
    };
});
</script>


<template>
    <div class="flex flex-col gap-3 sm:gap-4 p-2 sm:p-0">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900">List of Residents</h1>
            <div class="text-xs sm:text-sm text-gray-600" v-if="paginate">
                {{ paginate.from }}-{{ paginate.to }} of {{ paginate.total }} residents
            </div>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 sm:h-12 sm:w-12 border-b-2 border-gray-900"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!residents || residents.length === 0" class="text-center py-12 bg-white rounded-lg shadow-sm">
            <div class="text-gray-400 text-6xl mb-4">👥</div>
            <h3 class="text-lg md:text-xl font-medium text-gray-600 mb-2">No residents found</h3>
            <p class="text-gray-500 mb-4">Get started by adding your first resident.</p>
            <router-link to="/residents/add-resident"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-block">
                Add First Resident
            </router-link>
        </div>

        <!-- Residents List -->
        <template v-else>
            <!-- Mobile View -->
            <div class="block sm:hidden">
                <div class="space-y-3">
                    <div v-for="resident in filteredResidents" :key="resident.id" @click="openModal(resident)"
                        class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-pointer hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center min-w-0">
                                <!-- Avatar -->
                                <div class="flex-shrink-0 mr-3">
                                    <img :src="resident.avatar || 'https://ionicframework.com/docs/img/demos/avatar.svg'"
                                        :alt="`${resident.first_name} ${resident.last_name}`"
                                        class="w-10 h-10 rounded-full object-cover border-2 border-gray-200"
                                        @error="handleImageError" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">
                                        {{ resident.first_name }} {{ resident.last_name }}
                                    </div>
                                    <div class="text-xs text-gray-600 truncate">
                                        {{ resident.resident_number }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ resident.gender }} • {{ resident.age }} yrs
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden sm:block">
                <Table :columns="columns" :rows="residents" :rowClickable="true" @row-click="openModal"
                    class="cursor-pointer">
                    <template #cell(avatar)="{ row }">
                        <img :src="row.avatar || 'https://ionicframework.com/docs/img/demos/avatar.svg'"
                            :alt="`${row.first_name} ${row.last_name}`"
                            class="w-10 h-10 rounded-full object-cover border-2 border-gray-200"
                            @error="handleImageError" />
                    </template>
                </Table>
            </div>
        </template>

        <!-- Pagination -->
        <div v-if="paginate && residents?.length > 0" class="mt-4">
            <Paginate @page-changed="handlePageChange" :maxVisibleButtons="3" :totalPages="paginate.last_page"
                :totalItems="paginate.total" :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />
        </div>

        <!-- Modal -->
        <Modal :show="showModal" title="Resident Details" max-width="5xl" @close="closeModal">
            <div v-if="selectedResident" class="flex flex-col space-y-6">
                <!-- Profile & Details -->
                <div class="flex flex-col md:flex-row items-center md:items-start md:space-x-6">
                    <div class="flex-shrink-0 mb-4 md:mb-0">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-200 shadow-lg">
                            <img :src="selectedResident?.avatar || 'https://ionicframework.com/docs/img/demos/avatar.svg'"
                                :alt="`${selectedResident?.first_name} ${selectedResident?.last_name}`"
                                class="w-full h-full object-cover" @error="handleImageError" />
                        </div>
                        <div class="mt-3 text-center">
                            <p v-if="selectedResident?.first_name" class="text-xl font-bold text-gray-800">
                                {{ selectedResident.first_name }}
                            </p>
                            <p v-if="selectedResident?.middle_name" class="text-lg font-bold text-gray-800">
                                {{ selectedResident.middle_name }}
                            </p>
                            <p v-if="selectedResident?.last_name" class="text-xl font-bold text-gray-800">
                                {{ selectedResident.last_name }}
                            </p>
                            <p v-if="!selectedResident?.first_name && !selectedResident?.last_name && !selectedResident?.middle_name"
                                class="text-xl font-bold text-gray-800">
                                No Name
                            </p>
                        </div>
                    </div>

                    <!-- Resident Details -->
                    <div class="flex-1 text-center md:text-left">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div v-for="(value, label) in {
                                'Resident ID': selectedResident?.id,
                                'Resident Number': selectedResident?.resident_number,
                                'Middle Name': selectedResident?.middle_name,
                                Age: selectedResident?.age,
                                Gender: selectedResident?.gender,
                                Birthday: selectedResident?.birthday,
                                Address: selectedResident?.address,
                                'Contact Number': selectedResident?.contact_number,
                                'Emergency Contact': selectedResident?.emergency_contact,
                                Email: selectedResident?.email,
                                'Family Member': selectedResident?.family_member
                            }" :key="label" :class="{ 'sm:col-span-2': ['Address'].includes(label) }"
                                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-1">
                                <h4 class="text-gray-800 font-semibold text-xs">{{ label }}</h4>
                                <p class="text-gray-700 text-sm break-words">{{ value || 'N/A' }}</p>
                            </div>

                            <div v-if="selectedResident?.created_at"
                                class="sm:col-span bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                                <h4 class="text-gray-800 font-semibold text-xs">Created At</h4>
                                <p class="text-gray-700 text-sm break-words">{{ new
                                    Date(selectedResident.created_at).toLocaleString() }}</p>
                            </div>
                            <div v-if="selectedResident?.updated_at"
                                class="sm:col-span bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                                <h4 class="text-gray-800 font-semibold text-xs">Updated At</h4>
                                <p class="text-gray-700 text-sm break-words">{{ new
                                    Date(selectedResident.updated_at).toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Certificate Selection -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mt-4">
                    <h4 class="text-gray-800 font-semibold mb-2">Select Certificate</h4>
                    <select v-model="selectedCertificate"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option v-for="option in certificateOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>

                    <button @click="openCertificate"
                        class="mt-3 w-full bg-purple-500 text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition text-sm sm:text-base">
                        Preview Certificate
                    </button>

                    <!-- Certificate Preview -->
                    <div v-if="showCertificatePreview && selectedCertificate === 'indigency'"
                        class="mt-4 p-4 border rounded-lg bg-gray-50 max-h-[60vh] overflow-auto print:hidden">
                        <div ref="certificateContent">
                            <Indigency v-if="certificatePreviewProps" v-bind="certificatePreviewProps" />
                        </div>

                        <div class="flex gap-2 mt-4">
                            <button @click="showCertificatePreview = false"
                                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">
                                Close Preview
                            </button>

                            <button @click="downloadCertificate"
                                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                                Download Certificate
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex justify-center gap-3 mt-4">
                    <button @click="deleteResident(selectedResident?.id)"
                        class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition text-sm sm:text-base">
                        Delete
                    </button>

                    <router-link :to="`/residents/edit-resident/${selectedResident?.id}`" @click="closeModal"
                        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition text-sm sm:text-base flex items-center justify-center">
                        Edit
                    </router-link>

                    <button @click="closeModal"
                        class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition text-sm sm:text-base">
                        Close
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>


<style scoped>
* {
    transition: all 0.2s ease-in-out;
}

@media (max-width: 767px) {

    button,
    a {
        min-height: 44px;
        min-width: 44px;
    }
}

.cursor-pointer {
    cursor: pointer;
}

@media print {
    body * {
        visibility: hidden;
    }

    .print-visible,
    .print-visible * {
        visibility: visible;
    }

    .print-visible {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>