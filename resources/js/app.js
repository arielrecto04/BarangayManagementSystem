import "./bootstrap";

import { createApp } from "vue";
import App from "@/App.vue";
import router from "@/Router";
import { createPinia } from "pinia";

// Create the Vue app instance
const app = createApp(App);

// Use Pinia for state management
app.use(createPinia());

// Use Vue Router
app.use(router);

// ✅ Global error handler to catch runtime errors
app.config.errorHandler = (err, instance, info) => {
    console.error("Global error:", err, info);

    if (
        err &&
        err.name === "NotFoundError" &&
        err.message.includes("insertBefore")
    ) {
        console.group("insertBefore DOM insertion error details");
        console.error("Error message:", err.message);

        if (instance) {
            console.log(
                "Vue component instance where error occurred:",
                instance
            );
            console.log("Component name:", instance.type?.name || "Unknown");
            console.log("Component vnode:", instance.vnode);
        }

        // Log the call stack for deeper insight
        console.error("Stack trace:", err.stack);
        console.groupEnd();
    }

    // Optional: alert or error reporting service call
};

// ✅ Mount the app only after DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    const root = document.getElementById("app");
    if (root) {
        app.mount(root);
    } else {
        console.error("Root element #app not found in index.html");
    }
});
