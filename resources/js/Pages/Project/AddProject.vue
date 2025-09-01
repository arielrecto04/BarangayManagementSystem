<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/Stores';
import { Loader } from '@/Components';
import { storeToRefs } from 'pinia';
import useToast from '@/Utils/useToast';

const { showToast } = useToast();
const router = useRouter();
const projectStore = useProjectStore();
const { isLoading, error } = storeToRefs(projectStore);

// Form data matching the database schema
const form = ref({
    title: '',
    description: '',
    category: '',
    status: 'planning',
    start_date: '',
    target_completion: '',
    actual_completion: '',
    funding_source: '',
    barangay_zone: '',
    completion_percentage: 0,
    milestone_achieved: '',
    project_lead: '',
    assigned_organizations: [],
    number_of_members: 0,
    site_address: '',
    disbursement_schedule: '',
    challenges_encountered: '',
    files: []
});

const isSubmitting = ref(false);

const handleSubmit = async () => {
    try {
        isSubmitting.value = true;

        // Validate required fields
        if (!form.value.title.trim()) {
            showToast({ icon: 'error', title: 'Project title is required.' });
            return;
        }

        // Create FormData for file uploads
        const formData = new FormData();

        // Add all form fields to FormData (except files)
        Object.keys(form.value).forEach(key => {
            if (key === 'files') {
                // Skip files here, handle separately
                return;
            } else if (key === 'assigned_organizations') {
                // Handle assigned organizations array
                if (Array.isArray(form.value[key]) && form.value[key].length > 0) {
                    form.value[key].forEach((org, index) => {
                        formData.append(`assigned_organizations[${index}]`, org);
                    });
                }
            } else if (form.value[key] !== null && form.value[key] !== undefined && form.value[key] !== '') {
                formData.append(key, form.value[key]);
            }
        });

        // Handle files separately with better validation
        if (form.value.files && form.value.files.length > 0) {
            // Validate each file before adding
            const validFiles = [];
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            const maxSize = 10 * 1024 * 1024; // 10MB

            for (let i = 0; i < form.value.files.length; i++) {
                const file = form.value.files[i];

                // Check if it's actually a File object
                if (!(file instanceof File)) {
                    console.error('Invalid file object at index', i);
                    continue;
                }

                // Validate file type
                if (!allowedTypes.includes(file.type)) {
                    showToast({
                        icon: 'error',
                        title: `Invalid file type: ${file.name}. Allowed types: JPG, PNG, PDF, DOC, DOCX`
                    });
                    return;
                }

                // Validate file size
                if (file.size > maxSize) {
                    showToast({
                        icon: 'error',
                        title: `File too large: ${file.name}. Maximum size is 10MB`
                    });
                    return;
                }

                // Validate file is not empty
                if (file.size === 0) {
                    showToast({
                        icon: 'error',
                        title: `Empty file: ${file.name}`
                    });
                    return;
                }

                validFiles.push(file);
            }

            // Add validated files to FormData
            validFiles.forEach((file, index) => {
                formData.append('files[]', file, file.name);
            });

            console.log(`Added ${validFiles.length} valid files to form data`);
        } else {
            console.log('No files to upload');
        }

        // Debug: Log FormData contents
        console.log('FormData contents:');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                console.log(key, `File: ${value.name} (${value.size} bytes, ${value.type})`);
            } else {
                console.log(key, value);
            }
        }

        // Set proper headers for file upload
        const config = {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        };

        await projectStore.addProject(formData, config);

        if (!error.value) {
            showToast({ icon: 'success', title: 'Project created successfully!' });
            router.push({ name: 'List Projects' });
        } else {
            // Handle specific validation errors
            if (error.value['files.0']) {
                showToast({
                    icon: 'error',
                    title: `File validation error: ${error.value['files.0'][0]}`
                });
            } else {
                showToast({ icon: 'error', title: 'Failed to create project. Please check the form.' });
            }
        }
    } catch (err) {
        console.error('Error creating project:', err);

        // Log detailed error information
        if (err.response?.data?.errors) {
            console.error('Validation errors:', err.response.data.errors);

            // Show specific validation errors
            const errors = err.response.data.errors;

            // Handle file-specific errors
            if (errors['files.0']) {
                showToast({
                    icon: 'error',
                    title: `File error: ${errors['files.0'][0]}`
                });
                return;
            }

            // Handle other validation errors
            const firstErrorKey = Object.keys(errors)[0];
            if (firstErrorKey && errors[firstErrorKey]) {
                const errorMessage = Array.isArray(errors[firstErrorKey])
                    ? errors[firstErrorKey][0]
                    : errors[firstErrorKey];

                showToast({
                    icon: 'error',
                    title: `${firstErrorKey.replace('_', ' ')}: ${errorMessage}`
                });
                return;
            }
        }

        showToast({
            icon: 'error',
            title: 'An unexpected error occurred while creating the project.'
        });
    } finally {
        isSubmitting.value = false;
    }
};

