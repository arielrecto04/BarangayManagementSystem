import { axios } from "@/Utils";
import { defineStore } from "pinia";

export const useBlotterStore = defineStore("blotter", {
    state: () => ({
        _blotters: [],
        _blotter: null,
        _paginate: null,
        _isLoading: false,
        _error: null,
    }),

    getters: {
        blotters: (state) => state._blotters,
        blotter: (state) => state._blotter,
        paginate: (state) => state._paginate,
        isLoading: (state) => state._isLoading,
        error: (state) => state._error,
    },

    actions: {
        async getBlotters(page = 1) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.get(`/blotters?page=${page}`);
                this._blotters = response.data.data;
                this._paginate = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    per_page: response.data.per_page,
                    total: response.data.total,
                };
                return true;
            } catch (error) {
                console.error("Error fetching blotters:", error);
                this._error = error;
                return false;
            } finally {
                this._isLoading = false;
            }
        },

        async fetchBlotter(id) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.get(`/blotters/${id}`);
                this._blotter = response.data.data;
            } catch (error) {
                console.error(`Error fetching blotter ID ${id}:`, error);
                this._error = error;
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async addBlotter(formData) {
            this._isLoading = true;
            this._error = null;
            try {
                console.log("Store: Sending blotter creation request...");

                // Log FormData contents for debugging
                console.log("FormData being sent:");
                for (let pair of formData.entries()) {
                    if (pair[1] instanceof File) {
                        console.log(
                            `${pair[0]}: File - ${pair[1].name} (${pair[1].type}, ${pair[1].size} bytes)`
                        );
                    } else {
                        console.log(`${pair[0]}: ${pair[1]}`);
                    }
                }

                const response = await axios.post("/blotters", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });

                console.log("Store: Received response:", response.data);

                if (response.data?.data) {
                    // FIXED: Add the new blotter to the beginning of the array
                    this._blotters.unshift(response.data.data);
                    console.log(
                        "Store: Added new blotter to store, total count:",
                        this._blotters.length
                    );
                    return response.data;
                } else {
                    console.error(
                        "Store: Invalid server response structure:",
                        response.data
                    );
                    throw new Error("Invalid server response structure");
                }
            } catch (error) {
                console.error("Store: Error creating blotter:", error);
                this._error = error;

                // FIXED: Enhanced error handling with better messages
                if (
                    error.response?.status === 422 &&
                    error.response?.data?.errors
                ) {
                    const errors = error.response.data.errors;
                    console.error("Validation errors:", errors);

                    const errorMessages = Object.entries(errors)
                        .map(
                            ([field, messages]) =>
                                `${field}: ${
                                    Array.isArray(messages)
                                        ? messages.join(", ")
                                        : messages
                                }`
                        )
                        .join("\n");
                    throw new Error(`Validation failed:\n${errorMessages}`);
                } else if (error.response?.data?.message) {
                    throw new Error(error.response.data.message);
                } else if (error.message) {
                    throw new Error(error.message);
                } else {
                    throw new Error(
                        "Unknown error occurred while creating blotter"
                    );
                }
            } finally {
                this._isLoading = false;
            }
        },

        async getBlotterById(id) {
            return await this.fetchBlotter(id);
        },

        async updateBlotter(id, data) {
            this._isLoading = true;
            this._error = null;
            try {
                let response;
                let url = `/blotters/${id}`;

                if (data instanceof FormData) {
                    // Handle FormData for file uploads
                    data.append("_method", "PATCH");
                    response = await axios.post(url, data, {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    });
                } else {
                    // Handle regular JSON data
                    const payload = {
                        ...data,
                        complainants_type:
                            data.complainants_type || "App\\Models\\Resident",
                        respondents_type:
                            data.respondents_type || "App\\Models\\Resident",
                        total_cases: data.total_cases || "0",
                        witness: data.witness || "",
                    };

                    response = await axios.patch(url, payload);
                }

                // FIXED: Update local state with fresh data from server
                const updatedBlotter = response.data.data;

                // Update the blotters array
                const index = this._blotters.findIndex(
                    (b) => Number(b.id) === Number(id)
                );
                if (index !== -1) {
                    this._blotters[index] = updatedBlotter;
                    console.log("Updated blotter in store:", updatedBlotter);
                }

                // Update current blotter if it's the one being edited
                if (this._blotter && Number(this._blotter.id) === Number(id)) {
                    this._blotter = updatedBlotter;
                    console.log(
                        "Updated current blotter in store:",
                        updatedBlotter
                    );
                }

                return response.data;
            } catch (error) {
                console.error(`Error updating blotter ID ${id}:`, error);

                // Enhanced error handling
                let errorMessage = "Failed to update blotter";
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    errorMessage = Object.entries(errors)
                        .map(
                            ([field, messages]) =>
                                `${field}: ${messages.join(", ")}`
                        )
                        .join("\n");
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.message) {
                    errorMessage = error.message;
                }

                this._error = errorMessage;
                throw new Error(errorMessage);
            } finally {
                this._isLoading = false;
            }
        },

        async deleteBlotter(id) {
            this._isLoading = true;
            this._error = null;
            try {
                await axios.delete(`/blotters/${id}`);
                this._blotters = this._blotters.filter((b) => b.id !== id);
                return true;
            } catch (error) {
                console.error(`Error deleting blotter ID ${id}:`, error);
                this._error = error;
                return false;
            } finally {
                this._isLoading = false;
            }
        },

        prepareFormData(blotterData) {
            const formData = new FormData();

            Object.entries(blotterData).forEach(([key, value]) => {
                if (key === "supporting_documents" && Array.isArray(value)) {
                    value.forEach((file) => {
                        formData.append("supporting_documents[]", file);
                    });
                } else if (value !== null && value !== undefined) {
                    formData.append(key, value);
                }
            });

            formData.append("complainants_type", "App\\Models\\Resident");
            formData.append("respondents_type", "App\\Models\\Resident");
            formData.append("total_cases", blotterData.total_cases || "0");

            return formData;
        },

        resetBlotter() {
            console.log("Resetting blotter store state");
            this._blotter = null;
            this._error = null;
            this._isLoading = false;

            // Clear any pending operations
            if (this._currentRequest) {
                this._currentRequest = null;
            }
        },

        // Add a complete reset function
        resetStore() {
            console.log("Complete store reset");
            this._blotters = [];
            this._blotter = null;
            this._paginate = null;
            this._isLoading = false;
            this._error = null;
        },

        // Add proper cancellation for ongoing requests
        cancelOngoingRequests() {
            if (this._currentRequest) {
                // If using axios with cancel tokens, cancel here
                this._currentRequest = null;
            }
            this._isLoading = false;
        },
    },
});
