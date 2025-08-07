import { axios } from "@/utils";
import { defineStore } from "pinia";

export const useComplaintStore = defineStore("complaint", {
    state: () => ({
        _complaints: [],
        _complaint: null,
        _paginate: null,
        _isLoading: false,
        _error: null,
    }),

    getters: {
        complaints: (state) => state._complaints,
        complaint: (state) => state._complaint,
        paginate: (state) => state._paginate,
        isLoading: (state) => state._isLoading,
        error: (state) => state._error,
    },

    actions: {
        async getComplaints(page = 1) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.get(`/complaints?page=${page}`);
                this._complaints = response.data.data;
            } catch (error) {
                console.error(`Error updating complaint ID ${id}:`, error);
                this._error = error;
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async createComplaint(formData) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.post("/complaints", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });
                this._complaints.push(response.data.data);
            } catch (error) {
                console.error("Error creating complaint:", error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        async deleteComplaint(id) {
            this._isLoading = true;
            this._error = null;
            try {
                await axios.delete(`/complaints/${id}`);
                this._complaints = this._complaints.filter((c) => c.id !== id);
            } catch (error) {
                console.error(`Error deleting complaint ID ${id}:`, error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },
    },
});
