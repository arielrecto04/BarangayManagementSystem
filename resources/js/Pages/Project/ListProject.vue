<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Modal, Loader, Paginate, Table } from '@/Components';
import { debounce } from '@/Utils';
import { useProjectStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import useToast from '@/Utils/useToast';

const { showToast, showConfirm, showAlert } = useToast();
const router = useRouter();
const route = useRoute();
const projectStore = useProjectStore();

const isDeleting = ref(false);
const createModal = ref(false);
const viewModal = ref(false);
const selectedProject = ref(null);
const isSearching = ref(false);

const { error, isLoading, projects, paginate } = storeToRefs(projectStore);

const currentPage = ref(route.query.page || 1);

const deleteProjectHandler = async () => {
    if (!selectedProject.value) return;

    const result = await showConfirm(
        'Are you sure you want to delete this project?',
        'Delete Project'
    );

    if (!result.isConfirmed) return;

    try {
        await projectStore.deleteProject(selectedProject.value.id);
        viewModal.value = false;
        await projectStore.getProjects(currentPage.value);

        showToast({ icon: 'success', title: 'Project deleted!' });
    } catch (err) {
        console.error('Error deleting project:', err);
        showAlert({ icon: 'error', title: 'Error', text: 'Failed to delete project. Please try again.' });
    }
};

// Improved search handler with validation
const handleSearch = debounce(async (query) => {
    try {
        // Only search if query has at least 2 characters or is empty (to reset)
        if (query === '' || query.length >= 2) {
            isSearching.value = true;

            if (query === '') {
                // Reset to show all projects when search is cleared
                await projectStore.getProjects(1);
                currentPage.value = 1;
            } else {
                // Perform search
                await projectStore.searchProjects(query);
            }
        }
    } catch (error) {
        console.error('Search error:', error);
        // Don't show error for empty searches or short queries
        if (query.length >= 2) {
            alert('Search failed. Please try again.');
        }
    } finally {
        isSearching.value = false;
    }
}, 500);

const handleCreateModalAction = () => {
    createModal.value = !createModal.value;
    if (!createModal.value) {
        // Reset errors when closing modal
        projectStore._error = null;
    }
};

const handlePageChange = (page) => {
    currentPage.value = page;
    projectStore.getProjects(currentPage.value);
    router.replace({
        query: {
            page: currentPage.value
        }
    });
};

const search = ref('');
const createForm = ref(null);
const isSubmitting = ref(false);

const columns = [
    { key: "title", label: "Title" },
    { key: "description", label: "Description" },
    { key: "start_date", label: "Start Date" },
    { key: "target_completion", label: "Target Completion" },
    { key: "status", label: "Status" },
    { key: "completion_percentage", label: "Progress" }
];

// Responsive table columns
const mobileColumns = [
    { key: "title", label: "Title" },
    { key: "status", label: "Status" }
];

const desktopColumns = [
    { key: "title", label: "Title" },
    { key: "description", label: "Description" },
    { key: "start_date", label: "Start Date" },
    { key: "target_completion", label: "Target Completion" },
    { key: "status", label: "Status" },
    { key: "completion_percentage", label: "Progress" }
];

function formatStatus(status) {
    const statusMap = {
        'planning': 'Planning',
        'in_progress': 'In Progress',
        'on_hold': 'On Hold',
        'completed': 'Completed',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'pending': 'Pending'
    };
    return statusMap[status] || status;
}

function getStatusColor(status) {
    const colors = {
        'planning': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'on_hold': 'bg-red-100 text-red-800',
        'completed': 'bg-green-100 text-green-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'pending': 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function viewProject(project) {
    selectedProject.value = { ...project }; // Create copy to avoid reactivity issues
    viewModal.value = true;
}

function editProject(id) {
    // Close modal first
    viewModal.value = false;
    selectedProject.value = null;

    router.push({ name: 'Edit Project', params: { id } });
}

const createProject = async () => {
    try {
        isSubmitting.value = true;
        const formData = new FormData(createForm.value);
        await projectStore.addProject(formData);
        if (!error.value) {
            createModal.value = false;
            createForm.value.reset();
            await projectStore.getProjects(currentPage.value);
        }
    } catch (error) {
        console.log(error);
    } finally {
        isSubmitting.value = false;
    }
};

// Close modal
const closeModal = () => {
    viewModal.value = false;
    selectedProject.value = null;
};

onMounted(async () => {
    try {
        await projectStore.getProjects(currentPage.value);
    } catch (error) {
        console.error('Error loading projects:', error);
    }
});
</script>

<template>
    <div class="flex flex-col gap-3 sm:gap-4 p-2 sm:p-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900">List of Projects</h1>
            <div class="text-xs sm:text-sm text-gray-600" v-if="paginate">
                {{ paginate.from }}-{{ paginate.to }} of {{ paginate.total }} projects
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 sm:h-12 sm:w-12 border-b-2 border-gray-900"></div>
        </div>

        <!-- Mobile Card View -->
        <div v-else class="block sm:hidden">
            <div class="space-y-3">
                <div v-for="project in projects" :key="project.id" @click="viewProject(project)"
                    class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-pointer hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-medium text-gray-500 mb-1">Title</div>
                            <div class="text-sm font-semibold text-gray-900 truncate">
                                {{ project.title }}
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="text-xs font-medium text-gray-500 mb-1">Description</div>
                        <div class="text-sm text-gray-700 truncate">
                            {{ project.description || 'No description available' }}
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <div class="bg-gray-100 px-2 py-1 rounded">
                            <span class="text-gray-600">Progress:</span>
                            <span class="font-medium">{{ project.completion_percentage || 0 }}%</span>
                        </div>
                        <div class="bg-gray-100 px-2 py-1 rounded">
                            <span :class="{
                                'text-green-600 font-semibold': project.status === 'completed',
                                'text-yellow-600 font-semibold': project.status === 'in_progress',
                                'text-blue-600 font-semibold': project.status === 'planning',
                                'text-red-600 font-semibold': project.status === 'on_hold'
                            }">
                                {{ formatStatus(project.status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <div class="text-xs text-gray-600">
                            Start: {{ formatDate(project.start_date) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Target: {{ formatDate(project.target_completion) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block">
            <Table :columns="desktopColumns" :rows="projects.map(p => ({
                ...p,
                start_date: formatDate(p.start_date),
                target_completion: formatDate(p.target_completion)
            }))" :rowClickable="true" :showActions="false" @row-click="viewProject" class="cursor-pointer">
                <template #cell(description)="{ row }">
                    <p class="truncate max-w-xs">{{ row.description || 'No description' }}</p>
                </template>
                <template #cell(status)="{ row }">
                    <span :class="`px-2 py-1 text-xs rounded-full ${getStatusColor(row.status)}`">
                        {{ formatStatus(row.status) }}
                    </span>
                </template>
                <template #cell(completion_percentage)="{ row }">
                    <span>{{ row.completion_percentage || 0 }}%</span>
                </template>
            </Table>
        </div>

        <!-- Pagination -->
        <div v-if="paginate" class="mt-4">
            <Paginate @page-changed="handlePageChange" :maxVisibleButtons="3" :totalPages="paginate.last_page"
                :totalItems="paginate.total" :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />
        </div>

        <!-- View Project Details Modal -->
        <Modal title="Project Details" :show="viewModal" @close="closeModal" max-width="4xl">
            <div v-if="selectedProject" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.title }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.description || `No description
                                    available` }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.category || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span
                                    :class="`px-2 py-1 text-xs rounded-full ${getStatusColor(selectedProject.status)}`">
                                    {{ formatStatus(selectedProject.status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Timeline & Progress</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <p class="text-sm text-gray-900">{{ formatDate(selectedProject.start_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Target Completion</label>
                                <p class="text-sm text-gray-900">{{ formatDate(selectedProject.target_completion) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Actual Completion</label>
                                <p class="text-sm text-gray-900">{{ formatDate(selectedProject.actual_completion) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Progress</label>
                                <div class="flex items-center">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full"
                                            :style="`width: ${selectedProject.completion_percentage || 0}%`"></div>
                                    </div>
                                    <span class="text-sm text-gray-900">{{ selectedProject.completion_percentage ||
                                        0
                                        }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Team & Organization</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Project Lead</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.project_lead || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Team Size</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.number_of_members || 0 }}
                                    members</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Funding Source</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.funding_source || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Assigned
                                    Organizations</label>
                                <div v-if="selectedProject.assigned_organizations && selectedProject.assigned_organizations.length > 0"
                                    class="mt-1">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="(org, index) in selectedProject.assigned_organizations"
                                            :key="index" class="text-sm text-gray-900">{{ org }}</span>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-900">No organizations assigned</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Location & Details</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Site Address</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.site_address || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Barangay/Zone</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.barangay_zone || 'N/A' }}</p>
                            </div>
                            <div v-if="selectedProject.disbursement_schedule">
                                <label class="block text-sm font-medium text-gray-700">Disbursement Schedule</label>
                                <p class="text-sm text-gray-900">{{ selectedProject.disbursement_schedule }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="selectedProject.milestone_achieved || selectedProject.challenges_encountered">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                    <div class="space-y-3">
                        <div v-if="selectedProject.milestone_achieved">
                            <label class="block text-sm font-medium text-gray-700">Milestones Achieved</label>
                            <p class="text-sm text-gray-900">{{ selectedProject.milestone_achieved }}</p>
                        </div>
                        <div v-if="selectedProject.challenges_encountered">
                            <label class="block text-sm font-medium text-gray-700">Challenges Encountered</label>
                            <p class="text-sm text-gray-900">{{ selectedProject.challenges_encountered }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="selectedProject.files && selectedProject.files.length > 0">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Project Files</h3>
                    <div class="grid grid-cols-1 gap-2">
                        <div v-for="(file, index) in selectedProject.files" :key="index"
                            class="flex items-center p-2 border rounded-md">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm text-blue-600 hover:text-blue-800 overflow-hidden">
                                <a :href="file" target="_blank">{{ file.split('/').pop() }}</a>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4 border-t border-gray-200">
                    <button @click="editProject(selectedProject.id)"
                        class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                        Edit Project
                    </button>
                    <button @click="deleteProjectHandler"
                        class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors text-sm font-medium"
                        :disabled="isDeleting">
                        {{ isDeleting ? 'Deleting...' : 'Delete Project' }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
@media (max-width: 640px) {
    .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}

.cursor-pointer {
    cursor: pointer;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>