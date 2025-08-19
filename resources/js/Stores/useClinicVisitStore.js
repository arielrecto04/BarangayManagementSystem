import { axios } from '@/Utils'
import {defineStore} from 'pinia'

export const useClinicVisitStore = defineStore('clinicVisit', {
    state: () => ({
        _visits: [],
        _isLoading: false,
        _visit: null,
        _paginate: {
            total: 0,
            per_page: 0,
            current_page: 0,
            last_page: 0,
            from: 0,
            to: 0,
        },

    }),

    getters: {
        visits: (state) => state._visits,
        isLoading: (state) => state._isLoading,
        visit: (state) => state._visit,
        paginate: (state) =>state._paginate,
    },

    actions: {

        async getVisits(page = 1, searchQuery = '') {
            try {
                this._isLoading = true;
                const response = await axios.get(`/clinic-visits?page=${page}&search=${searchQuery}`);

                this._visits = response.data.data;
                this._paginate = {
                    total: response.data.total,
                    per_page: response.data.per_page,
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    from: response.data.from,
                    to: response.data.to,
                };
            } catch (error){

                console.error("Error fetching clinic visits:", error);
                throw error;

            } finally {

                this._isLoading = false;

            }
        },

        async addClinicVisit(visitData){
            try{

                this._isLoading = true;
                const response = await axios.post('/clinic-visits', visitData);
                this._visits.unshift(response.data);

            } catch(error){

                console.error("Error adding clinic visit:", error);
                throw error;

            } finally {

                this._isLoading = false;
            }
        },

        async getVisitById(visitId){
            try{
                this._isLoading = true;
                const response = await axios.get(`/clinic-visits/${visitId}`);
                this._visit = response.data;
            } catch(error){

                console.error(`Error fetching visit ${visitId}`, error);
                throw error;

            } finally{

                this._isLoading = false;
            }
        },

        async deleteClinicVisit(visitId){

            try {
                this._isLoading = true;
                await axios.delete(`/clinic-visits/${visitId}`);
                this._visits = this._visits.filter((visit) => visit.id !== visitId);
            }
            catch(error){
                console.error(`Error deleting visit ${visitId}:`, error);
                throw error;
            }
            finally{

                this._isLoading = false;

            }
        },

    },
});
