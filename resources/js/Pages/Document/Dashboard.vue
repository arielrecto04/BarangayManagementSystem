<script setup>
import { AuthLayout } from '@/Layouts';
import { onMounted, computed, ref, watch } from 'vue';
import { BarChart } from '@/Components/chart';
import { Table, Badge } from '@/Components';
import { Squares2X2Icon, TableCellsIcon, DocumentPlusIcon } from '@heroicons/vue/24/outline';
import { DocumentTypeEnum } from '@/Enums'
import { useDocumentRequestStore } from '@/Stores/useDocumentRequestStore';
import { storeToRefs } from 'pinia';
import { useRouter, useRoute } from 'vue-router';

const documentRequestStore = useDocumentRequestStore()
const router = useRouter()
const route = useRoute()



const { statistic, documentRequests } = storeToRefs(documentRequestStore)
const viewType = ref(router.currentRoute.value.query.viewType || 'dashboard');
const columns = ref([
    {
        label: 'Document Type',
        key: 'document_type',
    },
    {
        label: 'Email',
        key: 'requestor_email',
    },
    {
        label: 'Name',
        key: 'requestor_name',
    },
    {
        label: 'Contact',
        key: 'requestor_contact_number',
    },
    {
        label: 'Address',
        key: 'requestor_address',
    },
    {
        label: 'Remarks',
        key: 'remarks',
    },
    {
        label: 'Status',
        key: 'status',
    },
])


const totalByDocumentType = computed(() => {
    if (!statistic?.value) return [];
    return statistic.value?.total_by_document_type.map((item) => {
        return {
            document_type: item.document_type,
            count: item.count,
            total_requests_by_status: item.total_requests_by_status
        }
    })
})


const changeViewType = (type) => {
    viewType.value = type;
    router.replace({ query: { viewType: type } })
}


watch(viewType, () => {

    if (viewType.value === 'list') {
        documentRequestStore.fetchDocumentRequests()
    }
})


const selectedByDocumentType = (documentType) => {
    viewType.value = 'list';
    router.replace({ query: { viewType: 'list', documentType: documentType } })

}

onMounted(async () => {

    try {

        if (viewType.value === 'list') {
            await documentRequestStore.fetchDocumentRequests()
        }
        // await documentRequestStore.statistic()

    } catch (error) {
        console.log(error);
    }
})


</script>


