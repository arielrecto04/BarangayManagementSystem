<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/Stores';
import { Loader } from '@/Components';
import { storeToRefs } from 'pinia';

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

        // Create FormData for file uploads
        const formData = new FormData();

        // Add all form fields to FormData
        Object.keys(form.value).forEach(key => {
            if (key === 'files' && form.value[key] && form.value[key].length > 0) {
                // Handle multiple files
                for (let i = 0; i < form.value[key].length; i++) {
                    formData.append('files[]', form.value[key][i]);
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

        await projectStore.addProject(formData);

        if (!error.value) {
            router.push({ name: 'List Projects' });
        }
    } catch (err) {
        console.error('Error creating project:', err);
    } finally {
        isSubmitting.value = false;
    }
};

const handleFileUpload = (event) => {
    form.value.files = event.target.files;
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
        projectStore.clearErrors();
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

        <form @submit.prevent="handleSubmit" class="space-y-6" enctype="multipart/form-data">
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

            <!-- Files -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Files</label>
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