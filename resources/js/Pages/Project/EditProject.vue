<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProjectStore } from '@/Stores';
import { Loader } from '@/Components';
import { storeToRefs } from 'pinia';

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

// Helper function to format date for input - MOVED BEFORE WATCH
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

        // Create FormData for file uploads
        const formData = new FormData();

        // Add all form fields
        Object.keys(form.value).forEach(key => {
            if (key === 'files' && form.value[key] && form.value[key].length > 0) {
                // Handle new files
                for (let i = 0; i < form.value[key].length; i++) {
                    formData.append('files[]', form.value[key][i]);
                }
            } else if (key === 'existing_files') {
                // Handle existing files
                if (Array.isArray(form.value[key])) {
                    form.value[key].forEach(file => {
                        formData.append('existing_files[]', file);
                    });
                }
            } else if (key === 'assigned_organizations') {
                // Handle assigned organizations array
                if (Array.isArray(form.value[key])) {
                    form.value[key].forEach(org => {
                        formData.append('assigned_organizations[]', org);
                    });
                }
            } else if (form.value[key] !== null && form.value[key] !== undefined && form.value[key] !== '') {
                formData.append(key, form.value[key]);
            }
        });

        // Add project ID for update
        formData.append('id', project.value.id);

        await projectStore.updateProject(formData);

        if (!error.value) {
            router.push({ name: 'List Projects' });
        }
    } catch (err) {
        console.error('Error updating project:', err);
    } finally {
        isSubmitting.value = false;
    }
};

const handleFileUpload = (event) => {
    form.value.files = event.target.files;
};

const removeExistingFile = (index) => {
    form.value.existing_files.splice(index, 1);
};

onMounted(async () => {
    if (route.params.id) {
        await projectStore.getProjectById(route.params.id);
    }
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Edit Project</h2>
            <button @click="router.back()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <Loader v-if="isLoading && !project" class="my-8" />

        <form v-else @submit.prevent="handleSubmit" class="space-y-6" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Title *</label>
                    <input v-model="form.title" type="text" required
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.title" class="text-red-500 text-sm mt-1">{{ error.title[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input v-model="form.category" type="text"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select v-model="form.status"
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
                    <input v-model="form.funding_source" type="text"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="form.description" rows="4"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Enter project description"></textarea>
                <p v-if="error?.description" class="text-red-500 text-sm mt-1">{{ error.description[0] }}</p>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input v-model="form.start_date" type="date"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.start_date" class="text-red-500 text-sm mt-1">{{ error.start_date[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Completion</label>
                    <input v-model="form.target_completion" type="date"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.target_completion" class="text-red-500 text-sm mt-1">{{ error.target_completion[0]
                    }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actual Completion</label>
                    <input v-model="form.actual_completion" type="date"
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
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="error?.completion_percentage" class="text-red-500 text-sm mt-1">{{
                        error.completion_percentage[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barangay/Zone</label>
                    <input v-model="form.barangay_zone" type="text"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Team Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Lead</label>
                    <input v-model="form.project_lead" type="text"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Members</label>
                    <input v-model="form.number_of_members" type="number" min="0"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Address</label>
                <input v-model="form.site_address" type="text"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Additional Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Milestones Achieved</label>
                    <textarea v-model="form.milestone_achieved" rows="3"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Describe milestones achieved"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Challenges Encountered</label>
                    <textarea v-model="form.challenges_encountered" rows="3"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Describe challenges faced"></textarea>
                </div>
            </div>

            <!-- Disbursement Schedule -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Disbursement Schedule</label>
                <textarea v-model="form.disbursement_schedule" rows="3"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Describe disbursement schedule"></textarea>
            </div>

            <!-- Existing Files -->
            <div v-if="form.existing_files && form.existing_files.length > 0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Files</label>
                <div class="space-y-2">
                    <div v-for="(file, index) in form.existing_files" :key="index"
                        class="flex items-center justify-between p-2 border border-gray-200 rounded-md">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <a :href="file" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                {{ file.split('/').pop() }}
                            </a>
                        </div>
                        <button type="button" @click="removeExistingFile(index)"
                            class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <input type="file" multiple @change="handleFileUpload" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, PDF, DOC, DOCX</p>
                <p v-if="error?.files" class="text-red-500 text-sm mt-1">{{ error.files[0] }}</p>
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
                    <span v-if="isSubmitting">Updating...</span>
                    <span v-else>Update Project</span>
                </button>
            </div>

            <!-- Error Display -->
            <div v-if="error && typeof error === 'object' && Object.keys(error).length > 0"
                class="bg-red-50 border border-red-200 rounded-md p-4">
                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                <ul class="mt-2 text-sm text-red-700">
                    <li v-for="(messages, field) in error" :key="field" v-if="Array.isArray(messages)">
                        <strong>{{ field.replace('_', ' ').toLowerCase() }}:</strong> {{ messages[0] }}
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