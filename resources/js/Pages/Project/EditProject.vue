<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProjectStore } from '@/Stores';
import { Loader } from '@/Components';
import { storeToRefs } from 'pinia';
import useToast from '@/Utils/useToast';

const { showToast } = useToast();
const route = useRoute();
const router = useRouter();
const projectStore = useProjectStore();
const { project, isLoading, error } = storeToRefs(projectStore);

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
    existing_files: [],
    files: []
});

const isSubmitting = ref(false);

// Helper function to format date for input
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
};

// Watch for project changes and update form
watch(project, (newProject) => {
    if (newProject) {
        form.value = {
            ...form.value,
            title: newProject.title || '',
            description: newProject.description || '',
            category: newProject.category || '',
            status: newProject.status || 'planning',
            start_date: newProject.start_date ? formatDateForInput(newProject.start_date) : '',
            target_completion: newProject.target_completion ? formatDateForInput(newProject.target_completion) : '',
            actual_completion: newProject.actual_completion ? formatDateForInput(newProject.actual_completion) : '',
            funding_source: newProject.funding_source || '',
            barangay_zone: newProject.barangay_zone || '',
            completion_percentage: newProject.completion_percentage || 0,
            milestone_achieved: newProject.milestone_achieved || '',
            project_lead: newProject.project_lead || '',
            assigned_organizations: newProject.assigned_organizations || [],
            number_of_members: newProject.number_of_members || 0,
            site_address: newProject.site_address || '',
            disbursement_schedule: newProject.disbursement_schedule || '',
            challenges_encountered: newProject.challenges_encountered || '',
            existing_files: newProject.files || [],
        };
    }
}, { immediate: true });

