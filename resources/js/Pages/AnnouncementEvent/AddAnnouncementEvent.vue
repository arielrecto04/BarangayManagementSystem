<script setup>
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import { useAnnouncementEventStore } from "@/Stores";
import { storeToRefs } from "pinia";
import useToast from "@/Utils/useToast";

const router = useRouter();
const { showToast } = useToast();
const announcementEventStore = useAnnouncementEventStore();
const { isLoading, error } = storeToRefs(announcementEventStore);

// Form state
const form = ref({
    type: "announcement",
    title: "",
    description: "",
    start_date: "",
    end_date: "",
    location: "",
    author: "",
    image: null,
});

// Form errors
const formErrors = ref({});

// Image preview
const previewImage = ref(null);

// Helper function to format datetime for datetime-local input
const formatDateTimeLocal = (date) => {
    if (!date) return '';

    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

// Watch for start_date changes to ensure end_date is after start_date
watch(() => form.value.start_date, (newStartDate) => {
    if (newStartDate && form.value.end_date) {
        const startTime = new Date(newStartDate).getTime();
        const endTime = new Date(form.value.end_date).getTime();

        // If end date is before start date, adjust end date
        if (endTime <= startTime) {
            const newEndDate = new Date(startTime + 60 * 60 * 1000); // Add 1 hour
            form.value.end_date = formatDateTimeLocal(newEndDate);
        }
    }
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.image = file;
        previewImage.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    form.value.image = null;
    previewImage.value = null;
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) fileInput.value = '';
};

// Validation function
const validateForm = () => {
    formErrors.value = {};
    let isValid = true;

    const fields = {
        title: 'Title',
        start_date: 'Start Date',
        end_date: 'End Date',
        author: 'Author',
        description: 'Description',
        location: 'Location',
    };

    for (const [field, label] of Object.entries(fields)) {
        if (!form.value[field]) {
            formErrors.value[field] = `${label} is required.`;
            isValid = false;
        }
    }

    // Check if end date is after start date
    if (form.value.start_date && form.value.end_date) {
        const startTime = new Date(form.value.start_date).getTime();
        const endTime = new Date(form.value.end_date).getTime();

        if (endTime <= startTime) {
            formErrors.value.end_date = 'End date must be after start date.';
            isValid = false;
        }
    }

    return isValid;
};

// Submit handler
const handleSubmit = async () => {
    // Validate form before submission
    if (!validateForm()) {
        showToast({ icon: "error", title: "Please fill in all required fields correctly." });
        return;
    }

    try {
        const formData = new FormData();

        // Add all form fields to FormData
        Object.keys(form.value).forEach((key) => {
            if (form.value[key] !== null && form.value[key] !== "") {
                let value = form.value[key];

                // Handle datetime fields - ensure they're in the correct format
                if ((key === 'start_date' || key === 'end_date') && value) {
                    // Convert datetime-local input to ISO string
                    const date = new Date(value);
                    value = date.toISOString();
                }

                formData.append(key, value);
            }
        });

        await announcementEventStore.addEvent(formData);

        showToast({ icon: "success", title: "Event created successfully" });
        router.push("/announcement-events/list-announcements-events");
    } catch (err) {
        console.error(err);
        showToast({
            icon: "error",
            title: "Failed to create event",
            text: err.response?.data?.message || err.message,
        });
    }
};

// Set default times if needed
const setDefaultStartTime = () => {
    const now = new Date();
    form.value.start_date = formatDateTimeLocal(now);
};

const setDefaultEndTime = () => {
    const now = new Date();
    const oneHourLater = new Date(now.getTime() + 60 * 60 * 1000);
    form.value.end_date = formatDateTimeLocal(oneHourLater);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 p-3 overflow-auto">
        <div class="max-w-md mx-auto w-full">
            <!-- Header -->
            <div class="mb-5 pt-3">
                <h1 class="text-xl font-bold text-gray-800">Add Announcement / Event</h1>
                <p class="text-xs text-gray-500 mt-1">Fill in the details below</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm p-4 space-y-4">
                <!-- Type -->
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-700">Type</label>
                    <select v-model="form.type" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
                        <option value="announcement">Announcement</option>
                        <option value="event">Event</option>
                    </select>
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-700">Title</label>
                    <input v-model="form.title" type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
                    <p v-if="formErrors.title" class="text-red-500 text-xs mt-1">{{ formErrors.title }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-700">Description</label>
                    <textarea v-model="form.description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm"></textarea>
                    <p v-if="formErrors.description" class="text-red-500 text-xs mt-1">{{ formErrors.description }}</p>
                </div>

                <!-- Dates -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-700">Start Date & Time</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="setDefaultStartTime"
                                class="px-2.5 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg border whitespace-nowrap">
                                Now
                            </button>
                            <input v-model="form.start_date" type="datetime-local"
                                class="flex-1 min-w-[150px] border border-gray-300 rounded-lg px-3 py-2 text-xs"
                                :step="60" />
                        </div>
                        <p v-if="formErrors.start_date" class="text-red-500 text-xs mt-1">{{ formErrors.start_date }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-700">End Date & Time</label>
                        <div class="flex flex-wrap gap-2">
                            <input v-model="form.end_date" type="datetime-local"
                                class="flex-1 min-w-[150px] border border-gray-300 rounded-lg px-3 py-2 text-xs"
                                :step="60" :min="form.start_date" />
                            <button type="button" @click="setDefaultEndTime"
                                class="px-2.5 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg border whitespace-nowrap">
                                +1h
                            </button>
                        </div>
                        <p v-if="formErrors.end_date" class="text-red-500 text-xs mt-1">{{ formErrors.end_date }}</p>
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-700">Location</label>
                    <input v-model="form.location" type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
                    <p v-if="formErrors.location" class="text-red-500 text-xs mt-1">{{ formErrors.location }}</p>
                </div>

                <!-- Author -->
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-700">Author</label>
                    <input v-model="form.author" type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
                    <p v-if="formErrors.author" class="text-red-500 text-xs mt-1">{{ formErrors.author }}</p>
                </div>

                <!-- Image -->
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-700">Image</label>
                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileChange" />
                    <div class="flex flex-col gap-2">
                        <button type="button"
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-xs w-full"
                            @click="$refs.fileInput.click()">
                            Select Image
                        </button>

                        <div v-if="previewImage" class="mt-2 flex flex-col items-center w-full">
                            <div class="relative w-full">
                                <img :src="previewImage" alt="Preview"
                                    class="h-32 w-full object-cover rounded-lg shadow border max-w-full" />
                                <button type="button"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow-md"
                                    @click="removeImage">
                                    ×
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 text-center">Image preview</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-2 pt-3 sm:flex-row sm:gap-2">
                    <button type="submit" :disabled="isLoading"
                        class="flex-1 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 text-sm font-medium">
                        <span v-if="isLoading">Saving...</span>
                        <span v-else>Save</span>
                    </button>
                    <router-link to="/announcement-events/list-announcements-events"
                        class="flex-1 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium text-center">
                        Cancel
                    </router-link>
                </div>
            </form>

            <div v-if="error" class="mt-4 text-xs text-red-600 p-2 bg-red-50 rounded-lg">
                {{ error.message || 'An error occurred' }}
            </div>
        </div>
    </div>
</template>

<style>
html,
body,
#app {
    height: 100%;
}

.min-h-screen {
    min-height: 100vh;
}

input[type="datetime-local"] {
    max-height: 40px;
    min-width: 0;
    /* allow shrinking on mobile */
}

button,
input,
select,
textarea {
    font-size: 16px;
    /* improve touch targets on mobile */
}

@media (max-width: 375px) {
    input[type="datetime-local"] {
        font-size: 14px;
    }
}
</style>