import { axios } from '@/utils';
import { defineStore } from 'pinia';

export const useBlotterStore = defineStore('blotter', {
    state: () => ({
        _blotters: [],
        _blotter: null,
        _paginate: null,
        _blotter: null,
        _paginate: null,
        _isLoading: false,
        _error: null,
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
                this._blotter = response.data.data; // Fix: access the data property
            } catch (error) {
                console.error(`Error fetching blotter ID ${id}:`, error);
                this._error = error;
            } finally {
                this._isLoading = false;
            }
        },

        async addBlotter(formData) {
            this._isLoading = true;
            this._error = null;
            try {
                const response = await axios.post('/blotters', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                if (response.data?.data) {
                    this._blotters.unshift(response.data.data);
                    return response.data;
                }
                throw new Error('Invalid server response');
            } catch (error) {
                console.error("Error creating blotter:", error);
                this._error = error;

                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    const errorMessages = Object.values(errors).flat().join('\n');
                    throw new Error(`Validation failed:\n${errorMessages}`);
                }
                throw error;
            } finally {
                this._isLoading = false;
            }
        },
        async getBlotterById(id) {
            return await this.fetchBlotter(id);
        },

        // Update the updateBlotter method to:
        async updateBlotter(id, data) {
            this._isLoading = true;
            this._error = null;
            try {
                let response;
                let url = `/blotters/${id}`;

                if (data instanceof FormData) {
                    data.append('_method', 'PATCH');
                    response = await axios.post(url, data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });
                } else {
                    // Ensure required fields are included
                    const payload = {
                        ...data,
                        complainants_type: 'App\\Models\\Resident',
                        respondents_type: 'App\\Models\\Resident',
                        filing_date: data.filing_date ? new Date(data.filing_date).toISOString() : null,
                        datetime_of_incident: data.datetime_of_incident ? new Date(data.datetime_of_incident).toISOString() : null
                    };

                    response = await axios.patch(url, payload);
                }

                // Update local state
                const index = this._blotters.findIndex(b => b.id === id);
                if (index !== -1) {
                    this._blotters[index] = response.data.data;
                }

                // Update current blotter if it's the one being edited
                if (this._blotter?.id === id) {
                    this._blotter = response.data.data;
                }

                return response.data;
            } catch (error) {
                console.error(`Error updating blotter ID ${id}:`, error);

                // Enhanced error handling
                let errorMessage = 'Failed to update blotter';
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    errorMessage = Object.entries(errors)
                        .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
                        .join('\n');
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
                this._blotters = this._blotters.filter(b => b.id !== id);
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
                if (key === 'supporting_documents' && Array.isArray(value)) {
                    value.forEach(file => {
                        formData.append('supporting_documents[]', file);
                    });
                } else if (value !== null && value !== undefined) {
                    formData.append(key, value);
                }
            });

            formData.append('complainants_type', 'App\\Models\\Resident');
            formData.append('respondents_type', 'App\\Models\\Resident');
            formData.append('total_cases', blotterData.total_cases || '0');

            return formData;
        },

        resetBlotter() {
            this._blotter = null;
        }
    }
});
