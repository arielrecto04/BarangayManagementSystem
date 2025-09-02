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
        clearOfficial() {
            this._official = null;
        },

        selectOfficialById(OfficialId) {
            this._official = this._officials.find(
                (official) => official.id == OfficialId
            );
        },

        async getOfficials() {
            try {
                this._isLoading = true;
                // Remove the include parameter since backend now includes resident by default
                const response = await axios.get("/officials");
                console.log("API Response:", response);
                if (response.data && response.data.data) {
                    this._officials = response.data.data.map((official) => ({
                        ...official,
                        image: official.image || official.photo || null,
                        // Include resident data if available
                        resident: official.resident || null,
                    }));
                } else {
                    console.error("Invalid response format:", response);
                    this._officials = [];
                }
            } catch (error) {
                console.error("Error fetching officials:", error);
                this._officials = [];
            } finally {
                this._isLoading = false;
            }
        },

        async addOfficial(official) {
            try {
                this._isLoading = true;
                const response = await axios.post("/officials", official);
                // Normalize the response data
                const newOfficial = {
                    ...response.data,
                    image: response.data.image || response.data.photo || null,
                    resident: response.data.resident || null,
                };
                this._officials.push(newOfficial);
                return response;
            } catch (error) {
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async updateOfficial(id, updatedData) {
            try {
                this._isLoading = true;
                const response = await axios.put(
                    `/officials/${id}`,
                    updatedData
                );

                // Normalize the response data
                const updatedOfficial = {
                    ...response.data,
                    image: response.data.image || response.data.photo || null,
                    resident: response.data.resident || null,
                };

                this._official = updatedOfficial;
                this._officials = this._officials.map((o) =>
                    o.id === updatedOfficial.id ? updatedOfficial : o
                );

                return response;
            } catch (error) {
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async getOfficialById(officialId) {
            try {
                this._isLoading = true;
                const response = await axios.get(`/officials/${officialId}`);
                console.log(response.data);
                // Normalize the response data
                this._official = {
                    ...response.data,
                    image: response.data.image || response.data.photo || null,
                    resident: response.data.resident || null,
                };
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
                return response;
            } catch (error) {
                console.log(error);
                throw error;
            } finally {
                this._isLoading = false;
            }
        },
    },
});
