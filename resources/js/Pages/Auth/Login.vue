<script setup>
    import { BaseLayout } from "@/Layouts";
    import { useAuthenticationStore } from "@/Stores";
    import { ref } from 'vue';
    import { storeToRefs } from 'pinia';
    import useToast from '@/Utils/useToast';
    const { showToast } = useToast();

    const auth = useAuthenticationStore();

    const { isLoading, error } = storeToRefs(auth);

    const loginForm = ref();

    const login = async () => {
        try {

            const formData = new FormData(loginForm.value);
            await auth.login(formData);
            if (!error.value) {
                showToast({ icon: 'success', title: 'Login successful' });
            }

            console.log(auth.user);
        } catch (error) {
            showToast({ icon: 'error', title: error.message });
        }
    };





</script>

<template>
    <BaseLayout>
        <form ref="loginForm" @submit.prevent="login" class="flex flex-col gap-4 w-[400px] p-5 bg-white shadow-lg rounded-3xl mx-auto">
            <h1 class="text-2xl font-bold text-green-800">Login</h1>
            <label for="username">Username</label>
            <input type="text" placeholder="Username" name="email" class="p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-800 border-green-800 " />
            <template v-if="error?.errors?.email">
                <p class="text-red-500">{{ error?.errors?.email[0] }}</p>
            </template>
            <label for="password">Password</label>
            <input type="password" placeholder="Password" name="password" class="p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-800 border-green-800" />
            <template v-if="error?.errors?.password">
                <p class="text-red-500">{{ error?.errors?.password[0] }}</p>
            </template>
            <button type="submit" :disabled="isLoading" class="bg-green-800 text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-green-800 border-green-800">
                {{ isLoading ? 'Loading...' : 'Login' }}
            </button>
        </form>
    </BaseLayout>
</template>