const handleSubmit = async () => {
    try {
        isSubmitting.value = true;

        if (!form.value.title.trim()) {
            showToast({ icon: 'error', title: 'Project title is required.' });
            return;
        }

        const formData = new FormData();

        // Add all simple fields
        const simpleFields = ['title', 'description', 'category', 'status',
            'start_date', 'target_completion', 'actual_completion',
            'funding_source', 'barangay_zone', 'completion_percentage',
            'milestone_achieved', 'project_lead', 'number_of_members',
            'site_address', 'disbursement_schedule', 'challenges_encountered'];

        simpleFields.forEach(field => {
            if (form.value[field] !== null && form.value[field] !== undefined) {
                formData.append(field, form.value[field]);
            }
        });

        // Handle arrays correctly for Laravel
        if (form.value.assigned_organizations && form.value.assigned_organizations.length > 0) {
            form.value.assigned_organizations.forEach((org, index) => {
                formData.append(`assigned_organizations[${index}]`, org);
            });
        }

        // Handle existing files - send as existing_files array
        if (form.value.existing_files && form.value.existing_files.length > 0) {
            form.value.existing_files.forEach((fileUrl, index) => {
                formData.append(`existing_files[${index}]`, fileUrl);
            });
        }

        // Handle new files - only append actual File objects
        if (form.value.files && form.value.files.length > 0) {
            form.value.files.forEach((file, index) => {
                if (file instanceof File) {
                    formData.append(`files[${index}]`, file);
                }
            });
        }

        formData.append('_method', 'PUT');

        await projectStore.updateProject(project.value.id, formData);

        showToast({ icon: 'success', title: 'Project updated successfully!' });
        router.push({ name: 'List Projects' });

    } catch (err) {
        // Enhanced error handling
        console.error('Error updating project:', err);

        if (err.response?.data?.errors) {
            const errors = err.response.data.errors;
            console.log('Validation errors:', errors);

            // Display all validation errors to user
            for (const [field, messages] of Object.entries(errors)) {
                if (Array.isArray(messages) && messages.length > 0) {
                    showToast({
                        icon: 'error',
                        title: `${field.replace('_', ' ')}: ${messages[0]}`
                    });
                }
            }
        } else {
            showToast({
                icon: 'error',
                title: 'An unexpected error occurred while updating the project.'
            });
        }
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

    // Add valid files to existing files array
    form.value.files = [...(form.value.files || []), ...validFiles];

    console.log('New files selected:', validFiles.map(f => ({
        name: f.name,
        size: f.size,
        type: f.type
    })));

    // Clear the input
    event.target.value = '';
};

const removeExistingFile = (index) => {
    form.value.existing_files.splice(index, 1);
    showToast({ icon: 'info', title: 'File will be removed when you save the project.' });
};

const removeNewFile = (index) => {
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

onMounted(async () => {
    if (route.params.id) {
        await projectStore.getProjectById(route.params.id);
    }
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6">
        <div class="flex justify-between items-center mb-4 md:mb-6">
            <h2 class="text-lg md:text-xl font-semibold">Edit Project</h2>
            <button @click="router.back()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <Loader v-if="isLoading && !project" class="my-6 md:my-8" />

        <form v-else @submit.prevent="handleSubmit" class="space-y-4 md:space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Title *</label>
                    <input v-model="form.title" type="text" required @input="clearFieldError('title')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.title" class="text-red-500 text-sm mt-1">{{ error.title[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input v-model="form.category" type="text" @input="clearFieldError('category')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
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
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="form.description" rows="4" @input="clearFieldError('description')"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Enter project description"></textarea>
                <p v-if="error?.description" class="text-red-500 text-sm mt-1">{{ error.description[0] }}</p>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
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
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Team Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Lead</label>
                    <input v-model="form.project_lead" type="text" @input="clearFieldError('project_lead')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
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
                <div class="flex flex-col sm:flex-row gap-2 mb-2">
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
                        <span class="text-sm truncate">{{ org }}</span>
                        <button type="button" @click="removeOrganization(index)"
                            class="text-red-600 hover:text-red-800 ml-2 flex-shrink-0">
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
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Additional Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Milestones Achieved</label>
                    <textarea v-model="form.milestone_achieved" rows="3" @input="clearFieldError('milestone_achieved')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Describe milestones achieved"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Challenges Encountered</label>
                    <textarea v-model="form.challenges_encountered" rows="3"
                        @input="clearFieldError('challenges_encountered')"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Describe challenges faced"></textarea>
                </div>
            </div>

            <!-- Disbursement Schedule -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Disbursement Schedule</label>
                <textarea v-model="form.disbursement_schedule" rows="3"
                    @input="clearFieldError('disbursement_schedule')"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Describe disbursement schedule"></textarea>
            </div>

            <!-- Existing Files -->
            <div v-if="form.existing_files && form.existing_files.length > 0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Files</label>
                <div class="space-y-2">
                    <div v-for="(file, index) in form.existing_files" :key="index"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                        <div class="flex items-center space-x-3 min-w-0 flex-1">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="min-w-0 flex-1">
                                <a :href="file" target="_blank"
                                    class="text-blue-600 hover:text-blue-800 text-sm truncate block">
                                    {{ file.split('/').pop() }}
                                </a>
                                <p class="text-xs text-gray-500 truncate">Current file</p>
                            </div>
                        </div>
                        <button type="button" @click="removeExistingFile(index)"
                            class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full transition-colors flex-shrink-0 ml-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- New Files -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Add New Files</label>

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
                    Add Files
                </button>

                <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, PDF, DOC, DOCX (Max 10MB each)
                </p>
                <p v-if="error?.files" class="text-red-500 text-sm mt-1">{{ error.files[0] }}</p>

                <!-- Show newly selected files -->
                <div v-if="form.files && form.files.length > 0" class="mt-3 space-y-2">
                    <p class="text-sm text-gray-600 mb-2">New files to upload:</p>
                    <div v-for="(file, index) in form.files" :key="index"
                        class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center space-x-3 min-w-0 flex-1">
                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-900 truncate">{{ file.name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Math.round(file.size / 1024) }} KB - New
                                    file</p>
                            </div>
                        </div>
                        <button type="button" @click="removeNewFile(index)"
                            class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full transition-colors flex-shrink-0 ml-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 md:pt-6 border-t border-gray-200">
                <button type="button" @click="router.back()"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 text-center">
                    Cancel
                </button>
                <button type="submit" :disabled="isSubmitting || isLoading"
                    class="px-4 py-2 bg-green-700 text-white rounded-md hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                    <Loader v-if="isSubmitting" class="w-5 h-5 mr-2" />
                    <span v-if="isSubmitting">Updating...</span>
                    <span v-else>Update Project</span>
                </button>
            </div>

            <!-- Error Display -->
            <div v-if="error && typeof error === 'object' && Object.keys(error).length > 0"
                class="bg-red-50 border border-red-200 rounded-md p-4">
                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                <ul class="mt-2 text-sm text-red-700">
                    <li v-for="(messages, field) in error" :key="field">
                        <template v-if="Array.isArray(messages)">
                            <strong>{{ field.replace('_', ' ').toLowerCase() }}:</strong> {{ messages[0] }}
                        </template>
                        <template v-else>
                            <strong>{{ field.replace('_', ' ').toLowerCase() }}:</strong> {{ messages }}
                        </template>
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