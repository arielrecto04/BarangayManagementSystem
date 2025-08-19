<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAnnouncementEventStore } from "@/Stores";
import { storeToRefs } from "pinia";
import useToast from "@/Utils/useToast";

// Stores
const announcementEventStore = useAnnouncementEventStore();
const { event, isLoading } = storeToRefs(announcementEventStore);

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

// Fetch existing event
onMounted(async () => {
    const id = route.params.id;
    await announcementEventStore.fetchEvent(id);

    if (event.value) {
        form.value = {
            type: event.value.type || "announcement",
            title: event.value.title || "",
            description: event.value.description || "",
            start_date: event.value.start_date || "",
            end_date: event.value.end_date || "",
            location: event.value.location || "",
            author: event.value.author || "",
            image: null, // reset for new upload
        };
        previewImage.value = event.value.image ? `/${event.value.image}` : null;
    }
});

// Handle file input
const handleFileChange = (e) => {
    const file = e.target.files[0];
    form.value.image = file;
    if (file) {
        previewImage.value = URL.createObjectURL(file);
    }
};

// Submit update
const handleSubmit = async () => {
    try {
        const id = route.params.id;
        const formData = new FormData();
        Object.keys(form.value).forEach((key) => {
            if (form.value[key] !== null) {
                formData.append(key, form.value[key]);
            }
        });

        await announcementEventStore.updateEvent(id, formData);
        showToast({ icon: "success", title: "Event updated successfully" });
        router.push("/announcement-events/list");
    } catch (error) {
        showToast({ icon: "error", title: error.message || "Failed to update event" });
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
                    <label class="block text-sm font-medium mb-1">Start Date</label>
                    <input v-model="form.start_date" type="date" class="w-full border rounded-lg px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">End Date</label>
                    <input v-model="form.end_date" type="date" class="w-full border rounded-lg px-3 py-2" />
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
            <div>
                <label class="block text-sm font-medium mb-1">Image</label>
                <input type="file" accept="image/*" @change="handleFileChange" class="w-full" />
                <div v-if="previewImage" class="mt-3">
                    <p class="text-sm text-gray-500">Preview:</p>
                    <img :src="previewImage" alt="Preview" class="h-40 object-cover rounded-lg mt-1" />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <router-link to="/announcement-events/list-announcements-events"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100">
                    Cancel
                </router-link>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</template>
