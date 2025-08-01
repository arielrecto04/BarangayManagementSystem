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
        path: "/dashboard",
        name: "Dashboard",
        meta: {
            title: "Dashboard",
            requiresAuth: true,
        },
        component: () => import("@/Pages/Dashboard/Dashboard.vue"),
    },
    {
        path: "/residents",
        name: "Residents",
        meta: {
            title: "Residents",
            requiresAuth: true,
        },
        redirect: { name: "List Residents" },
        component: () => import("@/Pages/Resident/ParentResidentView.vue"),
        children: [
            {
                path: "list-residents",
                name: "List Residents",
                meta: {
                    title: "List Residents",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Resident/ListResidents.vue"),
            },
            {
                path: "add-resident",
                name: "Add Resident",
                meta: {
                    title: "Add Resident",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Resident/AddResident.vue"),
            },
            {
                path: "edit-resident/:id",
                name: "Edit Resident",
                meta: {
                    title: "Edit Resident",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Resident/EditResident.vue"),
            },
        ],
    },
    {
        path: "/documents",
        name: "Documents",
        meta: {
            title: "Documents",
            requiresAuth: true,
        },
        component: () => import("@/Pages/Document/ListDocument.vue"),
    },
    {
        path: "/complaints",
        name: "Complaints",
        meta: {
            title: "Complaints",
            requiresAuth: true,
        },
        component: () => import("@/Pages/Dashboard/Complaints.vue"),
    },
    {
        path: "/blotter",
        name: "Blotter",
        meta: {
            title: "Blotter",
            requiresAuth: true,
        },
        redirect:{ name: "List Blotter" },
        component: () => import("@/Pages/Blotter/ParentBlotterView.vue"),
        children: [
            {
                path: "list-blotter",
                name: "List Blotter",
                meta: {
                    title: "List Blotter",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Blotter/ListBlotter.vue"),
            },
            {
                path: "add-blotter",
                name: "Add Blotter",
                meta: {
                    title: "Add Blotter",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Blotter/AddBlotter.vue"),
            },
            {
                path: "edit-blotter/:id",
                name: "Edit Blotter",
                meta: {
                    title: "Edit Blotter",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Blotter/EditBlotter.vue"),
            },
        ]
    },
    {
        path: "/projects",
        name: "Projects",
        meta: {
            title: "Projects",
            requiresAuth: true,
        },
        component: () => import("@/Pages/Project/ListProject.vue"),
    },
    {
        path: "/officials",
        name: "Officials",
        meta: {
            title: "Officials",
            requiresAuth: true,
        },
        redirect: { name: "List Officials" },
        component: () => import("@/Pages/Official/ParentOfficialView.vue"),
        children: [
            {
                path: "list-officials",
                name: "List Officials",
                meta: {
                    title: "List Officials",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Official/CardOfficial.vue"),
            },
            {
                path: "add-official",
                name: "Add Official",
                meta:{
                    title: "Add Official",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Official/AddOfficial.vue"),
            },
            {
                path: "edit-official/:id",
                name: "Edit Official",
                meta: {
                    title: "Edit Official",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Official/EditOfficial.vue"),
            },

        ]
    },
    {
        path: "/health",
        name: "Health Services",
        meta: {
            title: "Health Services",
            requiresAuth: true,
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
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        meta: {
            title: "Not Found",
            requiresAuth: false,
        },
        component: () => import("@/Pages/Dashboard/NotFound.vue"),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const auth = useAuthenticationStore();

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        next({ name: "Login" });
        return;
    }
    // if (!to.meta.requiresAuth && auth.isAuthenticated && to.name == "Login" ) {
    //     next({ name: "Dashboard" });
    //     return;
    // }

    next();
});

router.beforeResolve((to, from, next) => {
    document.title = to.meta.title;
    next();
});

export default router;
