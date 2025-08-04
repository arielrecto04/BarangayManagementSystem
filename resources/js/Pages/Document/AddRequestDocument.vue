<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { AuthLayout } from '@/Layouts';
import { DocumentIcon, XMarkIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { DocumentTypeEnum } from '@/Enums'
import { useDocumentRequestStore } from '@/Stores'
import useToast from '@/Utils/useToast'
import { storeToRefs } from 'pinia'

const router = useRouter();
const route = useRoute();
const documentRequestStore = useDocumentRequestStore();

const initDocumentType = route.query.documentType;
const { isSubmitting } = storeToRefs(documentRequestStore);
const { showToast } = useToast();


// Form state
const form = ref({
    document_type: '',
    requestable_type: 'Resident',
    requestor_name: '',
    requestor_contact: '',
    requestor_address: '',
    remarks: '',
    files: [],
    status: 'pending',
});

// UI State
const uploadedFiles = ref([]);
const errors = ref({});

// Request type options
const requestTypes = [
    ...DocumentTypeEnum.toArray()
];

const requestableTypes = [
    'Resident',
    'Business'
];

// Methods
const selectRequestType = (type) => {
    console.log(type, 'type selected');
    form.value.requestable_type = type;

};

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        if (!uploadedFiles.value.some(f => f.name === file.name)) {
            uploadedFiles.value.push(file);
        }
    });
    form.files = uploadedFiles.value;
};

const removeFile = (index) => {
    uploadedFiles.value.splice(index, 1);
    form.files = uploadedFiles.value;
};

const resetForm = () => {
    form.reset();
    uploadedFiles.value = [];
    errors.value = {};
};



const submitForm = async () => {
    try {
        isSubmitting.value = true;

        // Create FormData for file upload
        const formData = new FormData();

        // Add all form fields to FormData
        for (const key in form.value) {
            if (key === 'requestable') {
                // Handle nested requestable data
                for (const reqKey in form.requestable) {
                    formData.append(`requestable[${reqKey}]`, form.requestable[reqKey]);
                }
            } else if (key === 'files') {
                // Handle file uploads
                form.value.files.forEach(file => {
                    formData.append('files[]', file);
                });
            } else if (key !== 'reset' && key !== 'clearErrors') {
                formData.append(key, form.value[key]);
            }
        }

        // Submit the form
        await documentRequestStore.addDocumentRequest(formData);

        showToast({ icon: 'success', title: 'Document request created successfully' });

    } catch (error) {
        console.log(error);
        showToast({ icon: 'error', title: error.message });
    } finally {
        isSubmitting.value = false;
    }
};


onMounted(() => {
    form.value.document_type = requestTypes.find((type) => type === initDocumentType);
});
</script>


