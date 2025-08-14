import { defineStore } from "pinia";
import { axios } from "@/Utils";
import { useRouter } from "vue-router";

export const useAuthenticationStore = defineStore("authentication", {
    state: () => ({
        _isAuthenticated: localStorage.getItem("token") !== null ? true : false,
        _user: JSON.parse(localStorage.getItem("user")) || null,
        _token: localStorage.getItem("token") || null,
        _isLoading: false,
        _error: null,
        router: useRouter(),
    }),
    getters: {
        user: (state) => state._user,
        token: (state) => state._token,
        isAuthenticated: (state) => state._isAuthenticated,
        isLoading: (state) => state._isLoading,
        error: (state) => state._error,
    },
    actions: {
        async login(credentials) {

            try {
                this._isLoading = true;
                const response = await axios.post("/login", credentials);
                this._token = response.data?.token;
                this._user = response.data?.user;
                localStorage.setItem("token", response.data?.token);
                localStorage.setItem("user", JSON.stringify(response.data?.user));
                this._isAuthenticated = true;
                console.log(this._user);

            } catch (error) {

                console.log(error);
                this._error = error?.response?.data;
            } finally {
                this._isLoading = false;
            }
        },
        async logout() {
            try {
                this._isLoading = true;
                 const response = await axios.post("/logout");
                this._isAuthenticated = false;
                this._token = null;
                this._user = null;
                localStorage.removeItem("token");
                localStorage.removeItem("user");
                this.router.push({ name: "Login" });
            } catch (error) {
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },
    },
});
