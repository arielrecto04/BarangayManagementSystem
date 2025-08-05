import { axios } from '@/utils';
import { defineStore } from 'pinia';

export const useResidentStore = defineStore('resident', {
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
  }),

  getters: {
    residents: (state) => state._residents,
    isLoading: (state) => state._isLoading,
    resident: (state) => state._resident,
    paginate: (state) => state._paginate,
  },

  actions: {
    // Select resident by ID from current list
    selectResidentById(residentId) {
      this._resident = this._residents.find((resident) => resident.id === residentId);
    },

    // Fetch paginated list of residents
    async getResidents(page = 1) {
      this._isLoading = true;
      try {
        const response = await axios.get(`/residents?page=${page}`);
        this._residents = response.data.data;

        // Defensive fallback for pagination values
        this._paginate = {
          total: response.data.total ?? 0,
          per_page: response.data.per_page ?? 10,
          current_page: response.data.current_page ?? 1,
          last_page: response.data.last_page ?? 1,
          from: response.data.from ?? 0,
          to: response.data.to ?? 0,
        };
      } catch (error) {
        console.error("Error fetching residents:", error);
      } finally {
        this._isLoading = false;
      }
    },

    // Add a new resident
    async addResident(resident) {
      this._isLoading = true;
      try {
        const response = await axios.post('/residents', resident);
        this._residents.push(response.data);
      } catch (error) {
        console.error("Error adding resident:", error);
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

      try {
        const response = await axios.put(`/residents/${this._resident.id}`, this._resident);
        this._residents = this._residents.map((resident) =>
          resident.id === response.data.data.id ? response.data : resident
        );
      } catch (error) {
        console.error("Error updating resident:", error);
      }
    },

    // Get a resident by ID from API
    async getResidentById(residentId) {
      this._isLoading = true;
      try {
        const response = await axios.get(`/residents/${residentId}`);
        this._resident = response.data;
      } catch (error) {
        console.error("Error fetching resident:", error);
      } finally {
        this._isLoading = false;
      }
    },

    // Delete a resident by ID
    async deleteResident(residentId) {
      this._isLoading = true;
      try {
        await axios.delete(`/residents/${residentId}`);
        this._residents = this._residents.filter((resident) => resident.id !== residentId);
      } catch (error) {
        console.error("Error deleting resident:", error);
      } finally {
        this._isLoading = false;
      }
    },
  },
});
