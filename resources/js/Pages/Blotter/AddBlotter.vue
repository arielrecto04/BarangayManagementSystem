<script setup>
import Multiselect from 'vue-multiselect';
import { useBlotterStore, useResidentStore } from '@/Stores'
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const router = useRouter();
const { showToast } = useToast();
const blotterStore = useBlotterStore();
const residentStore = useResidentStore();
const residents = ref([]);
const errors = ref({});
const selectedComplainant = ref(null);
const selectedRespondent = ref(null);

onMounted(async () => {
    await residentStore.getResidents();
    residents.value = residentStore.residents;
});

watch(selectedComplainant, (val) => {
    blotterDataForm.value.complainants_id = val?.id ?? '';
});

watch(selectedRespondent, (val) => {
    blotterDataForm.value.respondents_id = val?.id ?? '';
});




const blotterDataForm = ref({
    blotter_no: '',
    filing_date: '',
    title_case: '',
    nature_of_case: '',
    complainants_type: '',
    complainants_id: '',
    respondents_type: '',
    respondents_id: '',
    place: '',
    datetime_of_incident: '',
    blotter_type: '',
    barangay_case_no: '',
    total_cases: '0',
    open_cases: '0',
    in_progress: '0',
    resolved: '0',
    status: '',
    description: '',
    witness: '',
    supporting_documents: []
});
const handleFileUpload = (event) => {
  const newFiles = Array.from(event.target.files);
  const existingNames = new Set(
    blotterDataForm.value.supporting_documents.map(file => file.name)
  );
  const uniqueNewFiles = newFiles.filter(file => !existingNames.has(file.name));

  blotterDataForm.value.supporting_documents = [
    ...blotterDataForm.value.supporting_documents,
    ...uniqueNewFiles
  ];
  event.target.value = '';
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    const fields = {
        blotter_no: 'Blotter No',
        filing_date: 'Filing Date',
        title_case: 'Title Case',
        nature_of_case: 'Nature of Case',
        complainants_id: 'Complainant ID',
        respondents_id: 'Respondent ID',
        place: 'Place',
        datetime_of_incident: 'Date/Time of Incident',
        blotter_type: 'Blotter Type',
        barangay_case_no: 'Barangay Case No',
        status: 'Status',
        description: 'Description',

    };

    for (const [field, label] of Object.entries(fields)) {
        if (!blotterDataForm.value[field]) {
            errors.value[field] = `${label} is required`;
            isValid = false;
        }
    }

    return isValid;
};

