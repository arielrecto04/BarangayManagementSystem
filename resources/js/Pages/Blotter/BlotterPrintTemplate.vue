<script setup>
import { computed } from 'vue'

const props = defineProps({
    blotter: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['close'])

const formatDate = (dateStr) => {
    if (!dateStr) return { day: '__', month: '__', year: '__', time: '__:__' }

    const date = new Date(dateStr)
    return {
        day: date.getDate(),
        month: date.toLocaleDateString('en-US', { month: 'long' }),
        year: date.getFullYear(),
        time: date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        }),
    }
}

const incidentDate = computed(() => formatDate(props.blotter.datetime_of_incident))
const filingDate = computed(() => formatDate(props.blotter.filing_date))

const handlePrint = () => {
    window.print()
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm print-area">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[95vh] overflow-hidden">
            <!-- Modal Controls - Hidden in print -->
            <div class="no-print flex justify-between items-center p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Blotter Form - Print Preview</h2>
                <div class="flex gap-2">
                    <button @click="handlePrint"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                        Print
                    </button>
                    <button @click="$emit('close')"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition-colors">
                        Close
                    </button>
                </div>
            </div>

            <!-- Printable Content -->
            <div class="p-8 overflow-auto max-h-[calc(95vh-80px)] printable-content"
                style="font-family: 'Times New Roman', serif;">
                <div class="relative flex items-center justify-center mb-8">
                    <!-- Centered Text -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 text-center">
                        <div class="text-sm font-bold mb-1">Republic of the Philippines</div>
                        <div class="text-sm font-bold mb-1">City Government of Cotabato</div>
                        <div class="text-sm font-bold mb-1">Barangay Immaculada Concepcion - RH2</div>
                        <div class="text-sm font-bold">OFFICE OF THE BARANGAY COUNCIL</div>
                    </div>

                    <!-- Logo on the right -->
                    <div
                        class="ml-auto w-32 h-32 border border-dashed border-gray-400 flex items-center justify-center">
                        <div class="text-sm text-gray-500 text-center">(Barangay Logo)</div>
                    </div>
                </div>

                <!-- Main Grid -->
                <div class="grid grid-cols-12 gap-6 mb-6">
                    <!-- Left Column -->
                    <div class="col-span-6">
                        <div class="mb-4">
                            <div class="font-bold text-sm mb-2">Complainant</div>
                            <div class="border-b border-black pb-1 mb-2">
                                {{ blotter.complainant_name || '________________________' }}
                            </div>
                            <div class="text-center font-bold text-sm">-against-</div>
                        </div>
                        <div class="mb-4">
                            <div class="font-bold text-sm mb-2">Respondent</div>
                            <div class="border-b border-black pb-1">
                                {{ blotter.respondent_name || '________________________' }}
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Case Details -->
                    <div class="col-span-6 flex flex-col justify-start">
                        <div class="space-y-3 text-sm">
                            <div class="flex items-baseline">
                                <strong class="w-40">Blotter Number:</strong>
                                <span class="border-b border-black flex-1">{{ blotter.blotter_no || '__________'
                                    }}</span>
                            </div>
                            <div class="flex items-baseline">
                                <strong class="w-40">Case Number:</strong>
                                <span class="border-b border-black flex-1">{{ blotter.case_no || '__________' }}</span>
                            </div>
                            <div class="flex items-baseline">
                                <strong class="w-40">Nature of Case:</strong>
                                <span class="border-b border-black flex-1">{{ blotter.nature_of_case || '__________'
                                    }}</span>
                            </div>
                            <div class="flex items-baseline">
                                <strong class="w-40">Blotter Type:</strong>
                                <span class="border-b border-black flex-1">{{ blotter.blotter_type || '__________'
                                    }}</span>
                            </div>
                            <div class="flex items-baseline">
                                <strong class="w-40">Witness/es:</strong>
                                <span class="border-b border-black flex-1">{{ blotter.witness || '__________' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center my-6">
                    <h2 class="text-lg font-bold tracking-widest">BLOTTER DETAILS</h2>
                </div>

                <!-- Description -->
                <div class="mb-6 text-sm">
                    <div class="mb-2">
                        I/We hereby set forth the following information regarding the blotter case:
                    </div>
                    <div class="border border-black p-3 min-h-[150px] whitespace-pre-line">
                        {{ blotter.description || '' }}
                    </div>
                </div>

                <!-- Incident Date -->
                <div class="mb-8 text-sm">
                    Occurred on the <span class="border-b border-black px-2">{{ incidentDate.day }}</span> day
                    of
                    <span class="border-b border-black px-2">{{ incidentDate.month }}</span>,
                    <span class="border-b border-black px-2">{{ incidentDate.year }}</span> at
                    <span class="border-b border-black px-2">{{ incidentDate.time }}</span>.
                </div>

                <!-- Filing Date -->
                <div class="mb-8 text-sm">
                    Received and filed this <span class="border-b border-black px-2">{{ filingDate.day }}</span> day of
                    <span class="border-b border-black px-2">{{ filingDate.month }}</span>,
                    <span class="border-b border-black px-2">{{ filingDate.year }}</span> at
                    <span class="border-b border-black px-2">{{ filingDate.time }}</span>.
                </div>

                <!-- Signature -->
                <div class="flex justify-end">
                    <div class="text-center">
                        <div class="border-b border-black w-48 mb-2"></div>
                        <div class="text-sm font-bold">Barangay Captain/Lupon Chairman</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Improved print styles */
@media print {
    @page {
        size: auto;
    }

    body,
    html {
        height: auto !important;
        overflow: visible !important;
        background: white !important;
        font-family: "Times New Roman", serif !important;
        font-size: 12pt !important;
        /* Base font size for print */
        line-height: 1.4 !important;
    }

    body>*:not(.print-area) {
        display: none !important;
    }

    .print-area {
        position: fixed !important;
        inset: 0 !important;
        background: white !important;
        display: block !important;
        z-index: 99999 !important;
        overflow: visible !important;
        max-height: none !important;
    }

    .no-print {
        display: none !important;
    }

    .printable-content {
        padding: 0.75in !important;
        font-size: 12pt !important;
        max-height: none !important;
        overflow: visible !important;
    }

    /* Remove all screen-only styles */
    .rounded-lg,
    .shadow-xl,
    .overflow-hidden {
        all: unset !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        overflow: visible !important;
    }

    /* Optional: Ensure text aligns properly for print */
    h1,
    h2,
    h3,
    strong {
        font-weight: 700 !important;
    }

    .text-sm {
        font-size: 11pt !important;
    }

    .text-lg {
        font-size: 13pt !important;
    }

    .text-center {
        text-align: center !important;
    }

    .text-right {
        text-align: right !important;
    }

    .text-left {
        text-align: left !important;
    }
}
</style>
