<script setup>
defineProps({
    /**
     * Information about the barangay and municipality.
     */
    barangayInfo: {
        type: Object,
        required: true,
        default: () => ({
            name: 'Barangay Name',
            municipality: 'Municipality Name',
            province: 'Province Name',
            captainName: 'Captain Name',
            logoUrl: 'https://via.placeholder.com/100/CCCCCC/FFFFFF?text=Barangay+Logo'
        })
    },
    /**
     * Information about the BADAC member.
     */
    memberInfo: {
        type: Object,
        required: true,
        default: () => ({
            name: 'Member Name',
            position: 'Position',
            memberNumber: '12345',
            address: 'Complete Address',
            dateOfBirth: 'YYYY-MM-DD',
        }),
    },
    /**
     * Information specific to the ID card itself.
     */
    idInfo: {
        type: Object,
        required: true,
        default: () => ({
            validUntil: 'YYYY-MM-DD',
            mayorName: 'Mayor Name'
        })
    }
});
</script>

<template>
    <div class="flex flex-wrap justify-center gap-8 p-8 bg-gray-200 font-sans">

        <div
            class="w-[5.4cm] h-[8.6cm] bg-white rounded-xl shadow-lg p-3 flex flex-col items-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-blue-50/70 -z-0 -skew-y-12 -translate-y-1/4"></div>

            <header class="text-center w-full z-10">
                <div class="flex items-center justify-center gap-2">
                    <img :src="barangayInfo.logoUrl" alt="ADAC Logo" class="w-10 h-10">
                    <div class="text-[8px] font-semibold text-gray-700 leading-tight">
                        <p>Republic of the Philippines</p>
                        <p>{{ barangayInfo.provinceName }}</p>
                        <p>{{ barangayInfo.municipalityName }}</p>
                    </div>
                </div>
                <p class="font-bold text-sm mt-1">{{ barangayInfo.name.toUpperCase() }}</p>
                <p class="text-[9px] font-bold text-gray-800">BARANGAY ANTI-DRUG ABUSE COUNCIL</p>
            </header>

            <div
                class="w-28 h-32 bg-gray-200 border-2 border-gray-300 rounded-md mt-4 flex items-center justify-center z-10">
                <img v-if="memberInfo.photoUrl" :src="memberInfo.photoUrl" alt="Member Photo"
                    class="w-full h-full object-cover rounded-md">
                <p v-else class="text-xs text-gray-500 text-center p-2">ATTACHED PHOTO HERE</p>
            </div>

            <p class="font-bold text-base text-center mt-3 z-10">{{ memberInfo.name.toUpperCase() }}</p>

            <div class="bg-blue-700 text-white w-full text-center py-1 mt-1 z-10">
                <p class="font-semibold text-sm">{{ memberInfo.position.toUpperCase() }}</p>
            </div>

            <p class="text-[10px] text-gray-600 mt-2 z-10">BADAC MEMBER NO. <span class="font-bold">{{
                memberInfo.memberNumber }}</span></p>

            <div class="text-center mt-auto w-full z-10">
                <p class="font-bold text-xs border-t-2 border-black pt-1">{{ memberInfo.address.toUpperCase() }}</p>
                <p class="text-[9px] text-gray-600">COMPLETE ADDRESS</p>
            </div>
        </div>

        <div class="w-[5.4cm] h-[8.6cm] bg-white rounded-xl shadow-lg p-3 flex flex-col relative overflow-hidden">
            <div class="absolute bottom-0 left-0 w-full h-1/2 bg-blue-50/70 -z-0 skew-y-12 translate-y-1/4"></div>

            <div class="bg-blue-700 text-white w-full text-center py-1">
                <p class="font-semibold text-xs">PERSONAL INFORMATION</p>
            </div>

            <div class="grid grid-cols-[auto,1fr] gap-x-2 gap-y-1 text-[10px] mt-2 w-full z-10">
                <span class="font-bold text-gray-800">DATE OF BIRTH:</span>
                <span class="border-b border-blue-500 font-semibold text-gray-900">{{ memberInfo.dateOfBirth }}</span>

                <span class="font-bold text-gray-800">SEX:</span>
                <span class="border-b border-blue-500 font-semibold text-gray-900">{{ memberInfo.sex }}</span>

                <span class="font-bold text-gray-800">CIVIL STATUS:</span>
                <span class="border-b border-blue-500 font-semibold text-gray-900">{{ memberInfo.civilStatus }}</span>

                <span class="font-bold text-gray-800">CONTACT NO.:</span>
                <span class="border-b border-blue-500 font-semibold text-gray-900">{{ memberInfo.contactNumber }}</span>
            </div>

            <div class="text-[10px] mt-4 w-full z-10">
                <p class="font-bold text-gray-800">IN CASE OF EMERGENCY, PLEASE CONTACT:</p>
                <p class="border-b border-blue-500 font-semibold text-gray-900 h-4">{{ memberInfo.emergencyContact }}
                </p>
            </div>

            <div class="bg-blue-700 text-white w-full text-center py-1 mt-4">
                <p class="font-semibold text-[9px]">IN CASE OF LOSS, PLEASE RETURN TO THIS BARANGAY</p>
            </div>

            <div class="text-center mt-4 z-10">
                <p class="font-bold text-sm">{{ idInfo.validUntil }}</p>
                <p class="text-[10px]">VALID UNTIL</p>
            </div>

            <div class="text-center mt-auto w-full z-10">
                <div class="border-t-2 border-black w-3/4 mx-auto mb-1"></div>
                <p class="font-bold text-xs">{{ idInfo.mayorName.toUpperCase() }}</p>
                <p class="text-[9px] text-gray-600">CITY / MUNICIPAL MAYOR</p>
            </div>
        </div>
    </div>
</template>
