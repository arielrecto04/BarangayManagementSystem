import { axios } from "@/Utils";
import { defineStore } from "pinia";

export const useOfficialStore = defineStore("official", {
    state: () => ({
        _officials: [],
        _isLoading: false,
        _official: null,
    }),
    getters: {
        officials: (state) => state._officials,
        isLoading: (state) => state._isLoading,
        official: (state) => state._official,
    },

    actions: {
        selectOfficialById(OfficialId) {
            this._official = this._officials.find(
                (official) => official.id == OfficialId
            );
        },

        async getOfficials() {
            try {
                this._isLoading = true;
                const response = await axios.get("/officials");
                console.log("API Response:", response); // Debugging: Log the response
                if (response.data && response.data.data) {
                    this._officials = response.data.data;
                } else {
                    console.error("Invalid response format:", response);
                    this._officials = [];
                }
            } catch (error) {
                console.error("Error fetching officials:", error);
                this._officials = []; // Ensure the UI doesn't break
            } finally {
                this._isLoading = false;
            }
        },

        async addOfficial(official) {
            try {
                this._isLoading = true;
                const response = await axios.post("/officials", official);
                this._officials.push(response.data);
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },

        async updateOfficial(updatedData) {
            try {
                this._isLoading = true;
                const response = await axios.put(
                    `/officials/${this._official.id}`,
                    updatedData
                );

                // Update store state
                this._official = response.data;
                this._officials = this._officials.map((o) =>
                    o.id === response.data.id ? response.data : o
                );

                return response;
            } catch (error) {
                throw error; // Propagate error to component
            } finally {
                this._isLoading = false;
            }
        },

        async getOfficialById(officialId) {
            try {
                this._isLoading = true;
                const response = await axios.get(`/officials/${officialId}`);
                console.log(response.data);
                this._official = response.data;
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },
        async deleteOfficial(officialId) {
            try {
                this._isLoading = true;
                const response = await axios.delete(`/officials/${officialId}`);
                this._officials = this._officials.filter(
                    (official) => official.id !== officialId
                );
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },
    },
});
