import { axios } from "@/utils";
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
        selectProjectById(projectId) {
            this._project = this._projects.find(
                (project) => project.id == projectId
            );
        },

        async getProjects(page = 1) {
            try {
                this._isLoading = true;
                const response = await axios.get("/projects?page=" + page);
                console.log("API Response:", response); // Debugging: Log the response

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
                this._projects = []; // Ensure the UI doesn't break
            } finally {
                this._isLoading = false;
            }
        },

        async addProject(project) {
            try {
                this._error = null;
                this._isLoading = true;
                const response = await axios.post("/projects", project);
                this._projects.push(response.data.data);
            } catch (error) {
                console.log(error);

                if (
                    error.response &&
                    error.response.data &&
                    error.response.data.message
                ) {
                    this._error = error.response.data.errors;

                    console.log(this._error);
                }
            } finally {
                this._isLoading = false;
            }
        },

        async updateProject() {
            try {
                const response = await axios.put(
                    `/projects/${this._project.id}`,
                    this._project
                );
                this._projects = this._projects.map((project) => {
                    if (project.id === response.data.id) {
                        return response.data;
                    }
                    return project;
                });
            } catch (error) {
                console.log(error);
            } finally {
            }
        },

        async getProjectById(projectId) {
            try {
                this._isLoading = true;
                const response = await axios.get(`/projects/${projectId}`);
                console.log(response.data);
                this._project = response.data;
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },
        async deleteProject(projectId) {
            try {
                this._isLoading = true;
                const response = await axios.delete(`/projects/${projectId}`);
                this._projects = this._projects.filter(
                    (project) => project.id !== projectId
                );
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },

        async searchProjects(search) {
            try {
                this._isLoading = true;
                const response = await axios.get(`/projects/search?search=${search}`);
                this._projects = response.data.projects.data;
            } catch (error) {
                console.log(error);
            } finally {
                this._isLoading = false;
            }
        },
    },
});
