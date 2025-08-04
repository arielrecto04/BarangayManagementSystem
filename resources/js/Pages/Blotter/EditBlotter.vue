<script setup>
import { useBlotterStore, useResidentStore } from '@/Stores'
import { ref, onMounted, watch } from 'vue'
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

const { resident, isLoading } = storeToRefs(residentStore);

const residentId = router.currentRoute.value.params.id;



const updateResidentData  = async () => {
    try {
        await residentStore.updateResident();
        showToast({ icon: 'success', title: 'Resident updated successfully' });
        router.push('/residents');
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}


onMounted(async () => {
    await residentStore.getResidents();
    residents.value = residentStore.residents;
    await blotterStore.getBlotterById(blotterId);
});

watch(() => blotter.value, (newBlotter) => {
    if (newBlotter) {
        selectedComplainant.value = residents.value.find(r => r.id == newBlotter.complainants_id);
        selectedRespondent.value = residents.value.find(r => r.id == newBlotter.respondents_id);
    }
}, { immediate: true });


</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
        <template v-if="isLoading || !resident">
            <div class="flex justify-center items-center">
                <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
            </div>
        </template>
        <template v-else>
            <form @submit.prevent="updateResidentData">
                <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
                    <h1 class="text-2xl font-bold mb-6">Edit Resident</h1>
                    <h2 class="text-lg font-semibold mb-4">Resident Profile</h2>

                    <div class="grid grid-cols-3 gap-4">
                        <!-- First Row -->

                        <div class="flex flex-col gap-2">
                            <label for="first_name" class="text-sm font-semibold text-gray-600">First Name</label>
                            <input type="text" placeholder="First Name" v-model="resident.first_name"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />

                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="last_name" class="text-sm font-semibold text-gray-600">Last Name</label>
                            <input type="text" placeholder="Last Name" v-model="resident.last_name"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="row-span-3 flex justify-center items-center">
                            <div class="w-50 h-50 bg-gray-200 rounded-md"></div>
                        </div>

                        <!-- Second Row -->
                        <div class="flex flex-col gap-2">
                            <label for="birthday" class="text-sm font-semibold text-gray-600">Birthday</label>
                            <input type="date" placeholder="Birthday" v-model="resident.birthday"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="age" class="text-sm font-semibold text-gray-600">Age</label>
                            <input type="number" placeholder="Age" v-model="resident.age"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>

                        <div class="flex flex-col gap-2">
                        <label for="nature_of_case" class="text-sm font-semibold text-gray-600">Nature of Case</label>
                            <select
                                id="nature_of_case"
                                v-model="blotter.nature_of_case"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2"
                            >
                                <option value="" disabled selected>Select Nature of Case</option>
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

                        <!-- Third Row -->
                        <div class="flex flex-col gap-2">
                            <label for="address" class="text-sm font-semibold text-gray-600">Address</label>
                            <input type="text" placeholder="Lot no. / Street / Subdivision / Barangay"
                                v-model="resident.address"
                                class="input-style col-span-2 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <!-- <div class="flex flex-col gap-2">
                        <label for="contact_number" class="text-sm font-semibold text-gray-600">Image</label>
                        <button class="input-style bg-white  col-span-1 border border-gray-200 rounded-md px-4 py-2">Add
                        Image</button>
                    </div> -->

                        <!-- Fourth Row -->
                        <div class="flex flex-col gap-2">
                            <label for="contact_number" class="text-sm font-semibold text-gray-600">Contact No.</label>
                            <input type="text" placeholder="Contact No." v-model="resident.contact_number"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="family_member" class="text-sm font-semibold text-gray-600">Family Member</label>
                            <input type="text" placeholder="Family Member" v-model="resident.family_member"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="emergency_contact" class="text-sm font-semibold text-gray-600">Emergency
                                Contact</label>
                            <input type="text" placeholder="Emergency Contact" v-model="resident.emergency_contact"
                                class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
                        </div>
                    </div>


                    <div class="flex justify-center mt-10 gap-4">

                        <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md">Save</button>
                        <router-link to="/residents"
                            class="bg-white px-6 py-2 rounded-xl shadow-xl ml-4 font-bold hover:bg-gray-200">Cancel</router-link>

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
