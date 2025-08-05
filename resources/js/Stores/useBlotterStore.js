import { axios } from '@/utils';
import { defineStore } from 'pinia';

export const useBlotterStore = defineStore('blotter', {
    state: () => ({
        _blotters: [],
        _isLoading: false,
        _blotter: null,
    }),
    getters: {
        blotters: (state) => state._blotters,
        isLoading: (state) => state._isLoading,
        blotter: (state) => state._blotter,
    },
    actions: {
        async getBlotters() {
            try {
                this._isLoading = true;
                const response = await axios.get('/blotters');
                this._blotters = response.data.data;
            } catch (error) {
                console.error('Error fetching blotters:', error);
                throw error;
            } finally {
                this._isLoading = false;
            }
        },
        
        async getBlotterById(blotterId) {
            try {
                this._isLoading = true;
                const response = await axios.get(`/blotters/${blotterId}`);
                this._blotter = response.data;
            } catch (error) {
                console.error('Error fetching blotter:', error);
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async updateBlotter(blotterId, blotterData) {
            try {
                this._isLoading = true;
                const formattedData = {
                    ...blotterData,
                    complainants_type: 'App\\Models\\Resident',
                    respondents_type: 'App\\Models\\Resident',
                    total_cases: blotterData.total_cases || '0'
                };
                const response = await axios.put(`/blotters/${blotterId}`, formattedData);
                const updatedBlotter = response.data.data;
                const index = this._blotters.findIndex(b => b.id === blotterId);
                if (index !== -1) {
                    this._blotters[index] = updatedBlotter;
                }
                return updatedBlotter;
            } catch (error) {
                console.error('Error updating blotter:', error);
                throw error;
            } finally {
                this._isLoading = false;
            }
        },
        async deleteBlotter(blotterId) {
            try {
                this._isLoading = true;
                await axios.delete(`/blotters/${blotterId}`);
                this._blotters = this._blotters.filter((blotter) => blotter.id !== blotterId);
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },
        async addBlotter(blotter) {
            try {
                this._isLoading = true;
                // Format dates to match database expectations
                const formattedData = {
                    ...blotter,
                    filing_date: blotter.filing_date,
                    datetime_of_incident: new Date(blotter.datetime_of_incident).toISOString().split('T')[0],
                    total_cases: '0'
                };
                const response = await axios.post('/blotters', formattedData);
                if (response.data && response.data.data) {
                    this._blotters.push(response.data.data);
                    return response.data;
                }
                throw new Error('Invalid server response');
            } catch (error) {
                console.error('Error adding blotter:', error.response?.data || error);
            } finally {
                this._isLoading = false;
            }
        },
         resetBlotter() {
        this._blotter = null;
    }
    },
});