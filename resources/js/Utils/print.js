// print.js
import { createApp, h } from "vue";

const defaultOptions = {
    id: "print-certificate",
    popTitle: "Certificate Print",
    timeout: 1000,
    autoClose: true,
    preserveColors: true,
    includeParentStyles: true,
    adaptivePaperSize: true,
    fullWidth: true,
};

export const printComponent = async (component, props = {}, options = {}) => {
    const opts = { ...defaultOptions, ...options };
    let container;
    let app;
    let printWindow;

    return new Promise((resolve, reject) => {
        try {
            // Create container for rendering (hidden from view)
            container = document.createElement("div");
            container.id = opts.id;
            container.style.position = "absolute";
            container.style.left = "-9999px";
            container.style.top = "-9999px";
            container.style.width = opts.fullWidth ? "100%" : "21cm";
            container.style.background = "white";
            container.style.visibility = "hidden";
            document.body.appendChild(container);

            // Create Vue app and mount component
            app = createApp({
                render: () => h(component, { ...props }),
            });

            app.mount(container);

            // Wait for component to fully render
            setTimeout(async () => {
                try {
                    // Wait for all images to load
                    const images = container.querySelectorAll("img");
                    const imagePromises = Array.from(images).map((img) => {
                        if (img.complete) return Promise.resolve();
                        return new Promise((resolve) => {
                            img.onload = resolve;
                            img.onerror = resolve; // Continue even if image fails
                            setTimeout(resolve, 2000); // Timeout after 2 seconds
                        });
                    });

                    await Promise.all(imagePromises);

                    // Create print window
                    printWindow = window.open(
                        "",
                        opts.popTitle,
                        "width=800,height=600,scrollbars=yes,resizable=yes"
                    );

                    if (!printWindow) {
                        reject(
                            new Error(
                                "Failed to open print window. Please allow popups for this site."
                            )
                        );
                        return;
                    }

                    // Build HTML content for print window
                    const htmlContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>${opts.popTitle}</title>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <style>
                                body {
                                    margin: 0;
                                    padding: 0;
                                    font-family: 'Times New Roman', serif;
                                    background: white;
                                }
                                @media print {
                                    body {
                                        margin: 0;
                                        padding: 0;
                                    }
                                    @page {
                                        margin: 0.5in;
                                        size: auto;
                                    }
                                }
                                .certificate-container {
                                    width: 100%;
                                    margin: 0 auto;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="certificate-container">
                                ${container.innerHTML}
                            </div>
                            <script>
                                window.onload = function() {
                                    window.focus();
                                    setTimeout(function() {
                                        window.print();
                                        ${
                                            opts.autoClose
                                                ? "setTimeout(() => window.close(), 500);"
                                                : ""
                                        }
                                    }, 500);
                                };
                                window.onafterprint = function() {
                                    ${opts.autoClose ? "window.close();" : ""}
                                };
                            <\/script>
                        </body>
                        </html>
                    `;

                    // Write content to print window
                    printWindow.document.write(htmlContent);
                    printWindow.document.close();

                    // Cleanup
                    setTimeout(() => {
                        try {
                            if (app) app.unmount();
                            if (container && container.parentNode) {
                                container.parentNode.removeChild(container);
                            }
                        } catch (cleanupError) {
                            console.warn("Cleanup error:", cleanupError);
                        }
                    }, 5000);

                    resolve(printWindow);
                } catch (printError) {
                    console.error("Print error:", printError);
                    reject(printError);
                }
            }, opts.timeout);
        } catch (error) {
            // Cleanup on error
            if (app) {
                try {
                    app.unmount();
                } catch (e) {
                    console.warn("App unmount error:", e);
                }
            }
            if (container && container.parentNode) {
                try {
                    container.parentNode.removeChild(container);
                } catch (e) {
                    console.warn("Container cleanup error:", e);
                }
            }
            if (printWindow && !printWindow.closed) {
                try {
                    printWindow.close();
                } catch (e) {
                    console.warn("Print window close error:", e);
                }
            }
            reject(error);
        }
    });
};
