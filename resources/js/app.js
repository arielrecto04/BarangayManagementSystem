import "./bootstrap";

import { createApp } from "vue";
import App from "@/App.vue";
import router from "@/router";
import { createPinia } from "pinia";

// ✅ Assign to variable so you can access `config`
const app = createApp(App);

app.use(createPinia());
app.use(router);

// ✅ Global error handler
app.config.errorHandler = (err, instance, info) => {
    console.error("Global error:", err, info);
    // Optional: Show a toast or send to a logging service
};

app.mount("#app");
