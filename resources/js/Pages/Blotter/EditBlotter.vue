<script setup>
import { useBlotterStore, useResidentStore } from '@/Stores'
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';
import { storeToRefs } from 'pinia';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const router = useRouter();
const { showToast } = useToast();

const blotterStore = useBlotterStore();
const residentStore = useResidentStore();
const residents = ref([]);
const selectedComplainant = ref(null);
const selectedRespondent = ref(null);

const { blotter, isLoading } = storeToRefs(blotterStore);
const blotterId = router.currentRoute.value.params.id;

const formatDateForInput = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toISOString().slice(0, 16);
};
// Add this cleanup function
const cleanup = () => {
    selectedComplainant.value = null;
    selectedRespondent.value = null;
    blotterStore._blotter = null;

};

const updateBlotterData = async () => {
    try {
        if (!blotter.value) return;
        await blotterStore.updateBlotter(blotterId, blotter.value);
        showToast({ icon: 'success', title: 'Blotter updated successfully' });
        cleanup();
        router.push('/blotter'); // Changed to parent route
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}

// Add beforeUnmount hook
onBeforeUnmount(cleanup);

onMounted(async () => {
    await residentStore.getResidents();
    residents.value = residentStore.residents;
    await blotterStore.getBlotterById(blotterId);
    // Format dates after loading
     if (blotter.value) {
        blotter.value.filing_date = blotter.value.filing_date?.split('T')[0] || '';
        blotter.value.datetime_of_incident = formatDateForInput(blotter.value.datetime_of_incident);
    }
});

watch(() => blotter.value, (newBlotter) => {
    if (newBlotter) {
        selectedComplainant.value = residents.value.find(r => r.id == newBlotter.complainants_id);
        selectedRespondent.value = residents.value.find(r => r.id == newBlotter.respondents_id);

        newBlotter.filing_date = newBlotter.filing_date?.split('T')[0] || '';
        newBlotter.datetime_of_incident = formatDateForInput(newBlotter.datetime_of_incident);
    }
}, { immediate: true });
const handleCancel = () => {
  // Clean up Multiselect references
  selectedComplainant.value = null;
  selectedRespondent.value = null;
  
  // Reset the current blotter in store
  blotterStore._blotter = null;
  
  // Navigate back to parent blotter route
  router.push('/blotter');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <template v-if="isLoading || !blotter">
            <div class="flex justify-center items-center">
                <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
            </div>
        </template>
        <template v-else>
            <form @submit.prevent="updateBlotterData">
                <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
                    <h1 class="text-2xl font-bold mb-6">Edit Blotter</h1>
                    <h2 class="text-lg font-semibold mb-4">Blotter Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="blotter_no" class="text-sm font-semibold text-gray-600">Blotter No</label>
                            <input id="blotter_no" type="text" v-model="blotter.blotter_no" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="filing_date" class="text-sm font-semibold text-gray-600">Filing Date</label>
                            <input id="filing_date" type="date" v-model="blotter.filing_date" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="title_case" class="text-sm font-semibold text-gray-600">Title Case</label>
                            <input id="title_case" type="text" v-model="blotter.title_case" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                        <label for="nature_of_case" class="text-sm font-semibold text-gray-600">Nature of Case</label>
                            <select 
                                id="nature_of_case" 
                                v-model="blotter.nature_of_case" 
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2"
                            >
                                <option value="" disabled>Select Nature of Case</option>
                                <option value="Civil case">Civil Case</option>
                                <option value="Criminal case">Criminal Case</option>
                            </select>
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
                                @input="val => blotter.complainants_id = val?.id"
                            />
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
                                @input="val => blotter.respondents_id = val?.id"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="place" class="text-sm font-semibold text-gray-600">Place</label>
                            <input id="place" type="text" v-model="blotter.place" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="datetime_of_incident" class="text-sm font-semibold text-gray-600">Date/Time of Incident</label>
                            <input id="datetime_of_incident" type="datetime-local" v-model="blotter.datetime_of_incident" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="blotter_type" class="text-sm font-semibold text-gray-600">Blotter Type</label>
                            <input id="blotter_type" type="text" v-model="blotter.blotter_type" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="barangay_case_no" class="text-sm font-semibold text-gray-600">Barangay Case No</label>
                            <input id="barangay_case_no" type="text" v-model="blotter.barangay_case_no" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="col-span-2 flex flex-col gap-2">
                            <label for="description" class="text-sm font-semibold text-gray-600">Description</label>
                            <textarea id="description" v-model="blotter.description" class="input-style col-span-2 border border-gray-200 rounded-md px-4 py-2" rows="4"></textarea>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="status" class="text-sm font-semibold text-gray-600">Status</label>
                            <select id="status" v-model="blotter.status" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-center mt-10 gap-4">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md">Save</button>
                        <button 
                             @click="handleCancel"
                            class="bg-white px-6 py-2 rounded-xl shadow-xl ml-4 font-bold hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </template>
    </div>
</template>

<style>
.multiselect {
  min-height: auto;
}

.multiselect__tags {
  min-height: 44px;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  padding: 0.5rem;
}

.multiselect__input,
.multiselect__single {
  font-size: 1rem;
  margin-bottom: 0;
  padding: 0;
}

.multiselect__placeholder {
  margin-bottom: 0;
  padding-top: 0;
  padding-left: 0;
}

.multiselect__option--highlight {
  background: #3b82f6;
}

.multiselect__option--highlight::after {
  background: #3b82f6;
}
</style>
