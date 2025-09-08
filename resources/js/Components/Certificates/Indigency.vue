<script setup>
import { computed } from 'vue';
import { Municipal_Information, Barangay_Information } from '@/Lang/en';
const props = defineProps({
    resident: {
        type: Object,
        required: true,
        default: () => ({
            name: 'NARCISO R. MANALAC',
            address: 'Block 10 Channel Street, Addas Village 4',
            barangay: 'Mambog 4',
            municipality: 'Bacoor',
            province: 'Cavite',
            lowIncomeGroup: true,
        })
    },
    barangayInfo: {
        type: Object,
        required: true,
        default: () => ({
            name: Barangay_Information.title,
            municipality: Municipal_Information.municipality,
            province: Municipal_Information.province,
            captainName: 'Hon. Robert G. Nolasco'
        })
    },
    issueDetails: {
        type: Object,
        required: true,
        default: () => ({
            date_issued: '2023-04-20',
            day: '20',
            month: 'April',
            year: '2023',
            location: 'Office of the Barangay Captain',
            requestedFor: 'financial assistance from the Office of City Mayor Strike B. Revilla'
        })
    },
    leftSealUrl: {
        type: String,
        default: Municipal_Information.municipal_seal
    },
    rightSealUrl: {
        type: String,
        default: Barangay_Information.barangay_seal
    }
});
const fullResidentName = computed(() => props.resident.name.toUpperCase());
</script>

<template>
    <div class="certificate-container">
        <header class="certificate-header">
            <div class="seal-container">
                <img :src="leftSealUrl" alt="Municipal Seal" class="seal-image" />
            </div>
            <div class="header-content">
                <p class="republic-text">Republic of the Philippines</p>
                <p class="province-text">Province of {{ barangayInfo.province }}</p>
                <p class="city-text">City of {{ barangayInfo.municipality }}</p>
                <p class="barangay-text">BARANGAY {{ barangayInfo.name.toUpperCase() }}</p>
            </div>
            <div class="seal-container">
                <img :src="rightSealUrl" alt="Barangay Seal" class="seal-image" />
            </div>
        </header>
        <div class="office-title">
            <p>OFFICE OF THE BARANGAY CAPTAIN</p>
        </div>
        <h1 class="certificate-title">
            CERTIFICATE OF INDIGENCY
        </h1>
        <section class="certificate-content">
            <p class="content-paragraph">
                This is to certify that <strong>{{ fullResidentName }}</strong> is a Bonafide resident
                of {{ props.resident.address }}, Barangay {{ props.resident.barangay }}, City of
                {{ props.resident.municipality }}, Province of {{ props.resident.province }} and that he/she belongs to
                the
                low-income economic group.
            </p>
            <p class="content-paragraph">
                This certification is being issued upon the request of the
                above-mentioned name to ask for {{ issueDetails.requestedFor }}.
            </p>
            <p class="content-paragraph">
                Done this {{ issueDetails.day }}<sup>th</sup> day of
                {{ issueDetails.month }}, {{ issueDetails.year }}.
            </p>
        </section>
        <footer class="certificate-footer">
            <div class="signature-section">
                <p class="signature-label">Specimen Signature:</p>
                <div class="signature-line"></div>
            </div>
            <div class="captain-section">
                <p class="captain-name">{{ barangayInfo.captainName.toUpperCase() }}</p>
                <p class="captain-title">Punong Barangay</p>
            </div>
        </footer>
        <div class="note-section">
            <p class="note-label">Note:</p>
            <p class="note-text">'Not valid without official seal!'</p>
        </div>
    </div>
</template>

<style scoped>
.certificate-container {
    font-family: 'Times New Roman', serif;
    background-color: white;
    color: black;
    width: 100%;
    max-width: none;
    margin: 0 auto;
    padding: clamp(1rem, 4vw, 2rem);
    border: 1px solid transparent;
    /* Minimal border on screen */
    box-shadow: none;
    display: flex;
    flex-direction: column;
    min-height: 0;
    box-sizing: border-box;
    page-break-inside: avoid;
}

