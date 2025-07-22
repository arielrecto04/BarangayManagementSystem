import { createRouter, createWebHistory } from "vue-router";
import { useAuthenticationStore } from "@/Stores";

const routes = [
    {
        // path: "/",
        // name: "Home",
        // meta: {
        //     title: "Home",
        //     requiresAuth: false,
        // },
        // component: () => import("@/Pages/Home.vue"),
    },
    {
        path: "/Registration",
        name: "Registration",
        meta: {
            title: "Registration",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Auth/Registration.vue"),

    },
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        meta: {
            title: "Not Found",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/NotFound.vue"),
    },
    {
        path: "/dashboard",
        name: "Dashboard",
        meta: {
            title: "Dashboard",
            requiresAuth: true,
        },
        component: () => import("@/Pages/Dashboard/Dashboard.vue"),
    },
    {
        path: "/dashboard/residents",
        name: "Residents",
        meta: {
            title: "Residents",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/Residents.vue"),
    },
    {
        path: "/dashboard/residents/AddResident",
        name: "Add Resident",
        meta: {
            title: "Add Resident",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Resident/AddResident.vue"),
    },
    {
        path: "/dashboard/documents",
        name: "Documents",
        meta: {
            title: "Documents",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/Documents.vue"),
    },
    {
        path: "/dashboard/complaints",
        name: "Complaints",
        meta: {
            title: "Complaints",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/Complaints.vue"),
    },
    {
        path: "/dashboard/blotter",
        name: "Blotter",
        meta: {
            title: "Blotter",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/Blotter.vue"),
    },
    {
        path: "/dashboard/projects",
        name: "Projects",
        meta: {
            title: "Projects",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/Projects.vue"),
    },
    {
        path: "/dashboard/officials",
        name: "Officials",
        meta: {
            title: "Officials",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/Officials.vue"),
    },
    {
        path: "/dashboard/health",
        name: "Health Services",
        meta: {
            title: "Health Services",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/HealthServices.vue"),
    },
    {
        path: "/",
        name: "Login",
        meta: {
            title: "Login",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Auth/Login.vue"),
    },



];

const router = createRouter({
    history: createWebHistory(),
    routes,
});


router.beforeEach((to, from, next) => {
    const auth = useAuthenticationStore();

    console.log(auth.isAuthenticated);
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        next({ name: "Login" });
        return;
    }
    if (!to.meta.requiresAuth && auth.isAuthenticated) {
        next({ name: "Dashboard" });
        return;
    }

    next();
});

router.beforeResolve((to, from, next) => {
    document.title = to.meta.title;
    next();
});

export default router;