const handleFileUpload = (event) => {
    const newFiles = Array.from(event.target.files);

    // Validate file types and sizes
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    const maxSize = 10 * 1024 * 1024; // 10MB

    const validFiles = [];
    const invalidFiles = [];

    newFiles.forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            invalidFiles.push(`${file.name} - Invalid file type`);
        } else if (file.size > maxSize) {
            invalidFiles.push(`${file.name} - File too large (max 10MB)`);
        } else {
            validFiles.push(file);
        }
    });

    if (invalidFiles.length > 0) {
        showToast({
            icon: 'error',
            title: `Invalid files: ${invalidFiles.join(', ')}`
        });
    }

    // Remove duplicate files by name
    const existingNames = new Set(form.value.files.map(file => file.name));
    const uniqueValidFiles = validFiles.filter(file => !existingNames.has(file.name));

    form.value.files = [...form.value.files, ...uniqueValidFiles];

    console.log('Files selected:', form.value.files.map(f => ({
        name: f.name,
        size: f.size,
        type: f.type
    })));

    // Clear the input
    event.target.value = '';
};

const removeFile = (index) => {
    form.value.files.splice(index, 1);
};

// Organization management
const newOrganization = ref('');

const addOrganization = () => {
    if (newOrganization.value.trim()) {
        form.value.assigned_organizations.push(newOrganization.value.trim());
        newOrganization.value = '';
    }
};

const removeOrganization = (index) => {
    form.value.assigned_organizations.splice(index, 1);
};

