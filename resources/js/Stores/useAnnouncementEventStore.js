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
                    // Non-paginated (fallback)
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

                // If paginated, re-fetch first page instead of pushing
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

        async updateEvent(id, data) {
            this._isLoading = true;
            this._error = null;
            try {
                let response;

                if (data instanceof FormData) {
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
                    response = await axios.put(
                        `/announcement-events/${id}`,
                        data
                    );
                }

                const index = this._events.findIndex((e) => e.id === id);
                if (index !== -1) {
                    this._events[index] = response.data;
                }

                return response.data;
            } catch (error) {
                console.error(`Error updating event ID ${id}:`, error);
                this._error = error;
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async deleteEvent(id) {
            this._isLoading = true;
            this._error = null;
            try {
                await axios.delete(`/announcement-events/${id}`);
                this._events = this._events.filter((e) => e.id !== id);
            } catch (error) {
                console.error(`Error deleting event ID ${id}:`, error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },
    },
});
