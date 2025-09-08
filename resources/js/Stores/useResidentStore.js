import { axios } from "@/Utils";
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
            const id = Number(residentId);
            this._resident =
                this._residents.find(
                    (resident) => Number(resident.id) === id
                ) || null;
        },

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

                this._residents = residentData.map((r) => ({
                    ...r,
                    id: Number(r.id),
                    avatar:
                        r.avatar ||
                        "https://ionicframework.com/docs/img/demos/avatar.svg",
                }));

                const paginationData = response.data;
                this._paginate = {
                    total: paginationData.total ?? 0,
                    per_page: paginationData.per_page ?? 10,
                    current_page: paginationData.current_page ?? page,
                    last_page: paginationData.last_page ?? 1,
                    from: paginationData.from ?? 0,
                    to: paginationData.to ?? 0,
                };

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
                const responseData = response.data.data || response.data;

                const newResident = {
                    ...responseData,
                    id: Number(responseData.id),
                    avatar:
                        responseData.avatar ||
                        "https://ionicframework.com/docs/img/demos/avatar.svg",
                };

                if (this._residents.length < this._paginate.per_page) {
                    this._residents.push(newResident);
                }

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

        // Update resident
        async updateResident(residentData) {
            if (!residentData?.id) {
                console.error("No resident data or ID provided for update.");
                return;
            }

            this._isLoading = true;
            this._error = null;

            try {
                const response = await axios.put(
                    `/residents/${residentData.id}`,
                    residentData
                );

                const updatedResident = {
                    ...(response.data.data || response.data),
                    id: Number((response.data.data || response.data).id),
                    avatar:
                        (response.data.data || response.data).avatar ||
                        "https://ionicframework.com/docs/img/demos/avatar.svg",
                };

                this._residents = this._residents.map((resident) =>
                    Number(resident.id) === Number(updatedResident.id)
                        ? updatedResident
                        : resident
                );

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
                    avatar:
                        residentData.avatar ||
                        "https://ionicframework.com/docs/img/demos/avatar.svg",
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

                const id = Number(residentId);
                this._residents = this._residents.filter(
                    (resident) => Number(resident.id) !== id
                );

                this._paginate.total = Math.max(0, this._paginate.total - 1);

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

        // Get resident by number
        async getResidentByNumber(residentNumber) {
            try {
                const response = await axios.get(
                    `/residents/get-resident-by-number/${residentNumber}`
                );

                this._resident = {
                    ...response.data,
                    id: Number(response.data.id),
                    avatar:
                        response.data.avatar ||
                        "https://ionicframework.com/docs/img/demos/avatar.svg",
                };
            } catch (error) {
                console.log(error);

                if (error.response?.status === 404) {
                    showToast({
                        icon: "error",
                        title: "Resident not found",
                    });
                }
            }
        },

        clearState() {
            this._resident = null;
            this._error = null;
        },
    },
});
