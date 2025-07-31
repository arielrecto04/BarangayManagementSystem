<script setup>
import { ref, watchEffect } from 'vue';
import {AuthLayout} from '@/Layouts';
import { Modal } from '@/Components'
import { useDocumentStore } from '@/Stores';
import { storeToRefs } from 'pinia';


//instantiate section
const documentStore = useDocumentStore();




//Ref Section
const showUploadModal = ref(false);
const uploadForm = ref(null);
const searchQuery = ref('');
const documentToUpload = ref([]);
const { documents, isLoading } = storeToRefs(documentStore);



const getDocuments = (e) => {
    const files = e.target.files;

    documentToUpload.value = Array.from(files).map(file => ({
        name: file.name,
        type: file.type,
        size: file.size,
        file: file,
        lastModified: file.lastModified,
        progress: 0,
        status: 'pending'
    }));


    console.log(documentToUpload.value);
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
    console.log(documentToUpload.value);
  const uploadPromises  = documentToUpload.value.map(doc => {

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

  try {
    await Promise.all(uploadPromises);
    showUploadModal.value = false;
    documentToUpload.value = [];
    uploadModalAction();
  } catch (error) {
    console.error(error);
  }
}

// Hardcoded data
const quickAccess = [
    { id: 1, name: 'Shared with me', itemCount: 24 },
    { id: 2, name: 'Recent', itemCount: 15 },
    { id: 3, name: 'Starred', itemCount: 8 },
    { id: 4, name: 'Trash', itemCount: 3 }
];

const files = [
    {
        id: 1,
        name: 'Annual Report 2023.pdf',
        type: 'PDF',
        owner: 'John Doe',
        modified: '2023-11-15T14:30:00Z',
        size: 2457600
    },
    {
        id: 2,
        name: 'Project Proposal.docx',
        type: 'Document',
        owner: 'Jane Smith',
        modified: '2023-11-14T09:15:00Z',
        size: 512000
    },
    {
        id: 3,
        name: 'Budget Q4.xlsx',
        type: 'Spreadsheet',
        owner: 'Mike Johnson',
        modified: '2023-11-10T16:45:00Z',
        size: 1024000
    },
    {
        id: 4,
        name: 'Team Meeting.pptx',
        type: 'Presentation',
        owner: 'Sarah Williams',
        modified: '2023-11-08T11:20:00Z',
        size: 3072000
    },
    {
        id: 5,
        name: 'Design Assets',
        type: 'Folder',
        owner: 'Alex Chen',
        modified: '2023-11-05T13:10:00Z',
        size: 0
    }
];





// Helper functions
const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '--';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};
</script>


<template>
    <AuthLayout>
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-800">My Drive</h1>
                <div class="flex space-x-3">
                    <button @click="uploadModalAction" class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New
                    </button>
                    <button class="p-2 border rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="mb-6 flex justify-between items-center">
                <div class="relative w-1/3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" v-model="searchQuery" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-transparent" placeholder="Search in Drive">
                </div>
                <div class="flex space-x-2">
                    <select class="border border-gray-100 shadow-sm bg-white rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-transparent py-2 text-sm text-gray-600">
                        <option>Type</option>
                        <option>Documents</option>
                        <option>Spreadsheets</option>
                        <option>Presentations</option>
                    </select>
                        <select class="border border-gray-100 shadow-sm bg-white rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-transparent py-2 text-sm text-gray-600">
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
                    <div v-for="folder in quickAccess" :key="folder.id" class="p-4 bg-white shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
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
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last modified</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File size</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in files" :key="item.id" class="hover:bg-gray-50 cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 text-gray-400">
                                            <FileIcon :type="item.type" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                                            <div class="text-sm text-gray-500">{{ item.type }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-medium">
                                            {{ getInitials(item.owner) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm text-gray-900">{{ item.owner }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(item.modified) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatFileSize(item.size) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click.stop class="text-gray-400 hover:text-gray-600 mr-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal title="Upload Document" :show="showUploadModal" @close="uploadModalAction">
            <form ref="uploadForm" @submit.prevent="uploadDocuments" enctype="multipart/form-data">

                <label for="documents" class="text-sm font-medium text-gray-700 w-full border border-gray-200 rounded-md p-2 items-center flex cursor-pointer h-12 gap-5 ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="flex items-center">Choose file</span>
                    <input type="file" @change="getDocuments($event)" hidden name="documents" id="documents" multiple class="mt-1 block w-full">
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
                                <p :class="document.status === 'uploading' ? 'text-sm text-green-500 font-medium' : 'text-sm text-red-500 font-medium'">{{ document.progress }} %</p>
                                <p :class="document.status === 'uploading' ? 'text-sm text-green-500 font-medium' : 'text-sm text-red-500 font-medium'">{{ document.status }}</p>
                            </div>
                           </div>
                        </li>
                    </ul>
                </div>

                <button type="submit" class="mt-4 inline-flex justify-center rounded-md border border-transparent bg-green-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-green-700">Upload</button>
            </form>
        </Modal>
    </AuthLayout>
</template>


<style scoped>
/* Add any custom styles here */
</style>
