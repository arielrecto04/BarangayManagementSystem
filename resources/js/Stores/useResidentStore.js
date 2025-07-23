import {axios} from '@/utils'
import { defineStore } from 'pinia'

export const useResidentStore = defineStore('resident', {
    state: () => ({
        _residents: [],
        _isLoading: false,
    }),
    getters: {
        residents: (state) => state._residents,
        isLoading: (state) => state._isLoading,
    },

    actions: {

        async getResidents() {
            try {
                this._isLoading = true;
                const response = await axios.get('/residents');
              
                this._residents = response.data.data;
            } catch (error) {
                console.log(error);
            }
            finally {
                this._isLoading = false;
            }
        },
        
         async addResident(resident) {
            try {
                this._isLoading = true;
                const response = await axios.post('/residents', resident);
                this._residents.push(response.data);
            } catch (error) {
                console.log(error);
            }
            finally {
                this._isLoading = false;
            }
        },
        async deleteResident(id) {
            try {
                this._isLoading = true;
                await axios.delete(`/residents/${id}`);
                this._residents = this._residents.filter(resident => resident.id !== id);
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        }      
    },

});