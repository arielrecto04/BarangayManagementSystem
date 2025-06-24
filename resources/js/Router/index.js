import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        name: "Home",
        component: () => import("@/Pages/Home.vue"),
    },
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        component: () => import("@/Pages/NotFound.vue"),
    },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