<template>
    <AuthLayout>
        <div class="p-6 flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
            <div class="flex gap-2 ">
                <button @click="changeViewType('dashboard')"
                    :class="viewType === 'dashboard' ? 'bg-green-700 hover:bg-gray-300 text-white font-semibold py-2 px-4 rounded inline-flex items-center' : 'bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded inline-flex items-center'">
                    <Squares2X2Icon class="w-4 h-4" />
                </button>
                <button @click="changeViewType('list')"
                    :class="viewType === 'list' ? 'bg-green-700 hover:bg-gray-300 text-white font-semibold py-2 px-4 rounded inline-flex items-center' : 'bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded inline-flex items-center'">
                    <TableCellsIcon class="w-4 h-4" />
                </button>
            </div>
        </div>




        <div v-show="viewType === 'dashboard'" class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="p-4 h-96 bg-white flex flex-col justify-between shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex justify-between">
                    <router-link
                        :to="{ name: 'Stage Request Document', params: { documentType: DocumentTypeEnum.CertificateOfResidency } }"
                        class="font-medium text-gray-900">Barangay Certificate of Residency</router-link>
                    <router-link
                        :to="{ name: 'Add Document', query: { documentType: DocumentTypeEnum.CertificateOfResidency } }"
                        class="bg-green-700 hover:bg-green-800 rounded-lg text-white p-2 flex items-center gap-2">
                        <DocumentPlusIcon class="w-4 h-4" />
                        <span>Add New</span>
                    </router-link>
                </div>

                <div class="flex flex-col justify-end">
                    <p class="text-sm text-gray-500">{{totalByDocumentType?.find((item) => item.document_type ===
                        DocumentTypeEnum.CertificateOfResidency)?.count}}</p>
                </div>
                <div class="w-full h-full mt-4">
                    <BarChart :data="{
                        labels: ['new request', 'approved', 'processing', 'rejected', 'cancelled'],
                        datasets: [
                            {
                                label: 'Barangay Certificate of Residency',
                                data: [10, 20, 30],
                                backgroundColor: [
                                    '#e5e7eb',
                                    '#00b4d8',
                                    '#a3e635',
                                    '#f59e0b',
                                    '#ef4444',
                                ],
                                borderRadius: 4,
                            }
                        ]
                    }" :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    display: false,
                                },
                                grid: {
                                    drawTicks: false,
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                }
                            }
                        }
                    }" />
                </div>
            </div>
            <div
                class="p-4 h-96 bg-white flex flex-col justify-between shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center justify-between">
                    <a @click="selectedByDocumentType(DocumentTypeEnum.BarangayID)"
                        class="font-medium text-gray-900">Barangay ID</a>
                    <router-link :to="{ name: 'Add Document', query: { documentType: DocumentTypeEnum.BarangayID } }"
                        class="bg-green-700 hover:bg-green-800 rounded-lg text-white p-2 flex items-center gap-2">
                        <DocumentPlusIcon class="w-4 h-4" />
                        <span>Add New</span>
                    </router-link>
                </div>
                <div class="w-full h-full mt-4">
                    <BarChart :data="{
                        labels: ['new request', 'approved', 'processing', 'rejected', 'cancelled'],
                        datasets: [
                            {
                                label: 'Barangay ID',
                                data: [10, 20, 30],
                                backgroundColor: [
                                    '#e5e7eb',
                                    '#00b4d8',
                                    '#a3e635',
                                    '#f59e0b',
                                    '#ef4444',
                                ],
                                borderRadius: 4,
                            }
                        ]
                    }" :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    display: false,
                                },
                                grid: {
                                    drawTicks: false,
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                }
                            }
                        }
                    }" />
                </div>
            </div>
            <div
                class="p-4 h-96 bg-white flex flex-col justify-between shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center justify-between">
                    <a @click="selectedByDocumentType(DocumentTypeEnum.BarangayClearance)"
                        class="font-medium text-gray-900">Barangay Clearance</a>
                    <router-link
                        :to="{ name: 'Add Document', query: { documentType: DocumentTypeEnum.BarangayClearance } }"
                        class="bg-green-700 hover:bg-green-800 rounded-lg text-white p-2 flex items-center gap-2">
                        <DocumentPlusIcon class="w-4 h-4" />
                        <span>Add New</span>
                    </router-link>

                </div>
                <div class="w-full h-full mt-4">
                    <BarChart :data="{
                        labels: ['new request', 'approved', 'processing', 'rejected', 'cancelled'],
                        datasets: [
                            {
                                label: 'Barangay ID',
                                data: [10, 20, 30],
                                backgroundColor: [
                                    '#e5e7eb',
                                    '#00b4d8',
                                    '#a3e635',
                                    '#f59e0b',
                                    '#ef4444',
                                ],

                                borderRadius: 4,
                            }
                        ]
                    }" :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    display: false,
                                },
                                grid: {
                                    drawTicks: false,
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                }
                            }
                        }
                    }" />
                </div>
            </div>
            <div
                class="p-4 h-96 bg-white flex flex-col justify-between shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center justify-between">
                    <a @click="selectedByDocumentType(DocumentTypeEnum.CertificateOfIndigency)"
                        class="font-medium text-gray-900">Certificate of Indigency</a>
                    <router-link
                        :to="{ name: 'Add Document', query: { documentType: DocumentTypeEnum.CertificateOfIndigency } }"
                        class="bg-green-700 hover:bg-green-800 rounded-lg text-white p-2 flex items-center gap-2">
                        <DocumentPlusIcon class="w-4 h-4" />
                        <span>Add New</span>
                    </router-link>
                </div>
                <div class="w-full h-auto mt-4">
                    <BarChart :data="{
                        labels: ['new request', 'approved', 'processing', 'rejected', 'cancelled'],
                        datasets: [
                            {
                                label: 'Barangay ID',
                                data: [10, 20, 30],
                                backgroundColor: [
                                    '#e5e7eb',
                                    '#00b4d8',
                                    '#a3e635',
                                    '#f59e0b',
                                    '#ef4444',
                                ],
                                borderRadius: 4,
                            }
                        ]
                    }" :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    display: false,
                                },
                                grid: {
                                    drawTicks: false,
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                }
                            }
                        }
                    }" />
                </div>
            </div>
            <div
                class="p-4 h-96 bg-white flex flex-col justify-between shadow-sm rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center justify-between">
                    <a @click="selectedByDocumentType(DocumentTypeEnum.BusinessPermit)"
                        class="font-medium text-gray-900">Business Permit</a>
                    <router-link
                        :to="{ name: 'Add Document', query: { documentType: DocumentTypeEnum.BusinessPermit } }"
                        class="bg-green-700 hover:bg-green-800 rounded-lg text-white p-2 flex items-center gap-2">
                        <DocumentPlusIcon class="w-4 h-4" />
                        <span>Add New</span>
                    </router-link>
                </div>
                <div class="w-full h-full mt-4">
                    <BarChart :data="{
                        labels: ['new request', 'approved', 'processing', 'rejected', 'cancelled'],
                        datasets: [
                            {
                                label: 'Barangay ID',
                                data: [10, 20, 30],
                                backgroundColor: [
                                    '#e5e7eb',
                                    '#00b4d8',
                                    '#a3e635',
                                    '#f59e0b',
                                    '#ef4444',
                                ],
                                borderRadius: 4,
                            }
                        ]
                    }" :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    display: false,
                                },
                                grid: {
                                    drawTicks: false,
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                }
                            }
                        }
                    }" />
                </div>
            </div>
        </div>

        <template v-if="viewType === 'list'">
            <div>
                <Table :columns="columns" :rows="documentRequests">

                </Table>
            </div>
        </template>

    </AuthLayout>
</template>
