import { axios } from "@/utils";
import { defineStore } from "pinia";

export const useAnnouncementEventStore = defineStore("announcementEvent", {
    state: () => ({
        _announcementEvents: [], // Always an array
        _announcementEvent: null,
        _paginate: null,
        _isLoading: false,
        _error: null,
        _lastPage: 1, // Track last page fetched
        _currentType: "", // Track current filter type
    }),

    getters: {
        announcementEvents: (state) => state._announcementEvents,
        announcementEvent: (state) => state._announcementEvent,
        paginate: (state) => state._paginate,
        isLoading: (state) => state._isLoading,
        error: (state) => state._error,
        lastPage: (state) => state._lastPage,
        currentType: (state) => state._currentType,
    },

    actions: {
        async getAnnouncementEvents(page = 1, type = "") {
            this._isLoading = true;
            this._error = null;
            try {
                let url = `/announcement-events?page=${page}`;
                if (type) url += `&type=${type}`;

                const response = await axios.get(url);

                // Always ensure an array
                this._announcementEvents = Array.isArray(response.data.data)
                    ? response.data.data
                    : [];

                this._paginate = {
                    current_page: response.data.current_page || 1,
                    last_page: response.data.last_page || 1,
                    per_page: response.data.per_page || 10,
                    total: response.data.total || 0,
                };

                // Save pagination state
                this._lastPage = page;
                this._currentType = type;
            } catch (error) {
                console.error("Error fetching announcement events:", error);
                this._announcementEvents = [];
                this._paginate = null;
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        async fetchAnnouncementEvent(id) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.get(`/announcement-events/${id}`);
                this._announcementEvent = response.data || null;
            } catch (error) {
                console.error(
                    `Error fetching announcement event ID ${id}:`,
                    error
                );
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        async createAnnouncementEvent(formData) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.post(
                    "/announcement-events",
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );

                // Only add to local array if on first page
                if (!Array.isArray(this._announcementEvents))
                    this._announcementEvents = [];
                if (this._paginate?.current_page === 1) {
                    this._announcementEvents.unshift(response.data.data);
                }

                return response.data;
            } catch (error) {
                console.error("Error creating announcement event:", error);
                this._error = error;
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async updateAnnouncementEvent(id, data) {
            this._isLoading = true;
            this._error = null;
            try {
                let response;
                if (data instanceof FormData) {
                    response = await axios.post(
                        `/announcement-events/${id}`,
                        data,
                        { headers: { "Content-Type": "multipart/form-data" } }
                    );
                } else {
                    response = await axios.patch(
                        `/announcement-events/${id}`,
                        data
                    );
                }

                const index = this._announcementEvents.findIndex(
                    (a) => a.id === id
                );
                if (index !== -1)
                    this._announcementEvents[index] = response.data.data;

                return response.data;
            } catch (error) {
                console.error(
                    `Error updating announcement event ID ${id}:`,
                    error
                );
                this._error = error;
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async deleteAnnouncementEvent(id) {
            this._isLoading = true;
            this._error = null;
            try {
                await axios.delete(`/announcement-events/${id}`);
                if (Array.isArray(this._announcementEvents)) {
                    this._announcementEvents = this._announcementEvents.filter(
                        (a) => a.id !== id
                    );
                }
            } catch (error) {
                console.error(
                    `Error deleting announcement event ID ${id}:`,
                    error
                );
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },
    },
});
