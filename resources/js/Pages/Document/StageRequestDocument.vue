<script setup>
import { onMounted, watch } from 'vue';
import { AuthLayout } from '@/Layouts';
import draggable from 'vuedraggable';
import { useDocumentRequestStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { DocumentIcon, ClockIcon } from '@heroicons/vue/24/outline';
import { useRouter } from 'vue-router';
import { useDraggable } from '@/Components/Composables/useDraggable';

const documentRequestStore = useDocumentRequestStore();
const router = useRouter();
const { documentRequests, isLoading } = storeToRefs(documentRequestStore);

const {
    stages,
    getStatusColor,
    handleDragEnd,
    updateStages
} = useDraggable(documentRequestStore);



watch(() => documentRequests.value, updateStages,
    { immediate: true });

onMounted(async () => {
    try {
        await documentRequestStore.searchDocumentRequests(
            router.currentRoute.value.params.documentType
        );
    } catch (error) {
        console.error('Error fetching document requests:', error);
    }
});
</script>

<template>
    <AuthLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Document Request Board</h1>
            <p class="mt-2 text-sm text-gray-600">
                Manage and track document requests across different stages
            </p>
        </div>

        <div class="flex space-x-4 overflow-x-auto pb-4">
            <div v-for="stage in stages" :key="stage.id" class="flex-1 min-w-[320px] bg-white rounded-lg shadow-sm">
                <!-- Stage Header -->
                <div class="p-4 border-b">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">{{ stage.name }}</h3>
                        <span class="px-3 py-1 text-sm rounded-full" :class="getStatusColor(stage.id)">
                            {{ stage.requests.length }}
                        </span>
                    </div>
                </div>

                <!-- Draggable Container -->
                <draggable v-model="stage.requests" :group="{ name: 'requests' }" :data-stage="stage.id"
                    class="min-h-[200px] p-4 space-y-3" @end="handleDragEnd" item-key="id">
                    <template #item="{ element }">
                        <div class="bg-white border rounded-lg shadow-sm hover:shadow-md
                                    transition-shadow duration-200 cursor-move">
                            <div class="p-4">
                                <!-- Document Type -->
                                <div class="flex items-center justify-between mb-3">
                                    <span class="flex items-center text-sm font-medium text-gray-700">
                                        <DocumentIcon class="w-4 h-4 mr-2" />
                                        {{ element.document_type }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        #{{ element.id }}
                                    </span>
                                </div>

                                <!-- Requestor Info -->
                                <div class="mb-3">
                                    <h4 class="text-sm font-medium text-gray-900">
                                        {{ element.requestor_name }}
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        {{ element.requestor_email }}
                                    </p>
                                </div>

                                <!-- Footer -->
                                <div class="flex items-center justify-between pt-3 border-t">
                                    <span class="flex items-center text-xs text-gray-500">
                                        <ClockIcon class="w-4 h-4 mr-1" />
                                        {{ new Date(element.created_at).toLocaleDateString() }}
                                    </span>
                                    <span :class="getStatusColor(element.status)"
                                        class="px-2 py-1 text-xs rounded-full">
                                        {{ element.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <template #footer>
                        <div v-if="stage.requests.length === 0"
                            class="flex flex-col items-center justify-center py-8 text-gray-400">
                            <DocumentIcon class="w-8 h-8 mb-2" />
                            <p class="text-sm">No requests in this stage</p>
                        </div>
                    </template>
                </draggable>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
/* .sortable-ghost {
    @apply opacity-50 bg-gray-100;
}

.sortable-drag {
    @apply cursor-grabbing;
} */
</style>
