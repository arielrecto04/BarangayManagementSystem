// useAnnouncementEventStore.js
import { axios } from "@/utils";
import { defineStore } from "pinia";

export const useAnnouncementEventStore = defineStore("announcementEvent", {
    state: () => ({
        _events: [],
        _event: null,
        _paginate: null,
        _isLoading: false,
        _error: null,
    }),

    getters: {
        events: (state) => state._events,
        event: (state) => state._event,
        paginate: (state) => state._paginate,
        isLoading: (state) => state._isLoading,
        error: (state) => state._error,
    },

    actions: {
        // Fetch list of events with pagination
        async getEvents(page = 1, perPage = 10) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.get(
                    `/announcement-events?page=${page}&per_page=${perPage}`
                );

                if (response.data.data) {
                    // Paginated response
                    this._events = response.data.data;
                    this._paginate = {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        per_page: response.data.per_page,
                        total: response.data.total,
                    };
                } else {
                    // Non-paginated fallback
                    this._events = response.data;
                    this._paginate = null;
                }
            } catch (error) {
                console.error("Error fetching events:", error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        // Fetch single event by ID
        async fetchEvent(id) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.get(`/announcement-events/${id}`);
                this._event = response.data;
            } catch (error) {
                console.error(`Error fetching event ID ${id}:`, error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        // Add new event
        async addEvent(data) {
            this._isLoading = true;
            this._error = null;
            try {
                let response;
                if (data instanceof FormData) {
                    response = await axios.post("/announcement-events", data, {
                        headers: { "Content-Type": "multipart/form-data" },
                    });
                } else {
                    response = await axios.post("/announcement-events", data);
                }

                if (this._paginate) {
                    await this.getEvents(1, this._paginate.per_page);
                } else {
                    this._events.push(response.data);
                }

                return response.data;
            } catch (error) {
                console.error("Error adding event:", error);
                this._error = error;
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        // Update existing event
        // Flexible updateEvent method in useAnnouncementEventStore.js
        async updateEvent(id, data) {
            this._isLoading = true;
            this._error = null;

            try {
                console.log(`Updating event ${id} with data:`, data);

                let response;

                if (data instanceof FormData) {
                    console.log("Sending FormData (with image):");
                    for (let [key, value] of data.entries()) {
                        console.log(`${key}:`, value);
                    }

                    // Use POST with _method override for FormData
                    response = await axios.post(
                        `/announcement-events/${id}`,
                        data,
                        {
                            headers: {
                                "Content-Type": "multipart/form-data",
                            },
                        }
                    );
                } else {
                    // Use regular PUT for JSON data
                    console.log("Sending JSON data (no new image):", data);
                    response = await axios.put(
                        `/announcement-events/${id}`,
                        data,
                        {
                            headers: {
                                "Content-Type": "application/json",
                            },
                        }
                    );
                }

                // Update the event in the local state
                const index = this._events.findIndex(
                    (e) => e.id === parseInt(id)
                );
                if (index !== -1) {
                    this._events[index] = response.data;
                }

                // Also update the single event if it matches
                if (this._event && this._event.id === parseInt(id)) {
                    this._event = response.data;
                }

                return response.data;
            } catch (error) {
                console.error(`Error updating event ID ${id}:`, error);

                // Enhanced error logging
                if (error.response) {
                    console.error("Response status:", error.response.status);
                    console.error("Response data:", error.response.data);
                    console.error("Response headers:", error.response.headers);

                    // Store detailed error information
                    this._error = {
                        message: error.response.data?.message || error.message,
                        status: error.response.status,
                        errors: error.response.data?.errors || null,
                        data: error.response.data,
                    };
                } else if (error.request) {
                    console.error("Request error:", error.request);
                    this._error = {
                        message: "Network error - no response received",
                        status: null,
                        errors: null,
                        data: null,
                    };
                } else {
                    console.error("Error:", error.message);
                    this._error = {
                        message: error.message,
                        status: null,
                        errors: null,
                        data: null,
                    };
                }

                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        // Delete event by ID
        async deleteEvent(id) {
            this._isLoading = true;
            this._error = null;
            try {
                await axios.delete(`/announcement-events/${id}`);
                this._events = this._events.filter((e) => e.id !== id);
            } catch (error) {
                console.error(`Error deleting event ID ${id}:`, error);
                this._error = error;
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        // Refresh statuses using backend endpoint
        async refreshStatuses() {
            this._isLoading = true;
            this._error = null;
            try {
                await axios.post("/announcement-events/refresh-statuses");
                // Reload events to get updated statuses
                await this.getEvents();
            } catch (error) {
                console.error("Failed to refresh statuses:", error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        // Clear error state
        clearError() {
            this._error = null;
        },
    },
});
