<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { AuthLayout } from '@/Layouts';
import { Modal, Loader, Table, FileType, Paginate } from '@/Components'
import { useDocumentStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { ArrowDownOnSquareIcon, TrashIcon, EyeIcon } from '@heroicons/vue/24/outline'
import VenoBox from 'venobox';
import { formatFileSize } from '@/Utils';
import { useRoute, useRouter } from 'vue-router';
import { Squares2X2Icon, TableCellsIcon } from '@heroicons/vue/24/outline'

//instantiate section
const documentStore = useDocumentStore();
const route = useRoute();
const router = useRouter();




//Ref Section
const showUploadModal = ref(false);
const uploadForm = ref(null);
const searchQuery = ref('');
const documentToUpload = ref([]);
const { documents, isLoading, paginate } = storeToRefs(documentStore);
const venoBox = ref(null);
const viewType = ref(route.query.viewType || 'list');
const columns = [
    {
        label: 'File Name',
        key: 'file_name',
    },
    {
        label: 'File Size',
        key: 'file_sizes',
    },
    {
        label: 'Uploaded By',
        key: 'uploaded_by',
    }
];
const quickAccess = [
    { id: 1, name: 'Shared with me', itemCount: 24 },
    { id: 2, name: 'Recent', itemCount: 15 },
    { id: 3, name: 'Starred', itemCount: 8 },
    { id: 4, name: 'Trash', itemCount: 3 }
];


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
        lastModified: file.lastModified,
        progress: 0,
        status: 'pending',
        error: null
    }));


    console.log(documentToUpload.value, 'documentToUpload.value in getDocuments');
}

// File icon component
const FileIcon = ({ type }) => {
    const iconClass = 'h-8 w-8';

    // switch(type.toLowerCase()) {
    //     case 'pdf':
    //         return (
    //             <svg class={`${iconClass} text-red-500`} viewBox="0 0 24 24" fill="currentColor">
    //                 <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 20V4h7v5h5v11H6zm4-8h2v5h-2v-5zm0-3h2v2h-2V9zm0 6h2v2h-2v-2z"/>
    //             </svg>
    //         );
    //     case 'document':
    //         return (
    //             <svg class={`${iconClass} text-blue-500`} viewBox="0 0 24 24" fill="currentColor">
    //                 <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 4h7v5h5v11H6V4zm8 14h-2v-5h2v5zm0-7h-2V9h2v2z"/>
    //             </svg>
    //         );
    //     case 'spreadsheet':
    //         return (
    //             <svg class={`${iconClass} text-green-500`} viewBox="0 0 24 24" fill="currentColor">
    //                 <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 4h7v5h5v11H6V4zm4 14v-4h4v4h-4z"/>
    //             </svg>
    //         );
    //     case 'presentation':
    //         return (
    //             <svg class={`${iconClass} text-yellow-500`} viewBox="0 0 24 24" fill="currentColor">
    //                 <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 4h7v5h5v11H6V4zm8 14v-4h4v4h-4z"/>
    //             </svg>
    //         );
    //     case 'folder':
    //         return (
    //             <svg class={`${iconClass} text-yellow-400`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
    //                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
    //             </svg>
    //         );
    //     default:
    //         return (
    //             <svg class={`${iconClass} text-gray-400`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
    //                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    //             </svg>
    //         );
    // }
};

const uploadModalAction = () => {
    showUploadModal.value = !showUploadModal.value;
}



const uploadDocuments = async () => {
    console.log(documentToUpload.value, 'documentToUpload.value in uploadDocuments');
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
            doc.error = error;
        });
    });

    try {
        await Promise.all(uploadPromises);

    } catch (error) {
        console.error(error);
    }
}








// Helper functions
const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};




onMounted(() => {
    documentStore.getDocuments();
    venoBox.value = new VenoBox({
        selector: '.venobox',
        numeration: true,
        infinigall: true,
    });
})


watch(() => documentStore.documents, () => {
    if (documentStore.documents.length > 0) {
        nextTick(() => {
            venoBox.value = new VenoBox({
                selector: '.venobox',
                numeration: true,
                infinigall: true,
            });
        })
    }
})


onUnmounted(() => {
    // venoBox.value.destroy();
    venoBox.value.close();
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



                <!-- Table View -->

                <Loader v-if="isLoading" />



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


                        <button :data-href="row.file_path" class="venobox p-2 bg-gray-50 rounded-lg hover:bg-gray-100"
                            data-gall="document-gallery" data-vbtype="iframe">
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



                <d v-show="viewType === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="document in documents" :key="document.id"
                        class="p-4 bg-white shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer">
                        <a :data-href="document.file_path" data-gall="document-gallery" data-vbtype="iframe"
                            class="venobox flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <FileType :type="document.file_type" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">{{ document.file_name }}</h3>
                                <p class="text-sm text-gray-500">{{ document.file_sizes }}</p>
                            </div>
                        </a>
                    </div>
                </d>


                <template v-if="documents.length === 0">
                    <div class="flex items-center justify-center h-full">
                        <p class="text-gray-600">No documents found</p>
                    </div>
                </template>

                <Paginate :maxVisibleButtons="5" :totalPages="paginate.total" :totalItems="paginate.total"
                    :itemsPerPage="paginate.per_page" :currentPage="paginate.current_page"
                    @page-changed="getDocuments" />
            </div>
        </div>

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
                                    <p
                                        :class="document.status === 'uploaded' || document.status === 'uploading' ? 'text-sm text-green-500 font-medium' : 'text-sm text-red-500 font-medium'">
                                        {{ document.progress }} %</p>
                                    <p
                                        :class="document.status === 'uploaded' || document.status === 'uploading' ? 'text-sm text-green-500 font-medium' : 'text-sm text-red-500 font-medium'">
                                        {{ document.status }}</p>
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
    </AuthLayout>
</template>


<style scoped>
/* Add any custom styles here */
</style>