const createBlotter = async () => {
    try {
        if (!validateForm()) {
            showToast({ icon: 'error', title: 'Please fill in all required fields' });
            return;
        }
         const formData = new FormData();

        // Append all form fields
        Object.entries(blotterDataForm.value).forEach(([key, value]) => {
            if (key === 'supporting_documents' && Array.isArray(value)) {
                // Append each file individually
                value.forEach(file => {
                    formData.append('supporting_documents[]', file);
                });
            } else {
                // Append other fields
                formData.append(key, value);
            }
        });
        await blotterStore.addBlotter(formData);
        showToast({ icon: 'success', title: 'Blotter created successfully' });
        router.push('/blotter/list-blotter');
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <form @submit.prevent="createBlotter">
            <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
                <h1 class="text-2xl font-bold mb-6">Add New Blotter</h1>
                <h2 class="text-lg font-semibold mb-4">Blotter Profile</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="blotter_no" class="text-sm font-semibold text-gray-600">Blotter No</label>
                        <input id="blotter_no" type="text" placeholder="Enter Blotter No" v-model="blotterDataForm.blotter_no" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="filing_date" class="text-sm font-semibold text-gray-600">Filing Date</label>
                        <input id="filing_date" type="date" v-model="blotterDataForm.filing_date" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="title_case" class="text-sm font-semibold text-gray-600">Title Case</label>
                        <input id="title_case" type="text"
                        placeholder="Enter Title Case" v-model="blotterDataForm.title_case"
                        :class="['input-style col-span-1 border border-gray-200 rounded-md px-4 py-2', errors.title_case ? 'border-red-500' : 'border-gray-200']" />
                        <span v-if="errors.title_case" class="text-red-500 text-xs mt-1">{{ errors.title_case }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                    <label for="nature_of_case" class="text-sm font-semibold text-gray-600">Nature of Case</label>
                        <select
                        id="nature_of_case"
                        v-model="blotterDataForm.nature_of_case"
                        :class="['input-style col-span-1 border rounded-md px-4 py-2', errors.nature_of_case ? 'border-red-500' : 'border-gray-200']">
                        <span v-if="errors.nature_of_case" class="text-red-500 text-xs mt-1">{{ errors.nature_of_case }}</span
                    >
                        <option value="" disabled selected>Select Nature of Case</option>
                        <option value="civil case">Civil Case</option>
                        <option value="criminal case">Criminal Case</option>
                        </select>
                        <span v-if="errors.nature_of_case" class="text-red-500 text-xs mt-1">{{ errors.nature_of_case }}</span>
                    </div>


                    <div class="flex flex-col gap-2">
                        <label for="complainants_type" class="text-sm font-semibold text-gray-600">Complainant Type</label>
                        <input id="complainants_type" type="text" placeholder="Enter Complainant Type" v-model="blotterDataForm.complainants_type" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="complainants_id" class="text-sm font-semibold text-gray-600">Complainant</label>
                        <Multiselect
                            v-model="selectedComplainant"
                            :options="residents"
                            :custom-label="resident => `${resident.first_name} ${resident.last_name} `"
                            track-by="id"
                            placeholder="Search or select complainant"
                            :searchable="true"
                            :show-labels="false"
                            @select="val => blotterDataForm.complainants_id = val?.id"
                        />
                        <span v-if="errors.complainants_id" class="text-red-500 text-xs mt-1">{{ errors.complainants_id }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="respondents_id" class="text-sm font-semibold text-gray-600">Respondent</label>
                        <Multiselect
                            v-model="selectedRespondent"
                            :options="residents"
                            :custom-label="resident => `${resident.first_name} ${resident.last_name} `"
                            track-by="id"
                            placeholder="Search or select respondent"
                            :searchable="true"
                            :show-labels="false"
                            @select="val => blotterDataForm.respondents_id = val?.id"
                        />
                        <span v-if="errors.respondents_id" class="text-red-500 text-xs mt-1">{{ errors.respondents_id }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="place" class="text-sm font-semibold text-gray-600">Place</label>
                        <input id="place" type="text" placeholder="Enter Place" v-model="blotterDataForm.place"
                        :class="['input-style col-span-1 border border-gray-200 rounded-md px-4 py-2', errors.place ? 'border-red-500' : 'border-gray-200']" />
                        <span v-if="errors.place" class="text-red-500 text-xs mt-1">{{ errors.place }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="datetime_of_incident" class="text-sm font-semibold text-gray-600">Date/Time of Incident</label>
                        <input id="datetime_of_incident" type="datetime-local" v-model="blotterDataForm.datetime_of_incident"
                        :class="['input-style col-span-1 border border-gray-200 rounded-md px-4 py-2', errors.datetime_of_incident ? 'border-red-500' : 'border-gray-200']" />
                        <span v-if="errors.datetime_of_incident" class="text-red-500 text-xs mt-1">{{ errors.datetime_of_incident }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="blotter_type" class="text-sm font-semibold text-gray-600">Blotter Type</label>
                        <input id="blotter_type" type="text" placeholder="Enter Blotter Type" v-model="blotterDataForm.blotter_type"
                        :class="['input-style col-span-1 border border-gray-200 rounded-md px-4 py-2', errors.blotter_type ? 'border-red-500' : 'border-gray-200']" />
                        <span v-if="errors.blotter_type" class="text-red-500 text-xs mt-1">{{ errors.blotter_type }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="barangay_case_no" class="text-sm font-semibold text-gray-600">Barangay Case No</label>
                        <input id="barangay_case_no" type="text" placeholder="Enter Case No" v-model="blotterDataForm.barangay_case_no"
                        :class="['input-style col-span-1 border border-gray-200 rounded-md px-4 py-2', errors.barangay_case_no ? 'border-red-500' : 'border-gray-200']" />
                        <span v-if="errors.barangay_case_no" class="text-red-500 text-xs mt-1">{{ errors.barangay_case_no }}</span>
                    </div>
                    <div class="col-span-2 flex flex-col gap-2">
                        <label for="description" class="text-sm font-semibold text-gray-600">Description</label>
                            <textarea
                                id="description"
                                v-model="blotterDataForm.description"
                                placeholder="Enter Description"
                                rows="4"
                                :class="['input-style w-full border border-gray-200 rounded-md px-4 py-2', errors.description ? 'border-red-500' : 'border-gray-200']"
                            ></textarea>
                            <span v-if="errors.description" class="text-red-500 text-xs mt-1">{{ errors.description }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="witness" class="text-sm font-semibold text-gray-600">Witness</label>
                        <textarea
                            id="witness"
                            type="text"
                            placeholder="Enter Witness Name"
                            v-model="blotterDataForm.witness"
                            :class="['input-style col-span-1 border rounded-md px-4 py-2', errors.witness ? 'border-red-500' : 'border-gray-200']"
                        />
                        <span v-if="errors.witness" class="text-red-500 text-xs mt-1">{{ errors.witness }}</span>
                    </div>
                    <div class="flex flex-col gap-2 rel">
                        <label for="status" class="text-sm font-semibold text-gray-600">Status</label>
                        <select id="status" v-model="blotterDataForm.status" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2">
                            <option value="" disabled selected>Select Status</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                        <span v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-gray-600">Supporting Documents</label>
                        <input
                        ref="fileInput"
                        type="file"
                        multiple
                        class="hidden"
                        @change="handleFileUpload"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        />
                        <button
                        type="button"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md w-fit"
                        @click="$refs.fileInput.click()"
                        >
                        Upload Files
                        </button>
                        <div v-if="blotterDataForm.supporting_documents.length" class="mt-2 space-y-1 text-sm text-gray-700">
                            <div v-for="(file, index) in blotterDataForm.supporting_documents" :key="index" class="flex items-center gap-2">
                                <span>{{ file.name }}</span>
                                    <button
                                        type="button"
                                        class="text-red-500 hover:underline"
                                    @click="blotterDataForm.supporting_documents.splice(index, 1)"
                                    >
                                    ✖
                                    </button>
                                </div>
                            </div>
                    </div>


                </div>
                <div class="flex justify-center mt-10 gap-4">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md">Save</button>
                    <router-link to="/blotter" class="bg-white px-6 py-2 rounded-xl shadow-xl ml-4 font-bold hover:bg-gray-200">Cancel</router-link>
                </div>
            </div>
        </form>
    </div>
</template>
