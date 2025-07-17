import { createRouter, createWebHistory } from "vue-router";
import { useAuthenticationStore } from "@/Stores";

const routes = [
    {
        path: "/",
        name: "Home",
        meta: {
            title: "Home",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Home.vue"),
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
        component: () => import("@/Pages/NotFound.vue"),
    },
    {
        path: "/dashboard",
        name: "Dashboard",
        meta: {
            title: "Dashboard",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard.vue"),
    },
 {
        path: "/dashboard/residents",   
        name: "Residents",
        meta: {
            title: "Residents",
            requiresAuth: false,
        },
        component: () => import("@/Pages/residents.vue"),
    },
    {
        path: "/login",
        name: "Login",
        meta: {
            title: "Login",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Auth/Login.vue"),
    }
    

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
