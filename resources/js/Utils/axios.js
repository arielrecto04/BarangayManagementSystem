import axios from "axios";

import { useAuthenticationStore } from "@/Stores";


const base = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
    },
});



base.interceptors.request.use(
    (config) => {

        const { token } = useAuthenticationStore();

        console.log(token);

        if (token) {
            config.headers.Authorization = `bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);


base.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response.status === 401) {
        const authStore = useAuthenticationStore();
        authStore.logout();
    }
    return Promise.reject(error);
});


export default base;
