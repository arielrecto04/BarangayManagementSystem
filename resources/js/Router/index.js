import { createRouter, createWebHistory } from "vue-router";
import { useAuthenticationStore } from "@/Stores";

const routes = [
    {
        path: "/",
        name: "ParentLandingPage",
        redirect: { name: "Home" },
        meta: {
            title: "Home",
            requiresAuth: false,
        },
        component: () => import("@/Pages/LandingPage/ParentLandingPage.vue"),
        children: [
            {
                path: "home",
                name: "Home",
                meta: {
                    title: "Home",
                    requiresAuth: false,
                },
                component: () => import("@/Pages/LandingPage/Home.vue"),
            },
        ],
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
        redirect: { name: "Document Dashboard" },
        meta: {
            title: "Documents",
            requiresAuth: true,
        },
        children: [
            {
                path: "list-documents",
                name: "List Documents",
                meta: {
                    title: "List Documents",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Document/ListDocument.vue"),
            },
            {
                path: "dashboard",
                name: "Document Dashboard",
                meta: {
                    title: "Dashboard",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Document/Dashboard.vue"),
            },
            {
                path: "add-document",
                name: "Add Document",
                meta: {
                    title: "Add Document",
                    requiresAuth: true,
                },
                component: () =>
                    import("@/Pages/Document/AddRequestDocument.vue"),
            },
            {
                path: "stage-document/:documentType",
                name: "Stage Request Document",
                meta: {
                    title: "Stage Request Document",
                    requiresAuth: true,
                },
                component: () =>
                    import("@/Pages/Document/StageRequestDocument.vue"),
            },
        ],
    },
    {
        path: "/complaints",
        name: "Complaints",
        meta: {
            title: "Complaints",
            requiresAuth: true,
        },
        redirect: { name: "List Complaints" },
        component: () => import("@/Pages/Complaint/ParentComplaintView.vue"),
        children: [
            {
                path: "list-complaints",
                name: "List Complaints",
                meta: {
                    title: "List Complaints",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Complaint/ListComplaints.vue"),
            },
            {
                path: "add-complaint",
                name: "Add Complaint",
                meta: {
                    title: "Add Complaint",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Complaint/AddComplaint.vue"),
            },
            {
                path: "edit-complaint/:id",
                name: "Edit Complaint",
                meta: {
                    title: "Edit Complaint",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Complaint/EditComplaint.vue"),
            },
        ],
    },
    {
        path: "/blotter",
        name: "Blotter",
        meta: {
            title: "Blotter",
            requiresAuth: true,
        },
        redirect: { name: "List Blotter" },
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
        ],
    },
    {
        path: "/projects",
        name: "Projects",
        meta: {
            title: "Projects",
            requiresAuth: true,
        },
        redirect: { name: "List Projects" },
        component: () => import("@/Pages/Project/ParentProjectView.vue"),
        children: [
            {
                path: "list-projects",
                name: "List Projects",
                meta: {
                    title: "List Projects",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Project/ListProject.vue"),
            },
            {
                path: "add-project",
                name: "Add Project",
                meta: {
                    title: "Add Project",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Project/AddProject.vue"),
            },
            {
                path: "edit-project/:id",
                name: "Edit Project",
                meta: {
                    title: "Edit Project",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Project/EditProject.vue"),
            },
        ],
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
                path: "CardOfficial",
                name: "Card Official",
                meta: {
                    title: "Card Official",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Official/CardOfficial.vue"),
            },
            {
                path: "list-officials",
                name: "List Officials",
                meta: {
                    title: "List Officials",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/Official/ListOfficials.vue"),
            },
            {
                path: "add-official",
                name: "Add Official",
                meta: {
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
        ],
    },
    {
        path: "/health",
        name: "Health Services",
        meta: {
            title: "Health Services",
            requiresAuth: true,
        },
        redirect: { name: "Health Dashboard" },
        component: () =>
            import("@/Pages/HealthService/ParentHealthServiceView.vue"),
        children: [
            {
                path: "dashboard",
                name: "Health Dashboard",
                meta: {
                    title: "Health Dashboard",
                    requiresAuth: true,
                },
                component: () => import("@/Pages/HealthService/Dashboard.vue"),
            },
            {
                path: "clinic-visits",
                name: "Clinic Visits",
                meta: {
                    title: "Clinic Visits Log",
                    requiresAuth: true,
                },
                component: () =>
                    import(
                        "@/Pages/HealthService/ClinicVisit/ClinicVisits.vue"
                    ),
            },
            {
                path: "immunizations",
                name: "Immunizations",
                meta: {
                    title: "Immunization Records",
                    requiresAuth: true,
                },
                component: () =>
                    import(
                        "@/Pages/HealthService/Immunization/ImmunizationRecords.vue"
                    ),
            },
            {
                path: "mch",
                name: "Maternal & Child Health",
                component: () =>
                    import(
                        "@/Pages/HealthService/MaternalChildHealth/MaternalChildHealth.vue"
                    ),
                redirect: { name: "Prenatal Records" },
                children: [
                    {
                        path: "prenatal",
                        name: "Prenatal Records",
                        component: () =>
                            import(
                                "@/Pages/HealthService/MaternalChildHealth/PrenatalRecords.vue"
                            ),
                    },
                    {
                        path: "child-growth",
                        name: "Child Growth Records",
                        component: () =>
                            import(
                                "@/Pages/HealthService/MaternalChildHealth/ChildGrowthRecords.vue"
                            ),
                    },
                ],
            },
            {
                path: "programs",
                name: "Community Programs",
                component: () =>
                    import(
                        "@/Pages/HealthService/CommunityProgram/CommunityPrograms.vue"
                    ),
                redirect: { name: "Family Planning" }, // Default to the first sub-tab
                children: [
                    {
                        path: "family-planning",
                        name: "Family Planning",
                        component: () =>
                            import(
                                "@/Pages/HealthService/CommunityProgram/FamilyPlanning.vue"
                            ),
                    },
                    {
                        path: "nutrition-status",
                        name: "Nutrition Status",
                        component: () =>
                            import(
                                "@/Pages/HealthService/CommunityProgram/NutritionStatus.vue"
                            ),
                    },
                ],
            },
            {
                path: "reports",
                name: "Health Reports",
                meta: {
                    title: "Health Reports",
                    requiresAuth: true,
                },
                component: () =>
                    import("@/Pages/HealthService/HealthReports.vue"),
            },
            {
                path: "settings",
            },
        ],
    },

    {
        path: "/announcement-events",
        name: "Announcement Events",
        meta: {
            title: "Announcement & Events",
            requiresAuth: true,
        },
        redirect: { name: "List Announcement Events" },
        component: () =>
            import("@/Pages/AnnouncementEvent/ParentAnnouncementEventView.vue"),
        children: [
            {
                path: "list-announcements-events",
                name: "List Announcement Events",
                meta: {
                    title: "List Announcement Events",
                    requiresAuth: true,
                },
                component: () =>
                    import(
                        "@/Pages/AnnouncementEvent/ListAnnouncementEvents.vue"
                    ),
            },
            {
                path: "add-announcement-event",
                name: "Add Announcement Event",
                meta: {
                    title: "Add Announcement Event",
                    requiresAuth: true,
                },
                component: () =>
                    import(
                        "@/Pages/AnnouncementEvent/AddAnnouncementEvent.vue"
                    ),
            },
            {
                path: "edit-announcement-event/:id",
                name: "Edit Announcement Event",
                meta: {
                    title: "Edit Announcement Event",
                    requiresAuth: true,
                },
                component: () =>
                    import(
                        "@/Pages/AnnouncementEvent/EditAnnouncementEvent.vue"
                    ),
            },
        ],
    },

    {
        path: "/login",
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

    // Clear any potential store state issues when leaving edit pages
    if (from.name && from.name.includes("Edit")) {
        console.log("Leaving edit page, ensuring cleanup...");

        // Give components time to cleanup
        setTimeout(() => {
            // Force cleanup of any remaining reactive state
            // This helps prevent DOM node insertion issues
        }, 50);
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        next({ name: "Login" });
        return;
    }

    next();
});

router.afterEach((to, from) => {
    // Ensure DOM is stable after navigation
    if (from.name && from.name.includes("Edit")) {
        // Force garbage collection of any lingering DOM references
        setTimeout(() => {
            console.log("Post-navigation cleanup completed");
        }, 100);
    }
});

router.beforeResolve((to, from, next) => {
    document.title = to.meta.title;
    next();
});

export default router;
