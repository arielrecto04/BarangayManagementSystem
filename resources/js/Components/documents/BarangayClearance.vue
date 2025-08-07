<script setup>

import { Municipal_Information, Barangay_Information } from '@/Lang/en';
defineProps({
    /**
     * Information for the document header.
     */
    headerInfo: {
        type: Object,
        required: true,
        default : () => ({
            leftSealUrl: Municipal_Information.municipal_seal,
            provinceCity: Municipal_Information.province,
            barangayName: Barangay_Information.title,
            contactNumber: Municipal_Information.contact_number,
            rightSealUrl: Barangay_Information.barangay_seal
        })
    },
    /**
     * Information about the applicant and their activity.
     */
    applicantInfo: {
        type: Object,
        required: true,
        default : () => ({
            name: 'John Doe',
            activity: 'Activity',
            location: 'Location'
        })
    },
    /**
     * Details regarding the issuance date of the certificate.
     */
    issuanceInfo: {
        type: Object,
        required: true,
        default : () => ({
            day: new Date().getDate(),
            month: new Date().getMonth() + 1,
            year: new Date().getFullYear()
        })
    },
    /**
     * Details of the approving official.
     */
    approverInfo: {
        type: Object,
        required: true,
        default : () => ({
            name: 'John Doe',
            position: 'Position'
        })
    }
});
</script>



<template>
    <div class="bg-white text-black p-12 shadow-lg w-[21cm] min-h-[29.7cm] mx-auto my-8 font-serif flex flex-col">
        <header class="flex justify-between items-center text-center">
            <img :src="headerInfo.leftSealUrl" alt="City Seal" class="h-24 w-24 object-contain">
            <div>
                <p>Republic of the Philippines</p>
                <p>{{ headerInfo.provinceCity }}</p>
                <p class="font-bold text-lg">{{ headerInfo.barangayName.toUpperCase() }}</p>
                <p class="text-sm">Mobile No. {{ headerInfo.contactNumber }}</p>
            </div>
            <img :src="headerInfo.rightSealUrl" alt="Barangay Seal" class="h-24 w-24 object-contain">
        </header>

        <hr class="border-t-2 border-red-600 my-4">

        <h1 class="text-center font-bold text-xl uppercase tracking-wider my-12">
            Barangay Clearance Certificate
        </h1>

        <main class="flex-grow">
            <p class="mb-8">TO WHOM IT MAY CONCERN:</p>

            <div class="space-y-6 text-justify leading-relaxed">
                <p class="indent-12">
                    This is to certify that Barangay Clearance Certificate is hereby granted and issued to <strong
                        class="font-bold">{{ applicantInfo.name.toUpperCase() }}</strong>, {{ applicantInfo.activity }}
                    {{ applicantInfo.location }}.
                </p>
                <p class="indent-12">
                    This Certification is being issued upon the request of <strong class="font-bold">{{
                        applicantInfo.name.toUpperCase() }}</strong>, for whatever legal purpose it may serve.
                </p>
                <p class="indent-12">
                    Furthermore, this barangay interposes no objection for this activity.
                </p>
                <p class="indent-12 mt-8">
                    Given this <strong class="font-bold">{{ issuanceInfo.day }}</strong> day of <strong
                        class="font-bold">{{ issuanceInfo.month }}, {{ issuanceInfo.year }}</strong>.
                </p>
            </div>
        </main>


        <footer class="flex justify-end mt-24">
            <div class="w-1/2 text-center">
                <p class="mb-8">Approved By:</p>
                <img v-if="approverInfo.signatureUrl" :src="approverInfo.signatureUrl" alt="Signature"
                    class="h-12 mx-auto">
                <div v-else class="h-12"></div>
                <p class="font-bold uppercase mt-2 border-b-2 border-black pb-1">{{ approverInfo.name }}</p>
                <p class="text-sm">{{ approverInfo.position }}</p>
            </div>
        </footer>
    </div>
</template>