.certificate-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    text-align: center;
    margin-bottom: clamp(1.5rem, 4vw, 3rem);
}

.seal-container {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.seal-image {
    width: clamp(60px, 8vw, 120px);
    height: clamp(60px, 8vw, 120px);
    object-fit: contain;
    display: block;
    max-width: 100%;
    max-height: 100%;
}

.header-content {
    flex-grow: 1;
    padding: 0 clamp(0.5rem, 2vw, 1rem);
    line-height: 1.3;
    font-size: clamp(0.875rem, 2.5vw, 1.125rem);
}

.republic-text,
.province-text,
.city-text {
    margin: 0;
    font-weight: normal;
}

.barangay-text {
    font-weight: bold;
    margin-top: clamp(0.25rem, 1vw, 0.5rem);
}

.office-title {
    text-align: center;
    font-weight: bold;
    margin-bottom: clamp(1rem, 3vw, 2rem);
    font-size: clamp(0.875rem, 2.5vw, 1.125rem);
}

.certificate-title {
    text-align: center;
    font-weight: bold;
    font-size: clamp(1.25rem, 5vw, 2rem);
    margin-bottom: clamp(2rem, 6vw, 4rem);
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #b91c1c;
}

.certificate-content {
    flex-grow: 1;
    text-align: justify;
    line-height: clamp(1.4, 1.5vw, 1.6);
    font-size: clamp(1rem, 3vw, 1.25rem);
}

.content-paragraph {
    margin: 0 0 clamp(1rem, 3vw, 2rem) 0;
}

.content-paragraph:last-child {
    margin-bottom: 0;
}

.certificate-footer {
    margin-top: clamp(2rem, 6vw, 4rem);
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
}

.signature-section {
    width: 45%;
}

.signature-label {
    font-size: clamp(0.875rem, 2.5vw, 1.125rem);
    margin-bottom: clamp(0.25rem, 1vw, 0.5rem);
}

.signature-line {
    margin-top: clamp(0.25rem, 1vw, 0.5rem);
    height: clamp(0.5rem, 1.5vw, 1rem);
    border-bottom: 1px solid black;
    width: 100%;
}

.captain-section {
    width: 45%;
    text-align: center;
}

.captain-name {
    font-weight: bold;
    text-transform: uppercase;
    margin-top: clamp(1.5rem, 4vw, 3rem);
    font-size: clamp(0.875rem, 2.5vw, 1.125rem);
    margin-bottom: 0;
}

.captain-title {
    display: inline-block;
    margin-top: clamp(0.25rem, 1vw, 0.5rem);
    padding-top: clamp(0.25rem, 1vw, 0.5rem);
    border-top: 1px solid black;
    font-size: clamp(0.875rem, 2.5vw, 1.125rem);
    margin-bottom: 0;
}

.note-section {
    margin-top: clamp(2rem, 6vw, 4rem);
    font-style: italic;
    font-size: clamp(0.75rem, 2vw, 1rem);
}

.note-label {
    margin: 0;
    font-weight: bold;
}

.note-text {
    margin: 0;
}

/* Print adaption for any paper size */
@media print {
    .certificate-container {
        border: none;
        box-shadow: none;
        margin: 0;
        padding: 10mm 15mm 15mm 15mm;
        width: 100%;
        max-width: 100%;
        page-break-inside: avoid;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }

    body * {
        visibility: hidden;
    }

    .certificate-container,
    .certificate-container * {
        visibility: visible;
    }

    .certificate-container {
        position: absolute;
        left: 0;
        top: 0;
    }
}

/* Compatibility */
.certificate-container * {
    box-sizing: border-box;
}

strong {
    font-weight: bold;
}

sup {
    vertical-align: super;
    font-size: smaller;
}
</style>
