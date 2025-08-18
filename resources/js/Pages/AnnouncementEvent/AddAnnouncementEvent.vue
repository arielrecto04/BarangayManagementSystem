<!-- AddAnnouncementEvent.vue -->
<script setup>
import { ref } from "vue";
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

// Image preview
const previewImage = ref(null);
const handleFileChange = (e) => {
    const file = e.target.files[0];
    form.value.image = file;
    previewImage.value = file ? URL.createObjectURL(file) : null;
};

// Submit handler
const handleSubmit = async () => {
    try {
        const formData = new FormData();
        Object.keys(form.value).forEach((key) => {
            if (form.value[key] !== null && form.value[key] !== "")
                formData.append(key, form.value[key]);
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
</script>

<template>
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow p-6 mt-6">
        <h2 class="text-xl font-bold mb-6">Add Announcement / Event</h2>

        <form @submit.prevent="handleSubmit" class="space-y-5">
            <!-- Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select v-model="form.type"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="announcement">Announcement</option>
                    <option value="event">Event</option>
                </select>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" v-model="form.title" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="4"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" v-model="form.start_date"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" v-model="form.end_date"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" v-model="form.location"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>

            <!-- Author -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Author</label>
                <input type="text" v-model="form.author"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>

            <!-- Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Image</label>
                <input type="file" accept="image/*" @change="handleFileChange"
                    class="mt-1 block w-full text-sm text-gray-500" />
                <div v-if="previewImage" class="mt-3">
                    <img :src="previewImage" alt="Preview" class="h-40 object-cover rounded-lg shadow" />
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <router-link to="/announcement-events/list-announcements-events"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancel
                </router-link>
                <button type="submit" :disabled="isLoading"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 disabled:opacity-50">
                    <span v-if="isLoading">Saving...</span>
                    <span v-else>Save</span>
                </button>
            </div>
        </form>

        <div v-if="error" class="mt-4 text-sm text-red-600">
            {{ error.message || 'An error occurred' }}
        </div>
    </div>
</template>
