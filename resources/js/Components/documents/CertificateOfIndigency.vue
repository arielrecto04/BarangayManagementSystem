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
        province: Municipal_Information.title,
        municipality: Municipal_Information.municipality,
        barangayName: Barangay_Information.title,
        contactNumber: Municipal_Information.contact_number,
        leftSealUrl: Barangay_Information.barangay_seal,

    })
  },
  /**
   * Information about the applicant.
   */
  applicantInfo: {
    type: Object,
    required: true,
    default : () => ({
        name: 'John Doe',
        civilStatus: 'Single',
        contactNumber: '123-456-7890',
    })
  },
  /**
   * Details about the poverty threshold.
   */
  indigencyInfo: {
    type: Object,
    required: true,
    default : () => ({
        povertyThreshold: '10000',
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
        year: new Date().getFullYear(),
        location: Barangay_Information.address,
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
        civilStatus: 'Single',
        contactNumber: '123-456-7890',
    })
  },
});
</script>


<template>
    <div class="bg-white text-black p-12 shadow-lg w-[21cm] min-h-[29.7cm] mx-auto my-8 font-serif flex flex-col">
      <header class="flex justify-center items-center text-center space-x-8">
        <img :src="headerInfo.leftSealUrl" alt="Barangay Seal" class="h-24 w-24 object-contain">
        <div>
          <p>REPUBLIC OF THE PHILIPPINES</p>
          <p>Province of {{ headerInfo.province }}</p>
          <p>Municipality of {{ headerInfo.municipality }}</p>
          <p class="font-bold text-lg">{{ headerInfo.barangayName.toUpperCase() }}</p>
          <p class="font-bold italic">Office of the Punong Barangay</p>
        </div>
      </header>

      <hr class="border-t border-black my-4">

      <h1 class="text-center font-bold text-xl uppercase tracking-wider my-10">
        Certificate of Indigency
      </h1>

      <main class="flex-grow text-lg leading-loose">
        <p class="mb-8">TO WHOM IT MAY CONCERN:</p>

        <p class="indent-12 text-justify">
          This is to <strong class="font-bold">CERTIFY</strong> that Mr./Ms. <span class="font-bold underline px-4">{{ applicantInfo.name }}</span>, of legal age, {{ applicantInfo.civilStatus }}, Filipino Citizen and a resident of Barangay <span class="font-bold underline px-4">{{ headerInfo.barangayName }}</span>, belongs to the Indigent Families of this Barangay having an annual income not exceeding the Regional Poverty Threshold (RPT) of <span class="font-bold">{{ indigencyInfo.povertyThreshold }}</span> per annum as determined by the National Economic Development Authority (NEDA).
        </p>

        <p class="indent-12 text-justify mt-6">
          This <strong class="font-bold">CERTIFICATION</strong> is issued upon the request of the above-mentioned individual for whatever legal purpose/s it may best serve him or her.
        </p>

        <p class="indent-12 text-justify mt-8">
          <strong class="font-bold">ISSUED</strong> this <span class="font-bold underline px-4">{{ issuanceInfo.day }}</span> day of <span class="font-bold underline px-4">{{ issuanceInfo.month }}</span>, {{ issuanceInfo.year }} at Barangay <span class="font-bold underline px-4">{{ issuanceInfo.location }}</span>.
        </p>
      </main>

      <footer class="mt-20">
        <div class="flex justify-end">
          <div class="w-1/2 text-center">
            <div class="border-b-2 border-black w-full h-12 mb-2">
               <img v-if="approverInfo.signatureUrl" :src="approverInfo.signatureUrl" alt="Signature" class="h-12 mx-auto">
            </div>
            <p class="font-bold text-sm">SIGNATURE</p>
            <p class="font-bold uppercase tracking-wider mt-4">{{ approverInfo.name }}</p>
            <p class="text-sm">PUNONG BARANGAY</p>
          </div>
        </div>

        <div class="text-xs mt-24">
          <p class="font-bold">SPECIAL NOTE ON THE DRY SEAL:</p>
          <ul class="list-disc list-inside italic">
            <li>"Place the Dry Seal IF AVAILABLE"</li>
            <li>"If the Barangay has NO dry seal, then leave the lower part of the Certificate EMPTY."</li>
            <li>"If the Certificate contains, NOT VALID WITHOUT SEAL, then the seal MUST BE PLACED"</li>
            <li>"If the NOT VALID WITHOUT SEAL is present but no dry seal has been placed then the Certificate is NOT VALID AND NOT ACCEPTED."</li>
          </ul>
        </div>
      </footer>
    </div>
  </template>