<template>
    <AuthLayout>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Form Header -->
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <router-link to="/documents/dashboard" class="text-green-700 hover:text-white hover:bg-green-700 p-2 rounded-lg transition-colors duration-200">
                        <ArrowLeftIcon class="w-4 h-4" />
                    </router-link>
                    <h2 class="text-2xl font-semibold text-gray-800">Document Request Form</h2>
                </div>
                <p class="mt-1 text-sm text-gray-600">Please fill in the required information to request a document.</p>
            </div>

            <!-- Main Form -->
            <form @submit.prevent="submitForm" class="p-6 space-y-6">
                <!-- Document Type -->
                <div>
                    <label for="document_type" class="block text-sm font-medium text-gray-700">
                        Document Type <span class="text-red-500">*</span>
                    </label>

                    <select id="document_type" v-model="form.document_type"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        :class="{ 'border-red-300 text-red-900': errors.document_type }" required>
                        <option value="">Select Document Type</option>
                        <template v-for="type in requestTypes" :key="type">
                            <option :value="type">
                                {{ type }}
                            </option>
                        </template>
                    </select>
                    <p v-if="errors.document_type" class="mt-2 text-sm text-red-600">
                        {{ errors.document_type }}
                    </p>
                </div>

                <!-- Request Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Requesting As <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div v-for="type in requestableTypes" :key="type" @click="selectRequestType(type)"
                            class="border rounded-md p-4 cursor-pointer transition-colors duration-200" :class="{
                                'border-green-700 bg-green-50': form.requestable_type === type,
                                'border-gray-300 hover:border-green-700': form.requestable_type !== type
                            }">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-5 w-5 rounded-full border flex items-center justify-center mr-3"
                                    :class="{
                                        'border-green-700': form.requestable_type === type,
                                        'border-gray-300': form.requestable_type !== type
                                    }">
                                    <div v-if="form.requestable_type === type"
                                        class="h-3 w-3 rounded-full bg-green-600"></div>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">{{ type }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requestor Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">Requestor Information</h3>

                    <!-- For Individual -->
                    <div v-if="form.requestable_type == 'Resident'">
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="first_name" v-model="form.requestor_name"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-300': errors['requestable.first_name'] }" required>
                            </div>

                        </div>

                        <div class="mt-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" v-model="form.requestor_email"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': errors['requestor_email'] }" required>
                        </div>

                        <div class="mt-4">
                            <label for="contact_number" class="block text-sm font-medium text-gray-700">
                                Contact Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="contact_number" v-model="form.requestor_contact"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': errors['requestor_contact'] }" required>
                        </div>
                    </div>

                    <!-- For Business -->
                    <div v-else-if="form.requestable_type === 'Business'">
                        <div>
                            <label for="business_name" class="block text-sm font-medium text-gray-700">
                                Business Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="business_name" v-model="form.business_name"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': errors['business_name'] }" required>
                        </div>

                        <div class="mt-4">
                            <label for="owner_name" class="block text-sm font-medium text-gray-700">
                                Owner's Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="owner_name" v-model="form.owner_name"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': errors['owner_name'] }" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="business_email" class="block text-sm font-medium text-gray-700">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="business_email" v-model="form.business_email"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-300': errors['business_email'] }" required>
                            </div>
                            <div>
                                <label for="business_contact" class="block text-sm font-medium text-gray-700">
                                    Contact Number <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="business_contact" v-model="form.business_contact"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-300': errors['business_contact'] }" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <label for="remarks" class="block text-sm font-medium text-gray-700">
                        Additional Information / Purpose
                    </label>
                    <div class="mt-1">
                        <textarea id="remarks" v-model="form.remarks" rows="3"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
                            placeholder="Please provide any additional information or specify the purpose of this request..."></textarea>
                    </div>
                </div>

                <!-- File Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Supporting Documents (if any)
                    </label>
                    <div
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48" aria-hidden="true">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload files</span>
                                    <input id="file-upload" name="file-upload" type="file" class="sr-only"
                                        @change="handleFileUpload" multiple />
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PNG, JPG, PDF up to 10MB
                            </p>
                        </div>
                    </div>
                    <div v-if="uploadedFiles.length > 0" class="mt-2">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Files:</h4>
                        <ul class="space-y-2">
                            <li v-for="(file, index) in uploadedFiles" :key="index"
                                class="flex items-center justify-between bg-gray-50 p-2 rounded-md">
                                <div class="flex items-center">
                                    <DocumentIcon class="h-5 w-5 text-gray-400 mr-2" />
                                    <span class="text-sm text-gray-700 truncate max-w-xs">{{ file.name }}</span>
                                </div>
                                <button type="button" @click="removeFile(index)"
                                    class="text-red-600 hover:text-red-800">
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" @click="resetForm"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Reset
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="isSubmitting">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Submitting...
                        </span>
                        <span v-else>Submit Request</span>
                    </button>
                </div>
            </form>
        </div>

    </AuthLayout>
</template>

<style scoped>
/* Custom styles if needed */
</style>
