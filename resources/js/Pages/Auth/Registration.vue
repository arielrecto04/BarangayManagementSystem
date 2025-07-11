<script setup>
import { BaseLayout } from "@/Layouts";
import { useAuthenticationStore } from "@/Stores";
import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import useToast from '@/Utils/useToast';
const { showToast } = useToast();

const auth = useAuthenticationStore();
const { isLoading, error } = storeToRefs(auth);

const registerForm = ref();

const register = async () => {
    try {
        const formData = new FormData(registerForm.value);
        await auth.register(formData);
        if (!error.value) {
            showToast({ icon: 'success', title: 'Registration successful' });
        }
    } catch (error) {
        showToast({ icon: 'error', title: error.message });
    }
};
</script>

<template>
    <BaseLayout>
        <form ref="registerForm" @submit.prevent="register" class="flex flex-col gap-4 w-[400px] p-5 bg-white shadow-lg rounded-3xl mx-auto">
            <h1 class="text-2xl font-bold text-green-800">Register</h1>
            <label for="name">Name</label>
            <input type="text" placeholder="Name" name="name" class="p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-800 border-green-800" />
            <template v-if="error?.errors?.name">
                <p class="text-red-500">{{ error?.errors?.name[0] }}</p>
            </template>
            <label for="email">Email</label>
            <input type="email" placeholder="Email" name="email" class="p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-800 border-green-800" />
            <template v-if="error?.errors?.email">
                <p class="text-red-500">{{ error?.errors?.email[0] }}</p>
            </template>
            <label for="password">Password</label>
            <input type="password" placeholder="Password" name="password" class="p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-800 border-green-800" />
            <template v-if="error?.errors?.password">
                <p class="text-red-500">{{ error?.errors?.password[0] }}</p>
            </template>
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" placeholder="Confirm Password" name="password_confirmation" class="p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-800 border-green-800" />
            <template v-if="error?.errors?.password_confirmation">
                <p class="text-red-500">{{ error?.errors?.password_confirmation[0] }}</p>
            </template>
            <button type="submit" :disabled="isLoading" class="bg-green-800 text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-green-800 border-green-800">
                {{ isLoading ? 'Loading...' : 'Register' }}
            </button>
        </form>
    </BaseLayout>
</template>