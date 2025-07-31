import { defineStore } from "pinia";
import { axios } from "@/utils"; // Make sure axios is imported

export const useDocumentStore = defineStore("document", {
    state: () => ({
        _documents: [],
        _isLoading: false,
    }),
    getters: {
        documents: (state) => state._documents,
        isLoading: (state) => state._isLoading,
    },
    actions: {
        // The action now accepts an onProgress callback function
        async addDocument(formData, onProgress) {
            try {
                // We don't manage loading state here anymore, the component will.
                const response = await axios.post("/documents", formData, {
                    // Pass the component's progress handler to axios
                    onUploadProgress: onProgress,
                });

                // Add the successfully uploaded document to the main list
                this._documents.push(response.data.document);
            } catch (error) {
                console.error("Upload failed in store:", error);
                // Re-throw the error so the component's try/catch can handle it
                throw error;
            }
        },
    },
});
