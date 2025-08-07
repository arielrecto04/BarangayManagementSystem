<script setup>
import { AuthLayout } from "@/Layouts";
import { useComplaintStore } from "@/Stores";
import { storeToRefs } from "pinia";
import { ref, onMounted, computed, getCurrentInstance, onUpdated } from "vue";
import { useRouter } from "vue-router";
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'
const router = useRouter();
const complaintStore = useComplaintStore();
const { complaints } = storeToRefs(complaintStore);
const totalCases = computed(() => complaints.value.length);


// Navigate to list view with filter
const filterByStatus = async (status) => {
    await complaintStore.getComplaints(); // Refresh complaints
    router.push({ path: "/complaints/list", query: { status } });
};

onMounted(() => {
    complaintStore.getComplaints();
});

// 📦 Handle status update event
function handleStatusUpdated() {
    complaintStore.getComplaints(); // Refresh dashboard stats
}

// Complaint statistics
const inProgress = computed(() =>
    complaints.value.filter((c) => c.status === "In Progress").length
);
const openCases = computed(() =>
    complaints.value.filter((c) => c.status === "Open").length
);
const resolved = computed(() =>
    complaints.value.filter((c) => c.status === "Resolved").length
);

</script>

<template>
    <AuthLayout>
        <div class="m-5">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-semibold">Complaint Management</h1>
                <router-link to="/complaints/add-complaint"
                    class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 flex items-center gap-2">
                    <PlusIcon class="w-5 h-5" />
                    <span>New</span>
                </router-link>
            </div>

            <!-- Cards -->
            <div class="bg-gray-100 p-5 rounded-lg grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Total Cases -->
                <div class="bg-white rounded-xl p-5 shadow flex flex-col gap-2">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Total Cases
                        <FolderIcon class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ totalCases }}</div>
                </div>

                <!-- In Progress -->
                <div @click="filterByStatus('In Progress')"
                    class="cursor-pointer bg-white rounded-xl p-5 shadow hover:bg-gray-50 transition">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        In Progress
                        <ArrowPathIcon class="w-5 h-5 text-gray-500 animate-spin" />
                    </div>
                    <div class="text-3xl font-bold">{{ inProgress }}</div>
                </div>

                <!-- Open Cases -->
                <div @click="filterByStatus('Open')"
                    class="cursor-pointer bg-white rounded-xl p-5 shadow hover:bg-gray-50 transition">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Open Cases
                        <ChartPieIcon class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ openCases }}</div>
                </div>

                <!-- Resolved -->
                <div @click="filterByStatus('Resolved')"
                    class="cursor-pointer bg-white rounded-xl p-5 shadow hover:bg-gray-50 transition">
                    <div class="text-sm text-gray-600 font-semibold flex justify-between items-center">
                        Resolved
                        <CheckCircleIcon class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="text-3xl font-bold">{{ resolved }}</div>
                </div>
            </div>

            <!-- Nested List Complaint View -->
            <router-view />
        </div>
    </AuthLayout>
</template>
