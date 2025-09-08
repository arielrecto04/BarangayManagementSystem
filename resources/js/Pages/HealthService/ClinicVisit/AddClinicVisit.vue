<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useClinicVisitStore } from '@/Stores';
import { useResidentStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import useToast from '@/Utils/useToast';
import { ArrowLeftIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const { showToast } = useToast();
const clinicVisitStore = useClinicVisitStore();
const residentStore = useResidentStore();
const { residents } = storeToRefs(residentStore);

// Form data
const form = ref({
    resident_id: null,
    visit_date: new Date().toISOString().slice(0, 16), // Current date and time
    reason_for_visit: '',
    diagnosis: '',
    treatment_notes: '',
    prescription: ''
});

// UI state
const isSubmitting = ref(false);
const showResidentSearch = ref(false);
const residentSearchQuery = ref('');
const selectedResident = ref(null);

// Resident search functionality
const searchResidents = () => {
    if (residentSearchQuery.value.length >= 2) {
        residentStore.getResidents(1, residentSearchQuery.value);
    }
};

const selectResident = (resident) => {
    selectedResident.value = resident;
    form.value.resident_id = resident.id;
    showResidentSearch.value = false;
    residentSearchQuery.value = `${resident.last_name}, ${resident.first_name}`;
};

const clearResidentSelection = () => {
    selectedResident.value = null;
    form.value.resident_id = null;
    residentSearchQuery.value = '';
};

// Form submission
const handleSubmit = async () => {
    try {
        isSubmitting.value = true;
        await clinicVisitStore.addClinicVisit(form.value);
        showToast('Clinic visit logged successfully!', 'success');
        router.push({ name: 'Clinic Visits' });
    } catch (error) {
        showToast('Failed to log clinic visit. Please try again.', 'error');
        console.error('Error creating clinic visit:', error);
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    residentStore.getResidents();
});
</script>

<template>
    <div class="bg-white p-4 sm:p-6 shadow-lg rounded-lg">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-4 mb-6">
            <div class="flex items-center space-x-3 mb-3 sm:mb-0">
                <button @click="router.go(-1)" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <ArrowLeftIcon class="w-5 h-5" />
                </button>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Log New Clinic Visit</h2>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Resident Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Resident <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="flex">
                        <input v-model="residentSearchQuery" @input="searchResidents" @focus="showResidentSearch = true"
                            type="text" placeholder="Search resident by name..."
                            class="flex-1 p-3 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            required />
                        <button type="button" @click="searchResidents"
                            class="px-4 bg-green-700 text-white rounded-r-lg hover:bg-green-800 transition-colors">
                            <MagnifyingGlassIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div v-if="showResidentSearch && residents.length > 0"
                        class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <div v-for="resident in residents" :key="resident.id" @click="selectResident(resident)"
                            class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0">
                            <div class="font-medium">{{ resident.last_name }}, {{ resident.first_name }}</div>
                            <div class="text-sm text-gray-500">
                                Age: {{ resident.age }} | Purok: {{ resident.purok }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Resident Display -->
                <div v-if="selectedResident" class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-medium text-green-800">
                                {{ selectedResident.last_name }}, {{ selectedResident.first_name }}
                            </div>
                            <div class="text-sm text-green-600">
                                Age: {{ selectedResident.age }} | Contact: {{ selectedResident.contact_number || 'N/A'
                                }}
                            </div>
                        </div>
                        <button type="button" @click="clearResidentSelection"
                            class="text-green-600 hover:text-green-800">
                            ✕
                        </button>
                    </div>
                </div>
            </div>

            <!-- Visit Date and Time -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Visit Date & Time <span class="text-red-500">*</span>
                </label>
                <input v-model="form.visit_date" type="datetime-local"
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required />
            </div>

            <!-- Reason for Visit -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Visit <span class="text-red-500">*</span>
                </label>
                <textarea v-model="form.reason_for_visit" rows="3"
                    placeholder="e.g., Fever, Headache, Routine checkup..."
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required></textarea>
            </div>

            <!-- Diagnosis -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Diagnosis
                </label>
                <textarea v-model="form.diagnosis" rows="3" placeholder="Enter diagnosis or clinical findings..."
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
            </div>

            <!-- Treatment Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Treatment Notes
                </label>
                <textarea v-model="form.treatment_notes" rows="4"
                    placeholder="Treatment given, procedures performed, recommendations..."
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
            </div>

            <!-- Prescription -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Prescription
                </label>
                <textarea v-model="form.prescription" rows="3"
                    placeholder="Medications prescribed, dosage, instructions..."
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row sm:justify-end sm:space-x-3 space-y-3 sm:space-y-0 pt-6">
                <button type="button" @click="router.go(-1)"
                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                    Cancel
                </button>
                <button type="submit" :disabled="isSubmitting || !form.resident_id"
                    class="px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800 font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                    <div v-if="isSubmitting" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2">
                    </div>
                    {{ isSubmitting ? 'Saving...' : 'Save Clinic Visit' }}
                </button>
            </div>
        </form>
    </div>
</template>