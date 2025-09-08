import { axios } from "@/Utils";
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
                this._paginate = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    per_page: response.data.per_page,
                    total: response.data.total,
                };
            } catch (error) {
                console.error("Error fetching complaints:", error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        async fetchComplaint(id) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.get(`/complaints/${id}`);
                this._complaint = response.data;
            } catch (error) {
                console.error(`Error fetching complaint ID ${id}:`, error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        async addComplaint(data) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.post("/complaints", data);
                this._complaints.push(response.data.data);
            } catch (error) {
                console.error("Error adding complaint:", error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        async updateComplaint(id, data) {
            this._isLoading = true;
            this._error = null;
            try {
                let response;

                // Check if data is FormData (for file uploads)
                if (data instanceof FormData) {
                    response = await axios.post(`/complaints/${id}`, data, {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    });
                } else {
                    // Regular JSON update
                    response = await axios.patch(`/complaints/${id}`, data);
                }

                // FIXED: Update the local complaints array with fresh data
                const index = this._complaints.findIndex(
                    (c) => Number(c.id) === Number(id)
                );
                if (index !== -1) {
                    this._complaints[index] = response.data.data;
                    console.log(
                        "Updated complaint in store:",
                        response.data.data
                    );
                }

                // FIXED: Also update the single complaint if it's the one being viewed
                if (
                    this._complaint &&
                    Number(this._complaint.id) === Number(id)
                ) {
                    this._complaint = response.data.data;
                }

                return response.data;
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
