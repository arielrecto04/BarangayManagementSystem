<script setup>
import { computed } from 'vue';
import { Municipal_Information, Barangay_Information } from '@/Lang/en';

const props = defineProps({
    /**
     * Object containing details about the resident.
     */
    resident: {
        type: Object,
        required: true,
        default: () => ({
            name: 'Resident Name',
            civilStatus: 'Single',
            citizenship: 'Filipino'
        })
    },
    /**
     * Object containing details about the issuing Barangay.
     */
    barangayInfo: {
        type: Object,
        required: true,

        default: () => ({
            name: Barangay_Information.title,
            municipality: Municipal_Information.municipality,
            province: Municipal_Information.province,
            captainName: 'Captain Name'
        })
    },
    /**
     * Object containing details about the certificate's issuance.
     */
    issueDetails: {
        type: Object,
        required: true,
        default: () => ({
            date_issued: '2023-10-01',
            issued_by: 'Barangay Captain Name',
            day: '01',
            month: 'October',
            year: '2023',
            location: 'Barangay Hall'
        })
    },
    /**
     * URL for the left seal (e.g., Municipal seal).
     */
    leftSealUrl: {
        type: String,
        default: Municipal_Information.municipal_seal
    },
    /**
     * URL for the right seal (e.g., Barangay seal).
     */
    rightSealUrl: {
        type: String,
        default: Barangay_Information.barangay_seal
    }
});

// Computed property to format the resident's full name
const fullResidentName = computed(() => {
    return `Mr./Ms. ${props.resident.name.toUpperCase()}`;
});
</script>

<template>
    <div
        class="font-serif bg-white text-black w-[21cm] max-w-full my-8 mx-auto p-[1.5cm_2cm] border border-gray-700 shadow-lg flex flex-col">

        <header class="flex justify-between items-start text-center mb-5">
            <div class="flex-shrink-0">
                <img :src="leftSealUrl" alt="Municipal Seal" class="w-[85px] h-[85px]">
            </div>
            <div class="flex-grow px-5 leading-tight">
                <p>Republic of the Philippines</p>
                <p>Province of {{ barangayInfo.province }}</p>
                <p>Municipality of {{ barangayInfo.municipality }}</p>
                <p class="font-bold mt-1">BARANGAY {{ barangayInfo.name.toUpperCase() }}</p>
            </div>
            <div class="flex-shrink-0">
                <img :src="rightSealUrl" alt="Barangay Seal" class="w-[85px] h-[85px]">
            </div>
        </header>

        <div class="text-center font-bold mb-8">
            <p>OFFICE OF THE BARANGAY CAPTAIN</p>
        </div>

        <h1 class="text-center font-bold text-2xl mb-10 tracking-widest uppercase">
            Certificate of Residency
        </h1>

        <section class="flex-grow">
            <p class="font-bold mb-5">TO WHOM IT MAY CONCERN:</p>

            <p class="indent-[50px] leading-loose mb-5 text-justify">
                This is to certify that <strong class="font-bold">{{ fullResidentName }}</strong>, of legal age, {{
                    resident.civilStatus }}, {{ resident.citizenship }} citizen, whose specimen signature appears below, is
                a <strong class="font-bold">PERMANENT RESIDENT</strong> of this Barangay {{ barangayInfo.name }}, {{
                    barangayInfo.municipality }}, {{ barangayInfo.province }}.
            </p>

            <p class="indent-[50px] leading-loose mb-5 text-justify">
                Based on records of this office, she has been residing at Barangay {{ barangayInfo.name }}, Municipality
                of {{ barangayInfo.municipality }}, {{ barangayInfo.province }}.
            </p>

            <p class="indent-[50px] leading-loose mb-5 text-justify">
                This <strong class="font-bold">CERTIFICATION</strong> is being issued upon the request of the
                above-named person for whatever legal purpose it may serve.
            </p>

            <p class="indent-[50px] leading-loose mb-5 text-justify">
                Issued this {{ issueDetails.day }} day of {{ issueDetails.month }}, {{ issueDetails.year }} at {{
                    issueDetails.location }}.
            </p>
        </section>

        <footer class="mt-12">
            <div class="flex justify-between items-end">
                <div class="w-[45%]">
                    <p>Specimen Signature:</p>
                    <div class="mt-1 h-10 border-b border-black"></div>
                </div>

                <div class="w-[45%] text-center">
                    <p class="font-bold uppercase mt-10">{{ barangayInfo.captainName.toUpperCase() }}</p>
                    <p class="inline-block mt-0.5 pt-0.5 border-t border-black">Punong Barangay</p>
                </div>
            </div>

            <div class="mt-16 italic text-sm">
                <p>Note:</p>
                <p>'Not valid without official seal!'</p>
            </div>
        </footer>
    </div>
</template>
