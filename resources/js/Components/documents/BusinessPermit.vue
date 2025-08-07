<script setup>

import { Municipal_Information, Barangay_Information } from '@/Lang/en';
defineProps({
    headerInfo: {
        type: Object, required: true, default: () => ({
            provinceName: Barangay_Information.title,
            municipalityName: Municipal_Information.title,
            sealUrl: Barangay_Information.barangay_seal,
        })
    },
    permitInfo: {
        type: Object, required: true, default: () => ({
            permitNumber: '123456',
            permitYear: '2023',
            ownerName: 'John Doe',
            businessName: 'Business Name',
            businessAddress: 'Business Address',
            lineOfBusiness: 'Line of Business',
            paymentTerm: 'Payment Term',
        })
    },
    issuanceInfo: {
        type: Object, required: true, default: () => ({
            issueDay: new Date().getDate(),
            issueMonth: new Date().getMonth() + 1,
            issueLocation: Barangay_Information.address,
        })
    },
    paymentInfo: {
        type: Object, required: true, default: () => ({
            orNumber: '123456',
            dateIssued: '2023-01-01',
            expiryDate: '2023-01-01',
        })
    },
    approverInfo: {
        type: Object, required: true, default: () => ({
            name: 'John Doe',
            title: 'Title',
            signatureUrl: 'https://via.placeholder.com/150',
        })
    }
});
</script>


<template>
    <div
        class="bg-white text-black p-10 shadow-lg w-[21cm] min-h-[29.7cm] mx-auto my-8 font-serif flex flex-col relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-24 bg-gradient-to-r from-cyan-50 to-blue-100 -z-0"></div>
        <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-r from-purple-200 to-indigo-300 -z-0"></div>

        <div class="relative z-10 flex flex-col flex-grow">
            <header class="text-center mb-8">
                <img :src="headerInfo.sealUrl" alt="Municipal Seal" class="h-20 w-20 mx-auto mb-2 object-contain">
                <p>Republic of the Philippines</p>
                <p>Province of {{ headerInfo.provinceName }}</p>
                <p class="font-bold">{{ headerInfo.municipalityName.toUpperCase() }}</p>
                <div class="inline-block border-b-2 border-black mt-4 px-8">
                    <p class="text-lg">OFFICE OF THE MUNICIPAL MAYOR</p>
                </div>
            </header>

            <section class="text-center my-6">
                <h1 class="text-3xl font-bold text-red-600">BUSINESS PERMIT {{ permitInfo.permitYear }}</h1>
                <p class="font-bold mt-2">NO. <span class="underline">{{ permitInfo.permitNumber }}</span></p>
            </section>

            <main class="flex-grow text-lg leading-relaxed mt-6">
                <p class="text-justify indent-8">
                    Is <span class="font-semibold underline px-2">{{ permitInfo.ownerName }}</span>
                    hereby granted to
                    <span class="font-semibold underline px-2">{{ permitInfo.businessName }}</span>
                    of
                    <span class="font-semibold underline px-2">{{ permitInfo.businessAddress }}, {{
                        headerInfo.municipalityName }}</span>,
                    to operate the business of
                    <span class="font-semibold underline px-2">{{ permitInfo.lineOfBusiness }}</span>,
                    upon payment of the required license fee (s) ({{ permitInfo.paymentTerm }}), revoked or cancelled
                    for cause.
                </p>

                <p class="text-justify indent-8 mt-8">
                    This <strong class="font-bold">PERMIT</strong>, together with the official receipt/s shall be
                    displayed in a conspicuous place of business.
                </p>
            </main>

            <footer class="mt-12">
                <p class="indent-8 text-lg">
                    Issued this <span class="font-semibold underline px-2">{{ issuanceInfo.issueDay }}</span>
                    day of <span class="font-semibold underline px-2">{{ issuanceInfo.issueMonth }}, {{
                        permitInfo.permitYear }}</span>
                    at <span class="font-semibold underline px-2">{{ issuanceInfo.issueLocation }}</span>, Philippines.
                </p>

                <div class="mt-8 text-red-600 font-semibold text-lg space-y-2">
                    <p>PAID UNDER O.R. # <span class="underline">{{ paymentInfo.orNumber }}</span></p>
                    <p>DATE ISSUED: <span class="underline">{{ paymentInfo.dateIssued }}</span></p>
                    <p><strong class="font-bold">EXPIRY DATE:</strong> <span class="underline">{{ paymentInfo.expiryDate
                    }}</span></p>
                </div>

                <div class="flex justify-end mt-16">
                    <div class="w-2/5 text-center">
                        <img v-if="approverInfo.signatureUrl" :src="approverInfo.signatureUrl" alt="Signature"
                            class="h-12 mx-auto">
                        <div v-else class="h-12"></div>
                        <p class="font-bold text-xl border-t-2 border-black pt-2">{{ approverInfo.name.toUpperCase() }}
                        </p>
                        <p class="italic">{{ approverInfo.title }}</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>
