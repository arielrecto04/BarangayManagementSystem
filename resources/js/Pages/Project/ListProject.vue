<script setup>
import { ref, computed, onMounted } from 'vue';
import { AuthLayout } from '@/Layouts';
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
    TableCellsIcon,
    ListBulletIcon
} from '@heroicons/vue/24/outline';
import { useProjectStore } from '@/Stores';
import { storeToRefs } from 'pinia';

const router = useRouter();
const route = useRoute();
const projectStore = useProjectStore();


const viewMode = ref(route.query.viewMode || 'grid');
const createModal = ref(false);
const { error, isLoading, projects, paginate } = storeToRefs(projectStore);
const handleViewModeChange = (mode) => {

    console.log(mode);
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

const columns = [
    {
        key: "title", label: "Title"
    },
    {
        key: "description", label: "Description"
    },
    {
        key: "start_date", label: "Start Date"
    },
    {
        key: "end_date", label: "End Date"
    },
    {
        key: "status", label: "Status"
    },
]

const filteredProjects = computed(() => {

    if (isLoading.value && projects.value.length === 0) {
        return [];
    }

    console.log(projects.value, 'hello world')

    return projects.value.filter(project => {
        const matchesSearch = project.title.toLowerCase().includes(search.value.toLowerCase()) ||
            project.description.toLowerCase().includes(search.value.toLowerCase());
        const matchesStatus = !statusFilter.value || project.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});

function formatStatus(status) {
    const statusMap = {
        'planning': 'Planning',
        'in_progress': 'In Progress',
        'on_hold': 'On Hold',
        'completed': 'Completed'
    };
    return statusMap[status] || status;
}

function getStatusColor(status) {
    const colors = {
        'planning': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'on_hold': 'bg-red-100 text-red-800',
        'completed': 'bg-green-100 text-green-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function viewProject(id) {
    // Implement view project logic
    console.log('View project:', id);
}

function editProject(id) {
    // Implement edit project logic
    console.log('Edit project:', id);
}

const createProject = async () => {
    try {
        isSubmitting.value = true;
        await projectStore.addProject(createForm.value);

    } catch (error) {
        console.log(error);

    }
    finally {
        isSubmitting.value = false;
    }
}


onMounted(async () => {
    await projectStore.getProjects();
})

</script>


<template>
    <AuthLayout>

        <div class="flex justify-between items-center p-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projects
            </h2>
            <button @click="handleCreateModalAction"
                class="p-2 bg-green-700 text-white rounded-md hover:bg-green-800 transition-colors">
                <span>+ New Project</span>
            </button>
        </div>


        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <!-- Search and Filter -->
            <div class="mb-6 flex justify-between items-center">
                <div class="w-1/3">
                    <input v-model="search" @input="handleSearch(search)" type="search" placeholder="Search projects..."
                        class="w-full border border-gray-300 rounded-md p-2 bg-white" />
                </div>
                <div class="flex item-center gap-2">
                    <select v-model="statusFilter"
                        class="rounded-md  border-gray-300 shadow-sm p-2 bg-white focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Status</option>
                        <option value="planning">Planning</option>
                        <option value="in_progress">In Progress</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                    </select>
                    <button @click="handleViewModeChange('grid')"
                        :class="viewMode === 'grid' ? 'p-2 bg-green-700 rounded-md hover:bg-green-800 transition-colors text-white' : 'p-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors'">
                        <TableCellsIcon class="w-5 h-5" />
                    </button>
                    <button @click="handleViewModeChange('list')"
                        :class="viewMode === 'list' ? 'p-2 bg-green-700 rounded-md hover:bg-green-800 transition-colors text-white' : 'p-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors'">
                        <ListBulletIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
            <template v-if="isLoading && projects.length === 0">
                <Loader />
            </template>
            <template v-else>
                <div v-if="viewMode === 'grid'">
                    <!-- Projects Grid -->

                    <div v-if="filteredProjects.length > 0"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                                    {{ project.description }}
                                </p>

                                <div class="flex items-center text-sm text-gray-500 mb-4">
                                    <CalendarIcon class="h-4 w-4 mr-1" />
                                    <span>Due: {{ formatDate(project.due_date) }}</span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center">
                                        <UserGroupIcon class="h-4 w-4 mr-1 text-gray-400" />
                                        <span>{{ 3 }} members</span>
                                    </div>
                                    <div class="flex items-center">
                                        <ClockIcon class="h-4 w-4 mr-1 text-gray-400" />
                                        <span>{{ project.progress }}% complete</span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                    <div class="flex -space-x-2">
                                        <img v-for="(member, index) in [
                                            'https://randomuser.me/api/portraits/women/44.jpg',
                                            'https://randomuser.me/api/portraits/men/32.jpg',
                                            'https://randomuser.me/api/portraits/women/68.jpg'
                                        ]" :key="index" :src="member" :alt="'Team member ' + (index + 1)"
                                            class="h-8 w-8 rounded-full border-2 border-white" />
                                    </div>
                                    <div class="flex space-x-2">
                                        <button @click="viewProject(project.id)"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            <EyeIcon class="h-5 w-5" />
                                        </button>
                                        <button @click="editProject(project.id)"
                                            class="text-blue-600 hover:text-blue-900">
                                            <PencilIcon class="h-5 w-5" />
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
                        <div class="mt-6">
                            <PrimaryButton @click="createProject">
                                <PlusIcon class="h-4 w-4 mr-2" />
                                New Project
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
                <div v-if="viewMode === 'list'">
                    <Table :columns="columns" :rows="projects" :searchable="false" :selectable="false">

                        <template #cell(description)="{ row }">
                            <p class="truncate w-52">{{ row.description }}</p>
                        </template>
                    </Table>
                </div>
            </template>

        </div>

        <Paginate @page-changed="handlePageChange" :maxVisibleButtons="5" :totalPages="paginate.last_page"
            :totalItems="paginate.total" :currentPage="paginate.current_page" :itemsPerPage="paginate.per_page" />

        <Modal title="Create New Project" :show="createModal" @close="handleCreateModalAction">


            <form action="" ref="createForm" @submit.prevent="handleCreateProject" class="flex flex-col gap-2"
                enctype="multipart/form-data">
                <div class="flex flex-col gap-2">
                    <label for="">Project Name</label>
                    <input type="text" placeholder="Enter project name" name="title" id=""
                        class="border border-green-700 rounded-md p-2 focus:outline-none focus:border-green-700 focus:ring-green-700">

                    <p v-if="error?.title" class="text-red-500 text-sm mt-1">{{ error?.title[0] }}</p>

                </div>

                <div class="flex flex-col gap-2">
                    <label for="">Description</label>
                    <textarea name="description" id="" cols="30" rows="10"
                        class="border border-green-700 rounded-md p-2 focus:outline-none focus:border-green-700 focus:ring-green-700"></textarea>
                    <p v-if="error?.description" class="text-red-500 text-sm mt-1">{{ error?.description[0] }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="">Attachments</label>
                    <input type="file" name="attachments" id=""
                        class="border border-green-700 rounded-md p-2 focus:outline-none focus:border-green-700 focus:ring-green-700">
                    <p v-if="error?.attachments" class="text-red-500 text-sm mt-1">{{ error?.attachments[0] }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="">Start Date</label>
                    <input type="date" name="start_date" id=""
                        class="border border-green-700 rounded-md p-2 focus:outline-none focus:border-green-700 focus:ring-green-700">
                    <p v-if="error?.start_date" class="text-red-500 text-sm mt-1">{{ error?.start_date[0] }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="">End Date</label>
                    <input type="date" name="end_date" id=""
                        class="border border-green-700 rounded-md p-2 focus:outline-none focus:border-green-700 focus:ring-green-700">
                    <p v-if="error?.end_date" class="text-red-500 text-sm mt-1">{{ error?.end_date[0] }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="">Status</label>
                    <select name="status" id=""
                        class="border border-green-700 rounded-md p-2 focus:outline-none focus:border-green-700 focus:ring-green-700">
                        <option value="">Select Status</option>
                        <option value="planning">Planning</option>
                        <option value="in_progress">In Progress</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                    </select>
                    <p v-if="error?.status" class="text-red-500 text-sm mt-1">{{ error?.status[0] }}</p>
                </div>

                <div class="flex justify-end gap-2">
                    <Button @click="handleCreateModalAction" :disabled="isSubmitting"
                        class="bg-gray-500 p-2 rounded-md text-white">Cancel</Button>
                    <Button @click="createProject" :disabled="isSubmitting"
                        class="bg-green-700 p-2 text-white rounded-md">
                        <span v-if="isSubmitting">Creating...</span>
                        <span v-else>Save</span>
                    </Button>
                </div>

            </form>
        </Modal>
    </AuthLayout>
</template>


<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
