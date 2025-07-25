import axios from "axios";

const instance = axios.create({
    baseURL: 'http://127.0.0.1:8000/api', // Updated to match your backend URL
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});


const base = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
    },
});

if (localStorage.getItem("token") ) {

    base.interceptors.request.use(
        (config) => {
            const token = localStorage.getItem("token");
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
