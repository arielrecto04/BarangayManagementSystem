<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router'
import { Modal, Loader, Paginate, Table } from '@/Components'
import { debounce } from '@/Utils';

import {
    CalendarIcon,
    ClockIcon,
    EyeIcon,
    PencilIcon,
    PlusIcon,
    UserGroupIcon,
    FolderOpenIcon,
    Squares2X2Icon,
    TableCellsIcon,
} from '@heroicons/vue/24/outline';
import { useProjectStore } from '@/Stores';
import { storeToRefs } from 'pinia';

const router = useRouter();
const route = useRoute();
const projectStore = useProjectStore();
const isDeleting = ref(false);

const viewMode = ref(route.query.viewMode || 'grid');
const createModal = ref(false);
const viewModal = ref(false);
const selectedProject = ref(null);
const { error, isLoading, projects, paginate } = storeToRefs(projectStore);

const deleteProjectHandler = async () => {
    if (!selectedProject.value) return;

    if (!confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        return;
    }

    isDeleting.value = true;
    try {
        await projectStore.deleteProject(selectedProject.value.id);
        viewModal.value = false;
        await projectStore.getProjects(currentPage.value);
    } catch (error) {
        console.error('Error deleting project:', error);
        alert('Failed to delete project. Please try again.');
    } finally {
        isDeleting.value = false;
    }
}

const handleViewModeChange = (mode) => {
    viewMode.value = mode;
    router.replace({
        query: {
            viewMode: viewMode.value,
            page: currentPage.value
        }
    })
}

const handleSearch = debounce((query) => {
    projectStore.searchProjects(query);
}, 300);

const handleCreateModalAction = () => {
    createModal.value = !createModal.value;
    if (!createModal.value) {
        // Reset errors when closing modal
        projectStore._error = null;
    }
}

const currentPage = ref(route.query.page || 1);

const handlePageChange = (page) => {
    currentPage.value = page;
    projectStore.getProjects(currentPage.value);

    router.replace({
        query: {
            page: currentPage.value,
            viewMode: viewMode.value
        }
    })
}

const search = ref('');
const statusFilter = ref('');
const createForm = ref(null);
const isSubmitting = ref(false);

// Updated columns to match database fields - removed actions column
const columns = [
    { key: "title", label: "Title" },
    { key: "description", label: "Description" },
    { key: "start_date", label: "Start Date" },
    { key: "target_completion", label: "Target Completion" },
    { key: "status", label: "Status" },
    { key: "completion_percentage", label: "Progress" }
]

