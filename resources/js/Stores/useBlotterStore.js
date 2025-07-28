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
                console.log(error);
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
    },
});