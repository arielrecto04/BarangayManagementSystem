import { axios } from '@/utils';
import { defineStore } from 'pinia';

export const useComplaintStore = defineStore('complaint', {
    state: () => ({
        _complaints: [],
        _isLoading: false,
        _complaint: null,
    }),
    getters: {
        complaints: (state) => state._complaints,
        isLoading: (state) => state._isLoading,
        complaint: (state) => state._complaint,
    },
    actions: {
        async getcomplaints() {
            try {
                this._isLoading = true;
                const response = await axios.get('/complaints');
                this._complaints = response.data.data;
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },
        async deletecomplaint(complaintId) {
            try {
                this._isLoading = true;
                await axios.delete(`/complaints/${complaintId}`);
                this._complaints = this._complaints.filter((complaint) => complaint.id !== complaintId);
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },
    },
}); 