<script setup>
import { useOfficialStore, useResidentStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { onMounted, computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import useToast from '@/Utils/useToast';
import Modal from '../../Components/Modal.vue';
import CardOfficial from './CardOfficial.vue';

const officialStore = useOfficialStore();
const residentStore = useResidentStore();
const { officials, isLoading } = storeToRefs(officialStore);
const { residents } = storeToRefs(residentStore);
const router = useRouter();
const { showToast } = useToast();

// Modal state
const showModal = ref(false);
const selectedOfficial = ref(null);

// Helper function to get officials by position
const getOfficialsByPosition = (positions) => {
    return officials.value.filter(official =>
        positions.some(pos =>
            official.position && official.position.toLowerCase().includes(pos.toLowerCase())
        )
    );
};

// Organized sections configuration
const sectionsConfig = [
    {
        title: 'Barangay Officials',
        groups: [
            {
                name: 'Barangay Captain',
                positions: ['barangay captain'],
                layout: 'single',
                size: 'xl',
                color: 'blue'
            },
            {
                name: 'Secretary & Treasurer',
                positions: ['barangay secretary', 'barangay treasurer'],
                layout: 'grid-cols-1 sm:grid-cols-2',
                size: 'lg',
                colors: ['green', 'purple']
            },
            {
                name: 'Barangay Councilors',
                positions: ['barangay councilor', 'kagawad'],
                layout: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7',
                size: 'md',
                color: 'orange',
                limit: 7
            },
            {
                name: 'SK Chairman',
                positions: ['sk chairman'],
                layout: 'single',
                size: 'xl',
                color: 'red'
            },
            {
                name: 'SK Secretary & Treasurer',
                positions: ['sk secretary', 'sk treasurer'],
                layout: 'grid-cols-1 sm:grid-cols-2',
                size: 'lg',
                colors: ['teal', 'indigo']
            },
            {
                name: 'Youth Councilors',
                positions: ['youth councilor'],
                layout: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7',
                size: 'md',
                color: 'pink',
                limit: 7
            }
        ]
    },
    {
        title: 'Volunteer and Functional Personnel',
        groups: [
            {
                name: 'Barangay Tanod',
                positions: ['barangay tanod', 'tanod'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-10',
                size: 'sm',
                color: 'yellow',
                limit: 20
            },
            {
                name: 'Barangay Health Workers',
                positions: ['barangay health workers', 'health worker'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-10',
                size: 'sm',
                color: 'green',
                limit: 10
            },
            {
                name: 'Barangay Nutrition Scholars',
                positions: ['barangay nutrition scholars', 'nutrition scholar'],
                layout: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                size: 'md',
                color: 'rose',
                limit: 3
            },
            {
                name: 'Lupon Tagapamayapa',
                positions: ['lupon tagapamayapa', 'lupon'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-10',
                size: 'sm',
                color: 'indigo',
                limit: 10
            },
            {
                name: 'BADAC Members',
                positions: ['badac', 'anti-drug'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6',
                size: 'sm',
                color: 'lime'
            },
            {
                name: 'BCPC Members',
                positions: ['bcpc', 'children protection'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6',
                size: 'sm',
                color: 'purple'
            }
        ]
    },
    {
        title: 'Barangay Program Based-Committees',
        groups: [
            {
                name: 'BDRRMC',
                positions: ['bdrrmc', 'disaster risk'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6',
                size: 'sm',
                color: 'sky',
                limit: 15
            },
            {
                name: 'BPOC',
                positions: ['bpoc', 'peace and order'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6',
                size: 'sm',
                color: 'cyan',
                limit: 15
            },
            {
                name: 'Environment Committee',
                positions: ['environment committee', 'environmental'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6',
                size: 'sm',
                color: 'emerald',
                limit: 10
            },
            {
                name: 'GAD Committee',
                positions: ['gad committee', 'gender development'],
                layout: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6',
                size: 'sm',
                color: 'violet',
                limit: 8
            },
            {
                name: 'VAWC Desk',
                positions: ['vawc desk', 'violence against women'],
                layout: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                size: 'md',
                color: 'pink',
                limit: 3
            },
            {
                name: 'BPLO Section',
                positions: ['bplo section', 'business permit'],
                layout: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                size: 'md',
                color: 'amber',
                limit: 3
            }
        ]
    }
];

// Helper function to format name properly
const formatName = (name) => {
    if (!name) return 'Unknown Official';
    if (name.includes('null')) {
        return name
            .replace(/\s+null\s+/gi, ' ')
            .replace(/^null\s+/gi, '')
            .replace(/\s+null$/gi, '')
            .trim();
    }
    return name;
};

// Helper function to get resident data for official
const getResidentForOfficial = (official) => {
    if (official.resident) {
        return official.resident;
    }
    // Fallback: try to find resident by resident_id if not included
    if (official.resident_id && residents.value.length > 0) {
        return residents.value.find(resident => resident.id === official.resident_id);
    }
    return null;
};

// Event handlers
const deleteOfficial = async (id) => {
    if (!confirm('Are you sure you want to delete this official?')) return;

    try {
        await officialStore.deleteOfficial(id);
        showToast({ icon: 'success', title: 'Official deleted successfully' });
        officialStore.getOfficials();
        closeModal();
    } catch (error) {
        showToast({ icon: 'error', title: 'Failed to delete official' });
        console.error(error);
    }
};

const editOfficial = (id) => {
    router.push(`/officials/edit-official/${id}`);
};

const viewOfficial = (official) => {
    selectedOfficial.value = official;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedOfficial.value = null;
};

// Format date for display
const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString();
};

// Helper function to get official's avatar
const getOfficialAvatar = (official) => {
    // First check if official has direct image
    if (official.image && official.image.trim() !== '') {
        return official.image.startsWith('http') ? official.image : `/storage/${official.image}`;
    }

    // Then check resident data
    const resident = getResidentForOfficial(official);
    if (resident && resident.avatar) {
        return resident.avatar.startsWith('http') ? resident.avatar : resident.avatar;
    }

    return null;
};

onMounted(async () => {
    // Load officials with resident data
    await officialStore.getOfficials();
    // Also load residents as fallback if not included in officials response
    if (residents.value.length === 0) {
        await residentStore.getResidents();
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-2 sm:p-4 lg:p-6">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex justify-center items-center min-h-[50vh]">
            <div class="animate-spin rounded-full h-16 w-16 sm:h-32 sm:w-32 border-b-2 border-gray-900"></div>
        </div>

        <!-- Officials Organization Chart -->
        <template v-else>
            <div v-if="officials.length === 0" class="text-gray-500 text-center text-lg">
                No Officials Found.
            </div>

            <div v-else class="space-y-8 sm:space-y-12">
                <!-- Dynamic Sections -->
                <section v-for="(section, sectionIndex) in sectionsConfig" :key="sectionIndex">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-center text-gray-800 mb-4 sm:mb-6 lg:mb-8"
                        :class="{ 'border-t-2 sm:border-t-4 border-gray-300 pt-4 sm:pt-6 lg:pt-8': sectionIndex > 0 }">
                        {{ section.title }}
                    </h1>

                    <!-- Dynamic Groups -->
                    <div v-for="group in section.groups" :key="group.name" class="mb-6 sm:mb-8">
                        <template v-for="(officials, index) in [getOfficialsByPosition(group.positions)]" :key="index">
                            <div v-if="officials.length > 0">
                                <!-- Group Title (except for single officials) -->
                                <h3 v-if="group.layout !== 'single' && officials.length > 1"
                                    class="text-lg sm:text-xl font-semibold text-center text-gray-700 mb-3 sm:mb-4">
                                    {{ group.name }}
                                </h3>

                                <!-- Cards Grid -->
                                <div
                                    :class="group.layout === 'single' ? 'flex justify-center' : `grid ${group.layout} gap-3 sm:gap-4 max-w-7xl mx-auto`">
                                    <CardOfficial
                                        v-for="(official, officialIndex) in (group.limit ? officials.slice(0, group.limit) : officials)"
                                        :key="official.id" :official="official" :size="group.size"
                                        :color-scheme="group.colors ? group.colors[officialIndex % group.colors.length] : group.color"
                                        @view="viewOfficial" @edit="editOfficial" @delete="deleteOfficial" />
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </div>
        </template>

        <!-- View Modal (Official Details) -->
        <Modal :show="showModal" title="Official Details" maxWidth="2xl" @close="closeModal">
            <div v-if="selectedOfficial" class="flex flex-col space-y-6">

                <!-- Profile & Details: Horizontal layout on medium screens -->
                <div class="flex flex-col md:flex-row items-center md:items-start md:space-x-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0 mb-4 md:mb-0">
                        <div v-if="getOfficialAvatar(selectedOfficial)"
                            class="w-28 h-28 sm:w-32 sm:h-32 rounded-full overflow-hidden border-2 border-blue-100 shadow-lg">
                            <img :src="getOfficialAvatar(selectedOfficial)" :alt="formatName(selectedOfficial.name)"
                                class="w-full h-full object-cover" />
                        </div>
                        <div v-else
                            class="w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-gradient-to-tr from-gray-200 to-gray-300 shadow-lg">
                        </div>
                    </div>

                    <!-- Official Details -->
                    <div class="flex-1 text-center md:text-left">
                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900">
                            {{ formatName(selectedOfficial.name) }}
                        </h3>
                        <p class="text-blue-600 font-semibold text-base sm:text-lg mb-2">
                            {{ selectedOfficial.position }}
                        </p>
                        <p class="text-sm text-gray-500">
                            Resident ID: RES-{{ getResidentForOfficial(selectedOfficial)?.id || 'N/A' }}
                        </p>

                        <!-- Details Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <!-- Basic Information Card -->
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                                <h4 class="text-gray-800 font-semibold border-b pb-1">Basic Information</h4>
                                <p><span class="font-medium text-gray-500">Full Name:</span> {{
                                    formatName(selectedOfficial.name) }}</p>
                                <p><span class="font-medium text-gray-500">Position:</span> {{ selectedOfficial.position
                                    ||
                                    'N/A' }}</p>
                                <p><span class="font-medium text-gray-500">Length of Term:</span> {{
                                    selectedOfficial.terms ||
                                    'N/A' }}</p>
                                <p><span class="font-medium text-gray-500">Term Count:</span> {{
                                    selectedOfficial.no_of_per_term
                                    || 'N/A' }}</p>
                            </div>

                            <!-- Important Dates Card -->
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                                <h4 class="text-gray-800 font-semibold border-b pb-1">Important Dates</h4>
                                <p><span class="font-medium text-gray-500">Elected Date:</span> {{
                                    formatDate(selectedOfficial.elected_date) }}</p>
                                <p><span class="font-medium text-gray-500">Start Date:</span> {{
                                    formatDate(selectedOfficial.start_date) }}</p>
                                <p><span class="font-medium text-gray-500">End Date:</span> {{
                                    formatDate(selectedOfficial.end_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-gray-800 font-semibold border-b pb-1 mb-2">Description</h4>
                    <p class="text-gray-700 text-sm sm:text-base leading-relaxed">
                        {{ selectedOfficial.description || 'No description available.' }}
                    </p>
                </div>

                <!-- Edit/Delete Buttons -->
                <div class="flex justify-center gap-3 mt-4">
                    <button @click="editOfficial(selectedOfficial.id)"
                        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition text-sm sm:text-base">
                        Edit
                    </button>
                    <button @click="deleteOfficial(selectedOfficial.id)"
                        class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition text-sm sm:text-base">
                        Delete
                    </button>
                </div>

            </div>
        </Modal>

    </div>
</template>