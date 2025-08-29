<script setup>
import { AuthLayout } from "@/Layouts";
import { useProjectStore } from "@/Stores";
import { storeToRefs } from "pinia";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import {
    FolderIcon,
    CheckCircleIcon,
    ChartPieIcon,
    ArrowPathIcon,
    PlusIcon,
    PauseCircleIcon,
    HandThumbUpIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const projectStore = useProjectStore();
const { projects } = storeToRefs(projectStore);

const totalProjects = computed(() => projects.value.length);
const planning = computed(() => projects.value.filter((p) => p.status === "planning").length);
const inProgress = computed(() => projects.value.filter((p) => p.status === "in_progress").length);
const completed = computed(() => projects.value.filter((p) => p.status === "completed").length);
const onHold = computed(() => projects.value.filter((p) => p.status === "on_hold").length);
const approved = computed(() => projects.value.filter((p) => p.status === "approved").length);
const rejected = computed(() => projects.value.filter((p) => p.status === "rejected").length);

onMounted(() => {
    projectStore.getProjects();
});
</script>

<template>
    <AuthLayout>
        <div class="p-2 sm:p-5 lg:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-6 gap-2 sm:gap-0">
                <div>
                    <h1 class="text-base sm:text-xl lg:text-2xl font-semibold text-gray-900">Project Management</h1>
                    <p class="text-gray-600">Create, Manage, and Track Barangay Projects</p>
                </div>
                <router-link to="/projects/add-project"
                    class="bg-green-700 text-white px-3 py-1.5 sm:py-2 rounded-md hover:bg-green-800 transition-colors flex items-center justify-center gap-1.5 text-xs sm:text-sm font-medium">
                    <PlusIcon class="w-3 h-3 sm:w-4 sm:h-4" />
                    <span>New Project</span>
                </router-link>
            </div>

            <!-- Cards Grid - Updated to 3 columns for better layout -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6 mb-4 sm:mb-8">
                <!-- Total Projects -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Total</div>
                        <FolderIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-blue-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ totalProjects }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">All projects</div>
                </div>

                <!-- Approved -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Approved</div>
                        <HandThumbUpIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-teal-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ approved }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Approved projects</div>
                </div>

                <!-- Rejected -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Rejected</div>
                        <XCircleIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-red-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ rejected }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Rejected projects</div>
                </div>

                <!-- Planning -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Planning</div>
                        <ChartPieIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-orange-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ planning }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">In planning phase</div>
                </div>

                <!-- In Progress -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">In Progress</div>
                        <ArrowPathIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-yellow-500 animate-spin" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ inProgress }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Currently working</div>
                </div>

                <!-- On Hold -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">On Hold</div>
                        <PauseCircleIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-purple-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ onHold }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Temporarily paused</div>
                </div>

                <!-- Completed -->
                <div class="bg-white rounded-md sm:rounded-lg p-2.5 sm:p-4 lg:p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-1 sm:mb-3">
                        <div class="text-xs font-medium text-gray-600 leading-tight">Completed</div>
                        <CheckCircleIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-green-500" />
                    </div>
                    <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ completed }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 sm:mt-1 hidden sm:block">Finished projects</div>
                </div>

            </div>

            <!-- Nested List Project View -->
            <router-view />
        </div>
    </AuthLayout>
</template>