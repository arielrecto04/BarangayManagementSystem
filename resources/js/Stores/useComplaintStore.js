import { axios } from "@/utils";
import { defineStore } from "pinia";

export const useComplaintStore = defineStore("complaint", {
    state: () => ({
        _complaints: [],
        _isLoading: false,
        _complaint: null,
    }),
    getters: {
        complaints: (state) => state._complaints,
        isLoading: (state) => state._isLoading,
        complaint: (state) => state._complaint,
    },
    actions: {
        async getcomplaints() {
            try {
                this._isLoading = true;
                const response = await axios.get('/complaints');
                this._complaints = response.data.data;
            } catch (error) {
                console.error("Error fetching complaints:", error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },
        async deletecomplaint(complaintId) {
            try {
                this._isLoading = true;
                await axios.delete(`/complaints/${complaintId}`);
                this._complaints = this._complaints.filter((complaint) => complaint.id !== complaintId);
            } catch (error) {
                console.error('Error deleting complaint:', error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        async addComplaint(complaintData) {
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

                const index = this._complaints.findIndex((c) => c.id === id);
                if (index !== -1) {
                    this._complaints[index] = response.data.data;
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
