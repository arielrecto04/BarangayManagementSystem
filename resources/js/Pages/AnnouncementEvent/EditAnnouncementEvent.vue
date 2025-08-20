<script setup>
import { ref, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAnnouncementEventStore } from "@/Stores";
import { storeToRefs } from "pinia";
import useToast from "@/Utils/useToast";

// Stores
const announcementEventStore = useAnnouncementEventStore();
const { event, isLoading, error } = storeToRefs(announcementEventStore);

// Utils
const route = useRoute();
const router = useRouter();
const { showToast } = useToast();

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
const previewImage = ref(null);

// Helper to format datetime-local
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

// Watch start_date to adjust end_date if needed
watch(() => form.value.start_date, (newStartDate) => {
    if (newStartDate && form.value.end_date) {
        const startTime = new Date(newStartDate).getTime();
        const endTime = new Date(form.value.end_date).getTime();
        if (endTime <= startTime) {
            const newEndDate = new Date(startTime + 60 * 60 * 1000); // +1h
            form.value.end_date = formatDateTimeLocal(newEndDate);
        }
    }
});

// Fetch existing event
onMounted(async () => {
    const id = route.params.id;
    await announcementEventStore.fetchEvent(id);

    if (event.value) {
        form.value = {
            type: event.value.type || "announcement",
            title: event.value.title || "",
            description: event.value.description || "",
            start_date: event.value.start_date ? formatDateTimeLocal(event.value.start_date) : "",
            end_date: event.value.end_date ? formatDateTimeLocal(event.value.end_date) : "",
            location: event.value.location || "",
            author: event.value.author || "",
            image: null,
        };
        previewImage.value = event.value.image ? `/${event.value.image}` : null;
    }
});

// Handle file input
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.image = file;
        previewImage.value = URL.createObjectURL(file);
    }
};

// Remove image
const removeImage = () => {
    form.value.image = null;
    previewImage.value = null;
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) fileInput.value = '';
};

// Default times
const setDefaultStartTime = () => {
    const now = new Date();
    form.value.start_date = formatDateTimeLocal(now);
};

const setDefaultEndTime = () => {
    const now = new Date();
    const oneHourLater = new Date(now.getTime() + 60 * 60 * 1000);
    form.value.end_date = formatDateTimeLocal(oneHourLater);
};

// Submit update
const handleSubmit = async () => {
    try {
        const id = route.params.id;
        const formData = new FormData();

        Object.keys(form.value).forEach((key) => {
            if (form.value[key] !== null && form.value[key] !== "") {
                let value = form.value[key];
                if ((key === "start_date" || key === "end_date") && value) {
                    value = new Date(value).toISOString();
                }
                formData.append(key, value);
            }
        });

        await announcementEventStore.updateEvent(id, formData);
        showToast({ icon: "success", title: "Event updated successfully" });
        router.push("/announcement-events/list-announcements-events");
    } catch (err) {
        console.error(err);
        showToast({ icon: "error", title: "Failed to update event", text: err.response?.data?.message || err.message });
    }
};
</script>

<template>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Edit Announcement / Event</h1>

        <!-- Loader -->
        <div v-if="isLoading" class="flex justify-center items-center py-20">
            <div class="relative">
                <div class="w-16 h-16 border-4 border-indigo-200 rounded-full animate-spin"></div>
                <div
                    class="w-16 h-16 border-4 border-indigo-600 rounded-full animate-spin absolute top-0 left-0 border-t-transparent border-r-transparent">
                </div>
            </div>
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-5 bg-white shadow rounded-2xl p-6">
            <!-- Type -->
            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select v-model="form.type" class="w-full border rounded-lg px-3 py-2">
                    <option value="announcement">Announcement</option>
                    <option value="event">Event</option>
                </select>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium mb-1">Title</label>
                <input v-model="form.title" type="text" class="w-full border rounded-lg px-3 py-2" required />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea v-model="form.description" rows="4" class="w-full border rounded-lg px-3 py-2"></textarea>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Start Date & Time</label>
                    <div class="flex gap-2">
                        <button type="button" @click="setDefaultStartTime"
                            class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg border">
                            Now
                        </button>
                        <input v-model="form.start_date" type="datetime-local"
                            class="flex-1 border rounded-lg px-3 py-2" :step="60" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">End Date & Time</label>
                    <div class="flex gap-2">
                        <input v-model="form.end_date" type="datetime-local" class="flex-1 border rounded-lg px-3 py-2"
                            :step="60" :min="form.start_date" />
                        <button type="button" @click="setDefaultEndTime"
                            class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg border">
                            +1h
                        </button>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-medium mb-1">Location</label>
                <input v-model="form.location" type="text" class="w-full border rounded-lg px-3 py-2" />
            </div>

            <!-- Author -->
            <div>
                <label class="block text-sm font-medium mb-1">Author</label>
                <input v-model="form.author" type="text" class="w-full border rounded-lg px-3 py-2" />
            </div>

            <!-- Image -->
            <div class="flex flex-col">
                <label class="font-semibold text-sm mb-1">Image</label>
                <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileChange" />
                <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-fit"
                    @click="$refs.fileInput.click()">
                    Select Image
                </button>
                <div v-if="previewImage" class="mt-3 flex flex-col gap-2">
                    <div class="relative w-fit">
                        <img :src="previewImage" alt="Preview" class="h-40 object-cover rounded-lg shadow-md" />
                        <button type="button"
                            class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-sm transition-all duration-300 hover:scale-110"
                            @click="removeImage">
                            ✖
                        </button>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <router-link to="/announcement-events/list-announcements-events"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100">
                    Cancel
                </router-link>
                <button type="submit" :disabled="isLoading"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50">
                    <span v-if="isLoading">Saving...</span>
                    <span v-else>Save Changes</span>
                </button>
            </div>

            <div v-if="error" class="mt-4 text-sm text-red-600">
                {{ error.message || 'An error occurred' }}
            </div>
        </form>
    </div>
</template>
