import { axios } from "@/utils";
import { defineStore } from "pinia";
import useToast from "@/Utils/useToast";
const { showToast } = useToast();

export const useResidentStore = defineStore("resident", {
    state: () => ({
        _residents: [],
        _isLoading: false,
        _resident: null,
        _paginate: {
            total: 0,
            per_page: 0,
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
        },
        _error: null,
    }),

    getters: {
        residents: (state) => state._residents,
        isLoading: (state) => state._isLoading,
        resident: (state) => state._resident,
        paginate: (state) => state._paginate,
        error: (state) => state._error,
    },

    actions: {
        removeResident() {
            this._resident = null;
        },

        selectResidentById(residentId) {
            // Fix: Use strict equality and proper type conversion
            const id = Number(residentId);
            this._resident =
                this._residents.find(
                    (resident) => Number(resident.id) === id
                ) || null;
        },

        // Add method to safely get resident by ID without setting store state
        getResidentByIdSync(residentId) {
            const id = Number(residentId);
            return (
                this._residents.find(
                    (resident) => Number(resident.id) === id
                ) || null
            );
        },

        // Fetch paginated list of residents
        async getResidents(page = 1) {
            this._isLoading = true;
            this._error = null;

            try {
                const response = await axios.get(`/residents?page=${page}`);
                const residentData = response.data.data || [];

                // Ensure resident IDs are numbers
                this._residents = residentData.map((r) => ({
                    ...r,
                    id: Number(r.id),
                }));

                // More robust pagination handling
                const paginationData = response.data;
                this._paginate = {
                    total: paginationData.total ?? 0,
                    per_page: paginationData.per_page ?? 10,
                    current_page: paginationData.current_page ?? page,
                    last_page: paginationData.last_page ?? 1,
                    from: paginationData.from ?? 0,
                    to: paginationData.to ?? 0,
                };

                // Ensure current_page doesn't exceed last_page
                if (
                    this._paginate.current_page > this._paginate.last_page &&
                    this._paginate.last_page > 0
                ) {
                    this._paginate.current_page = this._paginate.last_page;
                }
            } catch (error) {
                console.error("Error fetching residents:", error);
                this._error =
                    error.response?.data?.message ||
                    "Failed to fetch residents";
                this._residents = [];
            } finally {
                this._isLoading = false;
            }
        },

        // Add a new resident
        async addResident(resident) {
            this._isLoading = true;
            this._error = null;

            try {
                const response = await axios.post("/residents", resident);
                const newResident = {
                    ...response.data,
                    id: Number(response.data.id),
                };

                // If we're not on the last page, we might need to refetch
                // or just add to current list if there's space
                if (this._residents.length < this._paginate.per_page) {
                    this._residents.push(newResident);
                }

                // Update pagination totals
                this._paginate.total += 1;

                return newResident;
            } catch (error) {
                console.error("Error adding resident:", error);
                this._error =
                    error.response?.data?.message || "Failed to add resident";
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        // Update current resident
        async updateResident() {
            if (!this._resident?.id) {
                console.error("No resident selected for update.");
                return;
            }

            this._isLoading = true;
            this._error = null;

            try {
                const response = await axios.put(
                    `/residents/${this._resident.id}`,
                    this._resident
                );

                // Update in the residents list
                const updatedResident = {
                    ...(response.data.data || response.data),
                    id: Number((response.data.data || response.data).id),
                };

                this._residents = this._residents.map((resident) =>
                    Number(resident.id) === Number(updatedResident.id)
                        ? updatedResident
                        : resident
                );

                // Update the selected resident
                this._resident = updatedResident;

                return updatedResident;
            } catch (error) {
                console.error("Error updating resident:", error);
                this._error =
                    error.response?.data?.message ||
                    "Failed to update resident";
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        // Get a resident by ID from API
        async getResidentById(residentId) {
            this._isLoading = true;
            this._error = null;

            try {
                const response = await axios.get(`/residents/${residentId}`);
                const residentData = response.data.data || response.data;
                this._resident = {
                    ...residentData,
                    id: Number(residentData.id),
                };
                return this._resident;
            } catch (error) {
                console.error("Error fetching resident:", error);
                this._error =
                    error.response?.data?.message || "Failed to fetch resident";
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        // Delete a resident by ID
        async deleteResident(residentId) {
            this._isLoading = true;
            this._error = null;

            try {
                await axios.delete(`/residents/${residentId}`);

                // Remove from local list using strict equality
                const id = Number(residentId);
                this._residents = this._residents.filter(
                    (resident) => Number(resident.id) !== id
                );

                // Update pagination totals
                this._paginate.total = Math.max(0, this._paginate.total - 1);

                // Clear selected resident if it was deleted
                if (this._resident && Number(this._resident.id) === id) {
                    this._resident = null;
                }

                return true;
            } catch (error) {
                console.error("Error deleting resident:", error);
                this._error =
                    error.response?.data?.message ||
                    "Failed to delete resident";
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async getResidentByNumber(residentNumber) {
            try {
                const response = await axios.get(
                    `/residents/get-resident-by-number/${residentNumber}`
                );
                console.log(response.data);
                this._resident = {
                    ...response.data,
                    id: Number(response.data.id),
                };
            } catch (error) {
                console.log(error);

                if (error.response) {
                    if (error.response.status === 404) {
                        showToast({
                            icon: "error",
                            title: "Resident not found",
                        });
                    }
                }
            }
        },

        // Add method to clear store state safely
        clearState() {
            this._resident = null;
            this._error = null;
            // Don't clear residents array as it might be needed by other components
        },
    },
});