// Clear errors when user starts typing
const clearFieldError = (fieldName) => {
    if (error.value && error.value[fieldName]) {
        projectStore.clearError();
    }
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Add New Project</h2>
            <button @click="router.back()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Title *</label>
                    <input v-model="form.title" type="text" required @input="clearFieldError('title')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.title" class="text-red-500 text-sm mt-1">{{ error.title[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input v-model="form.category" type="text" @input="clearFieldError('category')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="e.g., Infrastructure, Education, Health">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select v-model="form.status" @change="clearFieldError('status')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                        <option value="planning">Planning</option>
                        <option value="in_progress">In Progress</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="pending">Pending</option>
                    </select>
                    <p v-if="error?.status" class="text-red-500 text-sm mt-1">{{ error.status[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Funding Source</label>
                    <input v-model="form.funding_source" type="text" @input="clearFieldError('funding_source')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="e.g., Government, Private, NGO">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="form.description" rows="4" @input="clearFieldError('description')"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Provide a detailed description of the project..."></textarea>
                <p v-if="error?.description" class="text-red-500 text-sm mt-1">{{ error.description[0] }}</p>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input v-model="form.start_date" type="date" @input="clearFieldError('start_date')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.start_date" class="text-red-500 text-sm mt-1">{{ error.start_date[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Completion</label>
                    <input v-model="form.target_completion" type="date" @input="clearFieldError('target_completion')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.target_completion" class="text-red-500 text-sm mt-1">{{ error.target_completion[0]
                        }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actual Completion</label>
                    <input v-model="form.actual_completion" type="date" @input="clearFieldError('actual_completion')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.actual_completion" class="text-red-500 text-sm mt-1">{{ error.actual_completion[0]
                        }}</p>
                </div>
            </div>

            <!-- Progress and Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Completion Percentage</label>
                    <input v-model="form.completion_percentage" type="number" min="0" max="100"
                        @input="clearFieldError('completion_percentage')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.completion_percentage" class="text-red-500 text-sm mt-1">{{
                        error.completion_percentage[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barangay/Zone</label>
                    <input v-model="form.barangay_zone" type="text" @input="clearFieldError('barangay_zone')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Enter barangay or zone">
                </div>
            </div>

            <!-- Team Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Lead</label>
                    <input v-model="form.project_lead" type="text" @input="clearFieldError('project_lead')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Name of project leader">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Members</label>
                    <input v-model="form.number_of_members" type="number" min="0"
                        @input="clearFieldError('number_of_members')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Assigned Organizations -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Organizations</label>
                <div class="flex gap-2 mb-2">
                    <input v-model="newOrganization" type="text" @keyup.enter="addOrganization"
                        class="flex-1 border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Enter organization name">
                    <button type="button" @click="addOrganization"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Add
                    </button>
                </div>
                <div v-if="form.assigned_organizations.length > 0" class="space-y-2">
                    <div v-for="(org, index) in form.assigned_organizations" :key="index"
                        class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                        <span class="text-sm">{{ org }}</span>
                        <button type="button" @click="removeOrganization(index)"
                            class="text-red-600 hover:text-red-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Address</label>
                <input v-model="form.site_address" type="text" @input="clearFieldError('site_address')"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Full address of the project site">
            </div>

            <!-- Additional Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Milestones Achieved</label>
                    <textarea v-model="form.milestone_achieved" rows="3" @input="clearFieldError('milestone_achieved')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="List key milestones achieved..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Challenges Encountered</label>
                    <textarea v-model="form.challenges_encountered" rows="3"
                        @input="clearFieldError('challenges_encountered')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Describe any challenges faced..."></textarea>
                </div>
            </div>

            <!-- Disbursement Schedule -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Disbursement Schedule</label>
                <textarea v-model="form.disbursement_schedule" rows="3"
                    @input="clearFieldError('disbursement_schedule')"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Describe the funding disbursement schedule..."></textarea>
            </div>

            <!-- Files Upload Section -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Files</label>

                <!-- Hidden file input -->
                <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileUpload"
                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">

                <!-- Upload button -->
                <button type="button" @click="$refs.fileInput.click()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Upload Files
                </button>

                <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, PDF, DOC, DOCX (Max 10MB each)
                </p>

                <!-- Display file validation errors -->
                <div v-if="error && (error['files.0'] || error.files)" class="mt-2">
                    <p class="text-red-500 text-sm">
                        {{ error['files.0'] ? error['files.0'][0] : (error.files ? error.files[0] : '') }}
                    </p>
                </div>

                <!-- Show selected files -->
                <div v-if="form.files && form.files.length > 0" class="mt-3 space-y-2">
                    <p class="text-sm text-gray-600 mb-2">Selected files:</p>
                    <div v-for="(file, index) in form.files" :key="index"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                        <div class="flex items-center space-x-3 min-w-0">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-900 truncate">{{ file.name }}</p>
                                <p class="text-xs text-gray-500">{{ Math.round(file.size / 1024) }} KB</p>
                            </div>
                        </div>
                        <button type="button" @click="removeFile(index)"
                            class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <button type="button" @click="router.back()"
                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="submit" :disabled="isSubmitting || isLoading"
                    class="px-6 py-2 bg-green-700 text-white rounded-md hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <Loader v-if="isSubmitting" class="w-5 h-5 mr-2" />
                    <span v-if="isSubmitting">Creating Project...</span>
                    <span v-else>Create Project</span>
                </button>
            </div>

            <!-- Error Display -->
            <div v-if="error && Object.keys(error).length > 0" class="bg-red-50 border border-red-200 rounded-md p-4">
                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                <ul class="mt-2 text-sm text-red-700">
                    <li v-for="(errorMessages, field) in error" :key="field">
                        <strong>{{ field.replace('_', ' ').toLowerCase() }}:</strong>
                        {{ Array.isArray(errorMessages) ? errorMessages[0] : errorMessages }}
                    </li>
                </ul>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Loading spinner animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>