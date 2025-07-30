<script setup>
import { useBlotterStore } from '@/Stores'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';

const router = useRouter();
const { showToast } = useToast();

const blotterStore = useBlotterStore();

const blotterDataForm = ref({
    blotter_no: '',
    filing_date: '',
    title_case: '',
    nature_of_case: '',
    complainant_type: '',
    complainant_id: '',
    respondent_type: '',
    respondent_id: '',
    place: '',
    datetime_of_incident: '',
    blotter_type: '',
    barangay_case_no: '',
    status: ''
});

const createBlotter = async () => {
    try {
        if (!blotterDataForm.value.status) {
            showToast({ icon: 'error', title: 'Please select a status' });
            return;
        }
        await blotterStore.addBlotter(blotterDataForm.value);
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
                        <input type="text" placeholder="Enter Blotter No" v-model="blotterDataForm.blotter_no" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="filing_date" class="text-sm font-semibold text-gray-600">Filing Date</label>
                        <input type="date" v-model="blotterDataForm.filing_date" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="title_case" class="text-sm font-semibold text-gray-600">Title Case</label>
                        <input type="text" placeholder="Enter Title Case" v-model="blotterDataForm.title_case" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="nature_of_case" class="text-sm font-semibold text-gray-600">Nature of Case</label>
                        <input type="text" placeholder="Enter Nature of Case" v-model="blotterDataForm.nature_of_case" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="complainant_type" class="text-sm font-semibold text-gray-600">Complainant Type</label>
                        <input type="text" placeholder="Enter Complainant Type" v-model="blotterDataForm.complainant_type" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="complainant_id" class="text-sm font-semibold text-gray-600">Complainant ID</label>
                        <input type="text" placeholder="Enter Complainant ID" v-model="blotterDataForm.complainant_id" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="respondent_type" class="text-sm font-semibold text-gray-600">Respondent Type</label>
                        <input type="text" placeholder="Enter Respondent Type" v-model="blotterDataForm.respondent_type" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="respondent_id" class="text-sm font-semibold text-gray-600">Respondent ID</label>
                        <input type="text" placeholder="Enter Respondent ID" v-model="blotterDataForm.respondent_id" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="place" class="text-sm font-semibold text-gray-600">Place</label>
                        <input type="text" placeholder="Enter Place" v-model="blotterDataForm.place" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="datetime_of_incident" class="text-sm font-semibold text-gray-600">Date/Time of Incident</label>
                        <input type="datetime-local" v-model="blotterDataForm.datetime_of_incident" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="blotter_type" class="text-sm font-semibold text-gray-600">Blotter Type</label>
                        <input type="text" placeholder="Enter Blotter Type" v-model="blotterDataForm.blotter_type" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="barangay_case_no" class="text-sm font-semibold text-gray-600">Barangay Case No</label>
                        <input type="text" placeholder="Enter Case No" v-model="blotterDataForm.barangay_case_no" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="status" class="text-sm font-semibold text-gray-600">Status</label>
                        <select v-model="blotterDataForm.status" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2">
                            <option value="" disabled selected>Select Status</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
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