const filteredProjects = computed(() => {
    if (isLoading.value && projects.value.length === 0) {
        return [];
    }

    return projects.value.filter(project => {
        const matchesSearch = project.title.toLowerCase().includes(search.value.toLowerCase()) ||
            (project.description && project.description.toLowerCase().includes(search.value.toLowerCase()));
        const matchesStatus = !statusFilter.value || project.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});

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
    selectedProject.value = project;
    viewModal.value = true;
}

function editProject(id) {
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
}

onMounted(async () => {
    await projectStore.getProjects();
})
</script>

<template>
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <!-- Search and Filter -->
        <div class="mb-6 flex justify-between items-center">
            <div class="w-1/3">
                <input v-model="search" @input="handleSearch(search)" type="search" placeholder="Search projects..."
                    class="w-full border border-gray-300 rounded-md p-2 bg-white" />
            </div>
            <div class="flex item-center gap-2">
                <select v-model="statusFilter"
                    class="rounded-md border-gray-300 shadow-sm p-2 bg-white focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="planning">Planning</option>
                    <option value="in_progress">In Progress</option>
                    <option value="on_hold">On Hold</option>
                    <option value="completed">Completed</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="pending">Pending</option>
                </select>
                <button @click="handleViewModeChange('grid')"
                    :class="viewMode === 'grid' ? 'p-2 bg-green-700 rounded-md hover:bg-green-800 transition-colors text-white' : 'p-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors'">
                    <Squares2X2Icon class="w-5 h-5" />
                </button>
                <button @click="handleViewModeChange('list')"
                    :class="viewMode === 'list' ? 'p-2 bg-green-700 rounded-md hover:bg-green-800 transition-colors text-white' : 'p-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors'">
                    <TableCellsIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <template v-if="isLoading && projects.length === 0">
            <Loader />
        </template>
        <template v-else>
            <div v-if="viewMode === 'grid'">
                <!-- Projects Grid -->
                <div v-if="filteredProjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="project in filteredProjects" :key="project.id"
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    {{ project.title }}
                                </h3>
                                <span :class="`px-2 py-1 text-xs rounded-full ${getStatusColor(project.status)}`">
                                    {{ formatStatus(project.status) }}
                                </span>
                            </div>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ project.description || 'No description available' }}
                            </p>

                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <CalendarIcon class="h-4 w-4 mr-1" />
                                <span>Target: {{ formatDate(project.target_completion) }}</span>
                            </div>

                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <CalendarIcon class="h-4 w-4 mr-1" />
                                <span>Actual Completion: {{ formatDate(project.actual_completion) }}</span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <UserGroupIcon class="h-4 w-4 mr-1 text-gray-400" />
                                    <span>{{ project.number_of_members || 0 }} members</span>
                                </div>
                                <div class="flex items-center">
                                    <ClockIcon class="h-4 w-4 mr-1 text-gray-400" />
                                    <span>{{ project.completion_percentage || 0 }}% complete</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    Lead: {{ project.project_lead || 'N/A' }}
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="viewProject(project)" class="text-indigo-600 hover:text-indigo-900">
                                        <EyeIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <FolderOpenIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No projects found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new project.</p>
                </div>
            </div>

            <div v-if="viewMode === 'list'">
                <Table :columns="columns" :rows="filteredProjects" :searchable="false" :selectable="false">
                    <template #cell(description)="{ row }">
                        <p class="truncate w-52">{{ row.description || 'No description' }}</p>
                    </template>
                    <template #cell(start_date)="{ row }">
                        <span>{{ formatDate(row.start_date) }}</span>
                    </template>
                    <template #cell(target_completion)="{ row }">
                        <span>{{ formatDate(row.target_completion) }}</span>
                    </template>
                    <template #cell(status)="{ row }">
                        <span :class="`px-2 py-1 text-xs rounded-full ${getStatusColor(row.status)}`">
                            {{ formatStatus(row.status) }}
                        </span>
                    </template>
                    <template #cell(completion_percentage)="{ row }">
                        <span>{{ row.completion_percentage || 0 }}%</span>
                    </template>
                    <template #actions="{ row }">
                        <div class="flex gap-2">
                            <!-- View Button -->
                            <button @click="viewProject(row)" title="View"
                                class="text-gray-600 p-2 rounded text-sm transition-transform flex items-center justify-center hover:scale-125">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </Table>
            </div>
        </template>
    </div>

    <Paginate @page-changed="handlePageChange" :maxVisibleButtons="5" :totalPages="paginate.last_page"
        :totalItems="paginate.total" :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />

    <!-- View Project Details Modal -->
    <Modal title="Project Details" :show="viewModal" @close="viewModal = false" max-width="4xl">
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
                                available` }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <p class="text-sm text-gray-900">{{ selectedProject.category || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <span :class="`px-2 py-1 text-xs rounded-full ${getStatusColor(selectedProject.status)}`">
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
                            <p class="text-sm text-gray-900">{{ formatDate(selectedProject.target_completion) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Actual Completion</label>
                            <p class="text-sm text-gray-900">{{ formatDate(selectedProject.actual_completion) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Progress</label>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-600 h-2 rounded-full"
                                        :style="`width: ${selectedProject.completion_percentage || 0}%`"></div>
                                </div>
                                <span class="text-sm text-gray-900">{{ selectedProject.completion_percentage || 0
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
                            <p class="text-sm text-gray-900">{{ selectedProject.number_of_members || 0 }} members</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Funding Source</label>
                            <p class="text-sm text-gray-900">{{ selectedProject.funding_source || 'N/A' }}</p>
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
                        <span class="text-sm text-blue-600 hover:text-blue-800">
                            <a :href="file" target="_blank">{{ file.split('/').pop() }}</a>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <button @click="deleteProjectHandler"
                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700" :disabled="isDeleting">
                    {{ isDeleting ? 'Deleting...' : 'Delete Project' }}
                </button>
                <button @click="editProject(selectedProject.id)"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    Edit Project
                </button>
                <button @click="viewModal = false"
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Close
                </button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>