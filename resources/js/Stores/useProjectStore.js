import { axios } from "@/Utils";
import { defineStore } from "pinia";

export const useProjectStore = defineStore("project", {
    state: () => ({
        _projects: [],
        _isLoading: false,
        _project: null,
        _error: null,
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
        projects: (state) => state._projects,
        isLoading: (state) => state._isLoading,
        project: (state) => state._project,
        error: (state) => state._error,
        paginate: (state) => state._paginate,
    },

    actions: {
        clearError() {
            this._error = null;
        },

        clearErrors() {
            this._error = null;
        },

        selectProjectById(projectId) {
            this._project = this._projects.find(
                (project) => project.id == projectId
            );
        },

        async getProjects(page = 1) {
            try {
                this._isLoading = true;
                this._error = null;

                const response = await axios.get(`/projects?page=${page}`);
                console.log("API Response:", response);

                this._projects = response.data.projects.data;
                this._paginate = {
                    total: response.data.projects.total,
                    per_page: response.data.projects.per_page,
                    current_page: response.data.projects.current_page,
                    last_page: response.data.projects.last_page,
                    from: response.data.projects.from,
                    to: response.data.projects.to,
                };
            } catch (error) {
                console.error("Error fetching projects:", error);
                this._error = error.response?.data?.errors || {
                    general: ["Failed to fetch projects"],
                };
                this._projects = [];
            } finally {
                this._isLoading = false;
            }
        },

        async addProject(project, config = {}) {
            try {
                this._error = null;
                this._isLoading = true;

                console.log("Sending project data to API...");

                // Merge default config with provided config
                const requestConfig = {
                    ...config,
                    headers: {
                        "Content-Type": "multipart/form-data",
                        ...config.headers,
                    },
                };

                const response = await axios.post(
                    "/projects",
                    project,
                    requestConfig
                );
                console.log("Project creation successful:", response.data);

                // Insert at the top (latest first)
                this._projects.unshift(response.data.data);

                // Update total count in pagination
                this._paginate.total += 1;
                if (this._projects.length > this._paginate.per_page) {
                    this._projects.pop(); // keep within per_page limit
                }
            } catch (error) {
                console.error("Error adding project:", error);

                // Enhanced error logging similar to ListComplaints.vue pattern
                if (error.response) {
                    console.error("Response status:", error.response.status);
                    console.error("Response data:", error.response.data);

                    this._error = error.response?.data?.errors || {
                        general: [
                            error.response?.data?.message ||
                                "Failed to add project",
                        ],
                    };
                } else if (error.request) {
                    console.error("No response received:", error.request);
                    this._error = {
                        general: [
                            "No response from server. Please check your connection.",
                        ],
                    };
                } else {
                    console.error("Error setting up request:", error.message);
                    this._error = {
                        general: [
                            "An unexpected error occurred while adding the project.",
                        ],
                    };
                }

                // Re-throw the error so the component can handle it
                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async updateProject() {
            try {
                this._error = null;
                this._isLoading = true;

                const response = await axios.put(
                    `/projects/${this._project.id}`,
                    this._project
                );

                const updated = response.data.data || response.data;
                this._projects = this._projects.map((project) =>
                    project.id === updated.id ? updated : project
                );
            } catch (error) {
                console.error("Error updating project:", error);

                if (error.response) {
                    console.error(
                        "Update response status:",
                        error.response.status
                    );
                    console.error("Update response data:", error.response.data);

                    this._error = error.response?.data?.errors || {
                        general: [
                            error.response?.data?.message ||
                                "Failed to update project",
                        ],
                    };
                } else {
                    this._error = {
                        general: [
                            "An unexpected error occurred while updating the project.",
                        ],
                    };
                }

                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async getProjectById(projectId) {
            try {
                this._isLoading = true;
                this._error = null;

                const response = await axios.get(`/projects/${projectId}`);
                this._project = response.data.data || response.data;
            } catch (error) {
                console.error("Error fetching project:", error);

                this._error = error.response?.data?.errors || {
                    general: [
                        error.response?.data?.message ||
                            "Failed to fetch project",
                    ],
                };

                throw error;
            } finally {
                this._isLoading = false;
            }
        },

        async deleteProject(projectId) {
            try {
                this._isLoading = true;
                this._error = null;

                await axios.delete(`/projects/${projectId}`);

                // Remove from current projects list
                this._projects = this._projects.filter(
                    (project) => project.id !== projectId
                );

                // Update pagination info
                this._paginate.total -= 1;

                // If we're on a page that might now be empty, fetch previous page
                if (
                    this._projects.length === 0 &&
                    this._paginate.current_page > 1
                ) {
                    await this.getProjects(this._paginate.current_page - 1);
                }
            } catch (error) {
                console.error("Error deleting project:", error);

                this._error = error.response?.data?.errors || {
                    general: [
                        error.response?.data?.message ||
                            "Failed to delete project",
                    ],
                };

                throw error; // Re-throw to handle in component
            } finally {
                this._isLoading = false;
            }
        },

        async searchProjects(search) {
            try {
                this._isLoading = true;
                this._error = null;

                const response = await axios.get(
                    `/projects/search?search=${search}`
                );
                this._projects = response.data.projects.data;
            } catch (error) {
                console.error("Error searching projects:", error);

                this._error = error.response?.data?.errors || {
                    general: [error.response?.data?.message || "Search failed"],
                };
            } finally {
                this._isLoading = false;
            }
        },
    },
});
