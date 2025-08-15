import {axios} from '@/utils';
import {defineStore} from 'pinia';

export const useImmunizationStore = defineStore('immunization', {

    state: () => ({
        _records: [],
        _isLoading: false,
        _record: null,
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
        async getRecords(page = 1, searchQuery= '', vaccineFilter = ''){
            try{
                this._isLoading = true;
                const params = new URLSearchParams({
                    page,
                    search: searchQuery,
                    vaccine_name: vaccineFilter,
                });

                const response = await axios.get(`/immunizations?${params.toString()}`);

                this._records = response.data.data;
                this._paginate = {
                    total: response.data.total,
                    per_page: response.data.per_page,
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    from: response.data.from,
                    to: response.data.to,
                };
            } catch (error){
                console.error("Error fetching immunization records:", error);
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async addRecord(recordData) {
            try {
                this._isLoading = true;
                await axios.post('/immunizations', recordData);
                await this.getRecords();
            } catch(error){
                console.error("Error adding immunization record:", error);
                throw error;
            }finally{
                this._isLoading = false;
            }
        },

        async deleteRecord(recordId) {
            try {
                this._isLoading = true;
                await axios.delete(`/immunizations/${recordId}`);
                this._records = this.records.filter((record) => record.id !== recordId);
            } catch (error) {
                console.error(`Error deleting record ${recordId}:`, error);
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

    },

});