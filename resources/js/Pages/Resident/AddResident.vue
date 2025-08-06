<script setup>
import { useResidentStore } from '@/Stores'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast';

const router = useRouter();
const { showToast } = useToast();

const residentStore = useResidentStore();

const residentDataForm = ref({
    first_name: '',
    middle_name: '',
    last_name: '',
    birthday: '',
    age: '',
    gender: '',
    address: '',
    contact_number: '',
    contact_person: '',
    family_member: '',
    emergency_contact: '',
    avatar: 'https://ionicframework.com/docs/img/demos/avatar.svg' // Default avatar
});

// File upload related refs
const selectedFile = ref(null);
const fileInputRef = ref(null);
const imagePreview = ref(null);

// Handle file selection
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
        selectedFile.value = file;

        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else if (file) {
        showToast({ icon: 'error', title: 'Please select a valid image file' });
        event.target.value = '';
    }
};

// Trigger file input click
const triggerFileInput = () => {
    fileInputRef.value?.click();
};

// Remove selected image
const removeImage = () => {
    selectedFile.value = null;
    imagePreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Computed property for display image
const displayImage = computed(() => {
    return imagePreview.value || residentDataForm.value.avatar;
});

// Convert file to base64 or handle file upload
const processFileUpload = async (file) => {
    if (!file) return residentDataForm.value.avatar;

    // For now, we'll convert to base64. In production, you might want to upload to a server
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            resolve(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const createResident = async () => {
    try {
        // Process file upload if there's a selected file
        if (selectedFile.value) {
            const avatarData = await processFileUpload(selectedFile.value);
            residentDataForm.value.avatar = avatarData;
        }

        console.log(residentDataForm.value);
        await residentStore.addResident(residentDataForm.value);
        showToast({ icon: 'success', title: 'Resident created successfully' });
        router.push('/residents');

    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex justify-center items-center p-4 sm:p-6 lg:p-10">
        <form @submit.prevent="createResident" class="w-full">
            <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 lg:p-10 w-full max-w-6xl mx-auto">
                <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Add New Resident</h1>
                <h2 class="text-base sm:text-lg font-semibold mb-4">Resident Profile</h2>

                <!-- Mobile-first responsive layout -->
                <div class="flex flex-col lg:grid lg:grid-cols-4 gap-6">
                    <!-- Photo Upload Section - Top on mobile, Left on desktop -->
                    <div class="lg:col-span-1 flex flex-col items-center">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 lg:w-44 lg:h-44 xl:w-52 xl:h-52 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative group"
                            @click="triggerFileInput">
                            <!-- Hidden file input -->
                            <input ref="fileInputRef" type="file" accept="image/*" class="hidden"
                                @change="handleFileSelect" />

                            <!-- Image Preview or Upload Placeholder -->
                            <div v-if="imagePreview || residentDataForm.avatar !== 'https://ionicframework.com/docs/img/demos/avatar.svg'"
                                class="w-full h-full relative">
                                <img :src="displayImage" alt="Preview" class="w-full h-full object-cover" />
                                <!-- Overlay on hover -->
                                <div
                                    class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <div class="text-white text-center">
                                        <div class="text-xl sm:text-2xl mb-1 sm:mb-2">📸</div>
                                        <p class="text-xs sm:text-sm">Change Photo</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Placeholder -->
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-3xl sm:text-4xl text-gray-400 mb-2">📸</div>
                                    <p class="text-xs sm:text-sm text-gray-500">Upload Photo</p>
                                </div>
                            </div>
                        </div>

                        <!-- Remove button -->
                        <button v-if="selectedFile || imagePreview" type="button" @click="removeImage"
                            class="mt-2 text-red-500 text-sm hover:text-red-700 transition-colors">
                            Remove Photo
                        </button>
                    </div>

                    <!-- Form Fields - Bottom on mobile, Right on desktop -->
                    <div class="lg:col-span-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- First Row -->
                            <!-- First Name -->
                            <div class="flex flex-col gap-2">
                                <label for="first_name" class="text-sm font-semibold text-gray-600">First Name</label>
                                <input type="text" placeholder="First Name" v-model="residentDataForm.first_name"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>
                            <!-- Middle Name -->
                            <div class="flex flex-col gap-2">
                                <label for="middle_name" class="text-sm font-semibold text-gray-600">Middle Name</label>
                                <input type="text" placeholder="Middle Name" v-model="residentDataForm.middle_name"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>
                            <!-- Last Name -->
                            <div class="flex flex-col gap-2">
                                <label for="last_name" class="text-sm font-semibold text-gray-600">Last Name</label>
                                <input type="text" placeholder="Last Name" v-model="residentDataForm.last_name"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>

                            <!-- Second Row -->
                            <!-- Birthday -->
                            <div class="flex flex-col gap-2">
                                <label for="birthday" class="text-sm font-semibold text-gray-600">Birthday</label>
                                <input type="date" placeholder="Birthday" v-model="residentDataForm.birthday"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>
                            <!-- Age -->
                            <div class="flex flex-col gap-2">
                                <label for="age" class="text-sm font-semibold text-gray-600">Age</label>
                                <input type="number" placeholder="Age" v-model="residentDataForm.age"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>
                            <!-- Gender -->
                            <div class="flex flex-col gap-2">
                                <label for="gender" class="text-sm font-semibold text-gray-600">Gender</label>
                                <select name="" id="" v-model="residentDataForm.gender"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2">
                                    <option value="" selected disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>

                            <!-- Third Row -->
                            <!-- Address -->
                            <div class="flex flex-col gap-2 col-span-3">
                                <label for="address" class="text-sm font-semibold text-gray-600">Address</label>
                                <input type="text" placeholder="Lot no. / Street / Subdivision / Barangay"
                                    v-model="residentDataForm.address"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>

                            <!-- Fourth Row -->
                            <!-- Contact Number -->
                            <div class="flex flex-col gap-2">
                                <label for="contact_number" class="text-sm font-semibold text-gray-600">Contact
                                    No.</label>
                                <input type="text" placeholder="Contact No." v-model="residentDataForm.contact_number"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>
                            <!-- Family Member -->
                            <div class="flex flex-col gap-2">
                                <label for="family_member" class="text-sm font-semibold text-gray-600">Family
                                    Member</label>
                                <input type="text" placeholder="Family Member" v-model="residentDataForm.family_member"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>
                            <!-- Emergency Contact -->
                            <div class="flex flex-col gap-2">
                                <label for="emergency_contact" class="text-sm font-semibold text-gray-600">Emergency
                                    Contact</label>
                                <input type="text" placeholder="Emergency Contact"
                                    v-model="residentDataForm.emergency_contact"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>

                            <!-- Fifth Row -->
                            <!-- Contact Person -->
                            <div class="flex flex-col gap-2 col-span-3">
                                <label for="contact_person" class="text-sm font-semibold text-gray-600">Contact
                                    Person</label>
                                <input type="text" placeholder="Contact Person"
                                    v-model="residentDataForm.contact_person"
                                    class="input-style border border-gray-200 rounded-md px-4 py-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-center mt-8 sm:mt-10 gap-4">
                        <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md w-full sm:w-auto">Save</button>
                        <router-link to="/residents"
                            class="bg-white px-6 py-2 rounded-xl shadow-xl font-bold hover:bg-gray-200 flex justify-center items-center w-full sm:w-auto">Cancel</router-link>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>