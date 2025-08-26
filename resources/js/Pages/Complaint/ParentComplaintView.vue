<script setup>
import { AuthLayout } from "@/Layouts";
import { useComplaintStore } from "@/Stores";
import { storeToRefs } from "pinia";
import { ref, onMounted, computed, getCurrentInstance, onUpdated } from "vue";
import { useRouter } from "vue-router";
import {
    FolderIcon,
    CheckCircleIcon,
    ChartPieIcon,
    ArrowPathIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";

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
        <div class="p-2 sm:p-5 lg:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-6 gap-2 sm:gap-0">
                <h1 class="text-base sm:text-xl lg:text-2xl font-semibold text-gray-900">Complaint Management</h1>
                <router-link to="/complaints/add-complaint"
                    class="bg-black text-white px-3 py-1.5 sm:py-2 rounded-md hover:bg-gray-800 transition-colors flex items-center justify-center gap-1.5 text-xs sm:text-sm font-medium">
                    <PlusIcon class="w-3 h-3 sm:w-4 sm:h-4" />
                    <span>New</span>
                </router-link>
            </div>

            <!-- Cards Grid - Mobile-First 2x2 Layout -->
            <div class="grid grid-cols-2 gap-2 sm:gap-4 lg:gap-6 mb-4 sm:mb-8">
                <!-- Total Cases -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Total</div>
                        <FolderIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-blue-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ totalCases }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">All complaints</div>
                </div>

                <!-- Open Cases -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Open</div>
                        <ChartPieIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-orange-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ openCases }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Pending review</div>
                </div>

                <!-- In Progress -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Progress</div>
                        <ArrowPathIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-yellow-500 animate-spin" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ inProgress }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Processing</div>
                </div>

                <!-- Resolved -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Resolved</div>
                        <CheckCircleIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-green-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ resolved }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Completed</div>
                </div>
            </div>

            <!-- Nested List Complaint View -->
            <router-view />
        </div>
    </AuthLayout>
</template>
