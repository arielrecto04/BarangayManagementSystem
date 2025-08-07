<script setup>
import { onMounted, watch, ref, onUnmounted } from 'vue';
import { AuthLayout } from '@/Layouts';
import draggable from 'vuedraggable';
import { useDocumentRequestStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import { DocumentIcon, ClockIcon, ArrowLeftIcon, Squares2X2Icon, TableCellsIcon, EyeIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useRouter } from 'vue-router';
import { useDraggable } from '@/Components/Composables/useDraggable';
import { Table } from '@/Components';
import { CertificateOfResidency, BarangayID, BarangayClearance, CertificateOfIndigency, BusinessPermit } from '@/Components/documents';
import { DocumentTypeEnum } from '@/Enums';

const documentRequestStore = useDocumentRequestStore();
const router = useRouter();
const { documentRequests, isLoading, documentRequest } = storeToRefs(documentRequestStore);



const {
    stages,
    getStatusColor,
    handleDragEnd,
    updateStages
} = useDraggable(documentRequestStore);
const columns = [
    { key: 'document_type', label: 'Document Type' },
    { key: 'requestor_name', label: 'Requestor Name' },
    { key: 'requestor_email', label: 'Requestor Email' },
    { key: 'created_at', label: 'Created At' },
    { key: 'status', label: 'Status' }
];

const documentTypeValue = ref(router.currentRoute.value.params.documentType);



const viewType = ref(router.currentRoute.value.query.view || 'kanban');





const handleViewChange = (type) => {
    router.replace({
        query: { view: type }
    });


    console.log('View changed to:', type, documentRequests.value);
    viewType.value = type;
};


const previewDocumentRequest = (documentRequest) => {
    documentRequestStore.selectDocumentRequest(documentRequest);
}




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

onUnmounted(() => {
    documentRequestStore.removeDocumentRequest();
});
</script>


<template>
    <AuthLayout>
        <div class="mb-6">

            <div class="flex items-center gap-2">
                <router-link :to="{ name: 'Document Dashboard' }"
                    class="text-green-700 hover:underline hover:text-white hover:bg-green-700 p-2 rounded-lg transition-transform duration-300">
                    <ArrowLeftIcon class="w-5 h-5" />
                </router-link>

                <h1 class="text-2xl font-semibold text-gray-800">
                    Document Requests
                </h1>
            </div>

            <div class="flex justify-between items-center mt-4">
                <p class="mt-2 text-sm text-gray-600">
                    Manage and track document requests across different stages
                </p>


                <div class="flex items-center gap-2">
                    <button @click="handleViewChange('kanban')"
                        :class="viewType == 'kanban' ? `bg-green-700 p-2 rounded-lg hover:bg-green-800 text-white` : `bg-gray-200 hover:bg-gray-300 text-gray-700 p-2 rounded-lg`">
                        <Squares2X2Icon class="w-5 h-5" />
                    </button>
                    <button @click="handleViewChange('list')"
                        :class="viewType == 'list' ? `bg-green-700 p-2 rounded-lg hover:bg-green-800 text-white` : `bg-gray-200 hover:bg-gray-300 text-gray-700 p-2 rounded-lg`">
                        <TableCellsIcon class="w-5 h-5" />
                    </button>
                </div>

            </div>



            <div class="w-full mt-4">
                <div class="flex space-x-4 overflow-x-auto pb-4" v-show="viewType == 'kanban'">
                    <div v-for="stage in stages" :key="stage.id"
                        class="flex-1 min-w-[320px] bg-white rounded-lg shadow-sm">
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
                <div v-show="viewType == 'list'">

                    <Table :columns="columns" :rows="documentRequests">

                        <template #cell(status)="{ row }">
                            <span :class="getStatusColor(row.status)" class="px-2 py-1 text-xs rounded-full">{{
                                row.status
                            }}</span>

                        </template>

                        <template #actions="{ row }">
                            <button @click="previewDocumentRequest(row)"
                                class="bg-green-700 text-white px-2 py-1 rounded hover:bg-green-800 transition-colors duration-200">
                                <EyeIcon class="w-4 h-4" />
                            </button>
                        </template>
                    </Table>


                    <template v-if="documentRequest">
                        <CertificateOfResidency :resident="{
                            name: documentRequest.requestor_name,
                            address: documentRequest.requestable.address,
                            contact: documentRequest.requestable.contact,
                            email: documentRequest.requestable.email,

                        }" v-if="documentTypeValue == 'Certificate of Residency'" />
                        <BarangayID :memberInfo="{
                            name: documentRequest.requestor_name,
                            address: documentRequest.requestable.address,
                            contact: documentRequest.requestable.contact,
                            email: documentRequest.requestable.email,
                            photoUrl: documentRequest.requestable.avatar,
                            position: 'Resident',
                            memberNumber: documentRequest.requestable.resident_number,
                            dateOfBirth: documentRequest.requestable.birthday,
                            sex: documentRequest.requestable.gender,
                            emergencyContact: documentRequest.requestable.emergency_contact,
                        }" v-if="documentTypeValue == 'Barangay ID'" />

                        <BarangayClearance :applicantInfo="{
                            name: documentRequest.requestor_name,
                            activity: documentRequest.remarks,
                            location: documentRequest.requestable.address
                        }" v-if="documentTypeValue == 'Barangay Clearance'" />

                        <CertificateOfIndigency :applicantInfo="{
                            name: documentRequest.requestor_name,
                            civilStatus: documentRequest.requestable.civil_status,
                            contactNumber: documentRequest.requestable.contact,
                        }" v-if="documentTypeValue == 'Certificate of Indigency'" />

                        <BusinessPermit v-if="documentTypeValue == 'Business Permit'" />
                    </template>
                </div>




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
