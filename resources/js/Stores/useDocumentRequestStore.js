import { defineStore } from "pinia";
import { axios } from "@/Utils";

export const useDocumentRequestStore = defineStore("document-request", {
    state: () => ({
        documentRequests: [],
        isLoading: false,
        isSubmitting: false,
        paginate: {
            total: 0,
            per_page: 0,
            current_page: 0,
            last_page: 0,
            from: 0,
            to: 0,
        },
        statistic: {
            total_by_document_type: [],
            total_requests: 0,
            total_requests_by_status: [],
        },
    }),
    actions: {
        async fetchDocumentRequests() {
            try {
                this.isLoading = true;
                const response = await axios.get("/document-requests");
                this.documentRequests = response.data.data;
                this.paginate = {
                    total: response.data.total,
                    per_page: response.data.per_page,
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    from: response.data.from,
                    to: response.data.to,
                };
            } catch (error) {
                console.log(error);
            } finally {
                this.isLoading = false;
            }
        },
        async addDocumentRequest(data) {
            try {
                this.isSubmitting = true;
                const response = await axios.post("/document-requests", data);
                this.documentRequests.push(response.data);
            } catch (error) {
                console.log(error);
            } finally {
                this.isSubmitting = false;
            }
        },
        async statistic() {
            try {
                this.isLoading = true;
                const response = await axios.get(
                    "/document-requests/statistic"
                );
                this.statistic = response.data;
            } catch (error) {
                console.log(error);
            } finally {
                this.isLoading = false;
            }
        },
        async searchDocumentRequests(search) {
            try {

                console.log(search, 'search');
                const response = await axios.get(`/document-requests/search?search=${search}`);
                this.documentRequests = response.data.data;
                this.paginate = {
                    total: response.data.total,
                    per_page: response.data.per_page,
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    from: response.data.from,
                    to: response.data.to,
                };
            } catch (error) {
                console.log(error);
            }
        },

        async updateStatusDocumentRequest(id, data) {
            try {
                this.isSubmitting = true;
                const response = await axios.put(`/document-requests/update-status/${id}`, data);


                this.documentRequests = this.documentRequests.map((request) => {
                    if (request.id == id) {
                        return { ...request, ...response.data.document_request };
                    }
                    return request;
                });

            } catch (error) {
                console.log(error);
            } finally {
                this.isSubmitting = false;
            }

        },
    },
});
