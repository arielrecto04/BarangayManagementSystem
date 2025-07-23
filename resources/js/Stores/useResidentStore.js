import {axios} from '@/utils'
import { defineStore } from 'pinia'

export const useResidentStore = defineStore('resident', {
    state: () => ({
        _residents: [],
        _isLoading: false,
        _resident : null,
    }),
    getters: {
        residents: (state) => state._residents,
        isLoading: (state) => state._isLoading,
        resident: (state) => state._resident,
    },

    actions: {


        selectResidentById(residentId){

            this._resident = this._residents.find((resident) => resident.id == residentId);
        },

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

        async updateResident(){
            try{

                const response = await axios.put(`/residents/${this._resident.id}`, this._resident);
                this._residents = this._residents.map((resident) => {
                    if (resident.id === response.data.id) {
                        return response.data;
                    }
                    return resident;
                });


            }catch(error){
                console.log(error);
            }finally{

            }
        },

        async getResidentById(residentId){
            try{
                this._isLoading = true;
                const response = await axios.get(`/residents/${residentId}`);
                console.log(response.data)
                this._resident = response.data;
            }catch(error){
                console.log(error);
            }finally{
                this._isLoading = false;
            }
        },
        async deleteResident(residentId){ 
            try { 
                this._isLoading = true;
                const response = await axios.delete(`/residents/${residentId}`);
                this._residents = this._residents.filter((resident) => resident.id !== residentId);
            } catch (error) {
                console.log(error);
            }
            finally {
                this._isLoading = false;
            }
        }
    },

});
