import { defineStore } from "pinia";
import { axios } from "@/utils"; // Make sure axios is imported

export const useDocumentStore = defineStore("document", {
    state: () => ({
        _documents: [],
        _isLoading: false,
        _paginate : {
            total : 0,
            per_page : 0,
            current_page : 0,
            from : 0,
            to : 0,
        }
    }),
    getters: {
        documents: (state) => state._documents,
        isLoading: (state) => state._isLoading,
        paginate: (state) => state._paginate,
    },
    actions: {

        async getDocuments() {
            try {

                this._isLoading = true;
                const response = await axios.get("/documents");
                this._documents = response.data.data;
                this._paginate = {
                    total : response.data.total,
                    per_page : response.data.per_page,
                    current_page : response.data.current_page,
                    from : response.data.from,
                    to : response.data.to,
                };
                } catch (error) {
                console.error("Failed to fetch documents:", error);
            } finally {
                this._isLoading = false;
            }
        },
        // The action now accepts an onProgress callback function
        async addDocument(formData, onProgress) {
            try {
                // We don't manage loading state here anymore, the component will.
                const response = await axios.post("/documents", formData, {
                    // Pass the component's progress handler to axios
                    onUploadProgress: onProgress,
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });

                // Add the successfully uploaded document to the main list
                this._documents = [response.data.document, ...this._documents];
            } catch (error) {
                console.error("Upload failed in store:", error);
                // Re-throw the error so the component's try/catch can handle it
                throw error;
            }
        },
    },
});
