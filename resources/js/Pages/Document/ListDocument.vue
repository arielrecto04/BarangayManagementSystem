<script setup>
import { ref, onMounted, computed } from 'vue';
import { AuthLayout } from '@/Layouts';
import { Modal, Loader, Table, FileType, Paginate } from '@/Components'
import { useDocumentStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { ArrowDownOnSquareIcon, TrashIcon, EyeIcon } from '@heroicons/vue/24/outline'
import VueEasyLightbox from 'vue-easy-lightbox'
import { formatFileSize } from '@/Utils';
import { useRoute, useRouter } from 'vue-router';
import { Squares2X2Icon, TableCellsIcon } from '@heroicons/vue/24/outline'

// Instantiate section
const documentStore = useDocumentStore();
const route = useRoute();
const router = useRouter();

// Ref Section
const showUploadModal = ref(false);
const uploadForm = ref(null);
const searchQuery = ref('');
const documentToUpload = ref([]);
const { documents, isLoading, paginate } = storeToRefs(documentStore);
const viewType = ref(route.query.viewType || 'list');

// Lightbox state
const lightboxVisible = ref(false);
const lightboxIndex = ref(0);
const lightboxSources = computed(() =>
    documents.value.map(doc => ({
        src: doc.file_path,
        title: doc.file_name,
        type: 'iframe'
    }))
);

// Columns definition
const columns = [
    { label: 'File Name', key: 'file_name' },
    { label: 'File Size', key: 'file_sizes' },
    { label: 'Uploaded By', key: 'uploaded_by' }
];

// Quick access data
const quickAccess = [
    { id: 1, name: 'Shared with me', itemCount: 24 },
    { id: 2, name: 'Recent', itemCount: 15 },
    { id: 3, name: 'Starred', itemCount: 8 },
    { id: 4, name: 'Trash', itemCount: 3 }
];

// Methods
const changeViewType = (type) => {
    viewType.value = type;
    router.replace({ query: { viewType: type } });
}

const getDocuments = (e) => {
    const files = e.target.files;
    documentToUpload.value = Array.from(files).map(file => ({
        name: file.name,
        type: file.type,
        size: file.size,
        file: file,
        progress: 0,
        status: 'pending'
    }));
}

const uploadModalAction = () => {
    showUploadModal.value = !showUploadModal.value;
}

const uploadDocuments = async () => {
    const uploadPromises = documentToUpload.value.map(doc => {
        const formData = new FormData();
        formData.append('document', doc.file);
        doc.status = 'uploading';

        return documentStore.addDocument(formData, (progressEvent) => {
            doc.progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        }).then(() => {
            doc.status = 'uploaded';
        }).catch((error) => {
            doc.status = 'failed';
        });
    });

    await Promise.all(uploadPromises);
}

// Lightbox methods
const showLightbox = (index) => {
    lightboxIndex.value = index;
    lightboxVisible.value = true;
}

// Initialize documents
onMounted(() => {
    documentStore.getDocuments();
})
</script>

<template>
    <AuthLayout>
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-800">My Drive</h1>
                <div class="flex space-x-3">
                    <button @click="uploadModalAction"
                        class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        New
                    </button>
                    <button @click="changeViewType('list')"
                        :class="viewType === 'list' ? 'p-2 bg-green-700 text-white rounded-lg hover:bg-green-800' : 'p-2 bg-gray-100 rounded-lg hover:bg-green-700 hover:text-white'"
                        class="duration-300">
                        <TableCellsIcon class="h-5 w-5" />
                    </button>
                    <button @click="changeViewType('grid')"
                        :class="viewType === 'grid' ? 'p-2 bg-green-700 text-white rounded-lg hover:bg-green-800' : 'p-2 bg-gray-100 rounded-lg hover:bg-green-700 hover:text-white'"
                        class="duration-300">
                        <Squares2X2Icon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="mb-6 flex justify-between items-center">
                <div class="relative w-1/3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" v-model="searchQuery"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-transparent"
                        placeholder="Search in Drive">
                </div>
                <div class="flex space-x-2">
                    <select
                        class="border border-gray-100 shadow-sm bg-white rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-transparent py-2 text-sm text-gray-600">
                        <option>Type</option>
                        <option>Documents</option>
                        <option>Spreadsheets</option>
                        <option>Presentations</option>
                    </select>
                    <select
                        class="border border-gray-100 shadow-sm bg-white rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-transparent py-2 text-sm text-gray-600">
                        <option>Last modified</option>
                        <option>Name (A-Z)</option>
                        <option>Name (Z-A)</option>
                    </select>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="mb-8">
                <h2 class="text-sm font-medium text-gray-500 mb-4">QUICK ACCESS</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="folder in quickAccess" :key="folder.id"
                        class="p-4 bg-white shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">{{ folder.name }}</h3>
                                <p class="text-sm text-gray-500">{{ folder.itemCount }} items</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files and Folders -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-sm font-medium text-gray-500">MY FILES</h2>
                    <button class="text-sm text-blue-600 hover:text-blue-800">View all</button>
                </div>

                <Loader v-if="isLoading" />

                <!-- Table View -->
                <Table v-show="viewType === 'list'" :columns="columns" :rows="documents" :searchable="false"
                    :selectable="false">

                    <template #cell(file_name)="{ row }">
                        <div class="flex items-center gap-2">
                            <FileType :type="row.file_type" />
                            {{ row.file_name }}
                        </div>
                    </template>
                    <template #cell(uploaded_by)="{ row }">
                        <div class="flex items-center gap-2">
                            <img :src="`https://ui-avatars.com/api/?name=${row.uploaded_by.name}`" alt=""
                                class="w-10 h-10 rounded-full">
                            {{ row.uploaded_by.name }}
                        </div>
                    </template>
                    <template #cell(file_sizes)="{ row }">
                        {{ formatFileSize(row.file_sizes) }}
                    </template>
                    <template #actions="{ row }">
                        <button @click="showLightbox(documents.indexOf(row))"
                            class="p-2 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <EyeIcon class="w-5 h-5" />
                        </button>
                        <button @click="downloadDocument(row)" class="p-2 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <ArrowDownOnSquareIcon class="w-5 h-5" />
                        </button>
                        <button @click="deleteDocument(row)" class="p-2 bg-red-500 rounded-lg hover:bg-red-600">
                            <TrashIcon class="w-5 h-5 text-white" />
                        </button>
                    </template>
                </Table>

                <!-- Grid View -->
                <div v-show="viewType === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="(document, index) in documents" :key="document.id"
                        class="p-4 bg-white shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer"
                        @click="showLightbox(index)">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <FileType :type="document.file_type" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">{{ document.file_name }}</h3>
                                <p class="text-sm text-gray-500">{{ formatFileSize(document.file_sizes) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <template v-if="documents.length === 0">
                    <div class="flex items-center justify-center h-full">
                        <p class="text-gray-600">No documents found</p>
                    </div>
                </template>

                <Paginate :maxVisibleButtons="5" :totalPages="paginate.total" :totalItems="paginate.total"
                    :itemsPerPage="paginate.per_page" :currentPage="paginate.current_page"
                    @page-changed="documentStore.getDocuments" />
            </div>
        </div>

        <!-- Upload Modal -->
        <Modal title="Upload Document" :show="showUploadModal" @close="uploadModalAction">
            <form ref="uploadForm" @submit.prevent="uploadDocuments" enctype="multipart/form-data">
                <label for="documents"
                    class="text-sm font-medium text-gray-700 w-full border border-gray-200 rounded-md p-2 items-center flex cursor-pointer h-12 gap-5 ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="flex items-center">Choose file</span>
                    <input type="file" @change="getDocuments($event)" hidden name="documents" id="documents" multiple
                        class="mt-1 block w-full">
                </label>

                <div v-if="documentToUpload.length" class="mt-4">
                    <ul>
                        <li v-for="document in documentToUpload" :key="document.name">
                            <div class="flex flex-col gap-2 p-2 border border-gray-200 rounded-md mb-2">
                                <p class="font-medium">{{ document.name }}</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm text-gray-500">{{ document.type }}</p>
                                    <p class="text-sm text-gray-500">{{ formatFileSize(document.size) }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <p :class="{
                                        'text-sm font-medium': true,
                                        'text-green-500': document.status === 'uploaded' || document.status === 'uploading',
                                        'text-red-500': document.status === 'failed'
                                    }">
                                        {{ document.progress }} %
                                    </p>
                                    <p :class="{
                                        'text-sm font-medium': true,
                                        'text-green-500': document.status === 'uploaded',
                                        'text-blue-500': document.status === 'uploading',
                                        'text-red-500': document.status === 'failed'
                                    }">
                                        {{ document.status }}
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button type="submit"
                        class="mt-4 inline-flex justify-center rounded-md border border-transparent bg-green-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-green-700">Upload</button>
                    <button type="button" @click="uploadModalAction"
                        class="mt-4 inline-flex justify-center rounded-md border border-transparent bg-gray-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-gray-700">Cancel</button>
                </div>
            </form>
        </Modal>

        <!-- Lightbox Component -->
        <VueEasyLightbox :visible="lightboxVisible" :imgs="lightboxSources" :index="lightboxIndex"
            @hide="lightboxVisible = false" />
    </AuthLayout>
</template>

<style scoped>
/* Add any custom styles here */
</style>