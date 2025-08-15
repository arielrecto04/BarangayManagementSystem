<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAnnouncementEventStore } from "@/Stores";
import useToast from "@/Utils/useToast";

const router = useRouter();
const { showToast } = useToast();
const announcementEventStore = useAnnouncementEventStore();

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

// For image preview
const imagePreview = ref(null);

// Handle file selection
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const validTypes = ["image/jpeg", "image/png", "image/gif", "image/jpg"];
    if (!validTypes.includes(file.type)) {
        showToast({ icon: 'error', title: "Only image files (jpg, jpeg, png, gif) are allowed." });
        event.target.value = null;
        return;
    }
    form.value.image = file;
    // Preview
    const reader = new FileReader();
    reader.onload = e => {
        imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// Submit form
const submitForm = async () => {
    try {
        const formData = new FormData();
        formData.append("type", form.value.type);
        formData.append("title", form.value.title);
        formData.append("description", form.value.description || "");
        formData.append("start_date", form.value.start_date || "");
        formData.append("end_date", form.value.end_date || "");
        formData.append("location", form.value.location || "");
        formData.append("author", form.value.author || "");
        if (form.value.image) formData.append("image", form.value.image);

        await announcementEventStore.createAnnouncementEvent(formData);
        showToast({ icon: 'success', title: "Announcement/Event created successfully!" });
        router.push({
            path: "/announcement-events/list-announcements-events",
            query: { newItem: "true", type: form.value.type }
        });
    } catch (error) {
        console.error(error);
        showToast({ icon: 'error', title: "Failed to create announcement/event." });
    }
};

// Remove selected image
const removeImage = () => {
    form.value.image = null;
    imagePreview.value = null;
};
</script>

<template>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Add Announcement/Event</h1>
        <div class="bg-white shadow-lg rounded-xl p-6 space-y-6">
            <!-- Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select v-model="form.type"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black p-3">
                    <option value="announcement">Announcement</option>
                    <option value="event">Event</option>
                </select>
            </div>
            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" v-model="form.title" placeholder="Enter title"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black p-3" />
            </div>
            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="form.description" placeholder="Enter description" rows="4"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black p-3 resize-none"></textarea>
            </div>
            <!-- Dates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" v-model="form.start_date"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black p-3" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" v-model="form.end_date"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black p-3" />
                </div>
            </div>
            <!-- Location -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" v-model="form.location" placeholder="Enter location"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black p-3" />
            </div>
            <!-- Author -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                <input type="text" v-model="form.author" placeholder="Enter author name"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black p-3" />
            </div>
            <!-- Image Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                <div v-if="!form.image" class="border border-dashed border-gray-300 rounded-lg p-4 text-center">
                    <label
                        class="bg-black text-white px-4 py-2 rounded-lg cursor-pointer hover:bg-gray-800 transition-colors">
                        Upload Image
                        <input type="file" class="hidden" @change="handleFileChange" accept="image/*" />
                    </label>
                    <p class="mt-2 text-sm text-gray-500">JPEG, JPG, PNG, GIF up to 10MB</p>
                </div>
                <div v-else class="flex items-center space-x-4 p-2 border border-gray-200 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-medium">{{ form.image.name }}</p>
                        <p class="text-sm text-gray-500">{{ (form.image.size / 1024 / 1024).toFixed(2) }} MB</p>
                    </div>
                    <button type="button" @click="removeImage"
                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                        Remove
                    </button>
                </div>
                <!-- Image preview -->
                <div v-if="imagePreview" class="mt-3">
                    <img :src="imagePreview" alt="Preview" class="w-40 h-40 object-cover rounded-lg border" />
                </div>
            </div>
            <!-- Submit & Cancel Buttons -->
            <div class="flex justify-end space-x-3">
                <!-- Cancel Button -->
                <button type="button" @click="router.push({ name: 'List Announcement Events' })"
                    class="bg-gray-300 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </button>

                <!-- Save Button -->
                <button type="button" @click="submitForm"
                    class="bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors">
                    Save
                </button>
            </div>

        </div>
    </div>
</template>
