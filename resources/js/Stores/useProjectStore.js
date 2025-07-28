import {axios} from '@/utils'
import { defineStore } from 'pinia'

export const useprojectStore = defineStore('project', {
    state: () => ({
        _projects: [],
        _isLoading: false,
        _project : null,
    }),
    getters: {
        projects: (state) => state._projects,
        isLoading: (state) => state._isLoading,
        project: (state) => state._project
    },

    actions: {


        selectprojectById(projectId){

            this._project = this._projects.find((project) => project.id == projectId);
        },

        async getprojects() {
    try {
        this._isLoading = true;
        const response = await axios.get('/projects');
        console.log('API Response:', response); // Debugging: Log the response
        if (response.data && response.data.data) {
            this._projects = response.data.data;
        } else {
            console.error('Invalid response format:', response);
            this._projects = [];
        }
    } catch (error) {
        console.error('Error fetching projects:', error);
        this._projects = []; // Ensure the UI doesn't break
    } finally {
        this._isLoading = false;
    }
},

         async addproject(project) {
            try {
                this._isLoading = true;
                const response = await axios.post('/projects', project);
                this._projects.push(response.data);
            } catch (error) {
                console.log(error);
            }
            finally {
                this._isLoading = false;
            }
        },

        async updateproject(){
            try{

                const response = await axios.put(`/projects/${this._project.id}`, this._project);
                this._projects = this._projects.map((project) => {
                    if (project.id === response.data.id) {
                        return response.data;
                    }
                    return project;
                });


            }catch(error){
                console.log(error);
            }finally{

            }
        },

        async getprojectById(projectId){
            try{
                this._isLoading = true;
                const response = await axios.get(`/projects/${projectId}`);
                console.log(response.data)
                this._project = response.data;
            }catch(error){
                console.log(error);
            }finally{
                this._isLoading = false;
            }
        },
        async deleteproject(projectId){ 
            try { 
                this._isLoading = true;
                const response = await axios.delete(`/projects/${projectId}`);
                this._projects = this._projects.filter((project) => project.id !== projectId);
            } catch (error) {
                console.log(error);
            }
            finally {
                this._isLoading = false;
            }
        }
    },

});
