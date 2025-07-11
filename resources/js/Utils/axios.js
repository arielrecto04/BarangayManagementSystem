import axios from "axios";

import { useAuthenticationStore } from "@/Stores";

const base = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
    },
});

if (localStorage.getItem("token")) {
    base.interceptors.request.use(
        (config) => {
            const authStore = useAuthenticationStore();
            const token = localStorage.getItem("token") || authStore.token;
            if (token) {
                config.headers.Authorization = `bearer ${token}`;
            }
            return config;
        },
        (error) => {
            return Promise.reject(error);
        }
    );
}

export default base;
