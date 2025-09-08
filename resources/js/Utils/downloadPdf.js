import html2pdf from "html2pdf.js";

/**
 * Convert modern color functions to compatible formats
 * @param {HTMLElement} element - Element to process
 * @returns {HTMLElement} Cloned element with converted colors
 */
const convertModernColors = (element) => {
    const clone = element.cloneNode(true);

    // Function to convert oklch/lab colors to rgb fallbacks
    const convertColorInStyle = (styleText) => {
        return (
            styleText
                // Convert oklch to rgb approximations
                .replace(/oklch\([^)]+\)/g, (match) => {
                    // Basic oklch to rgb conversion (you may want to use a proper color conversion library)
                    // For now, we'll use common color approximations
                    const colorMap = {
                        "oklch(0.628 0.258 29.234)": "rgb(239, 68, 68)", // red-500
                        "oklch(0.7206 0.191 142.5)": "rgb(34, 197, 94)", // green-500
                        "oklch(0.6956 0.143 263.83)": "rgb(59, 130, 246)", // blue-500
                        "oklch(0.8471 0.199 83.87)": "rgb(234, 179, 8)", // yellow-500
                        // Add more mappings as needed
                    };

                    return colorMap[match] || "rgb(128, 128, 128)"; // fallback to gray
                })
                // Convert lab colors
                .replace(/lab\([^)]+\)/g, "rgb(128, 128, 128)")
                // Convert lch colors
                .replace(/lch\([^)]+\)/g, "rgb(128, 128, 128)")
        );
    };

    // Process all elements in the clone
    const allElements = clone.querySelectorAll("*");
    allElements.forEach((el) => {
        // Convert inline styles
        if (el.style.cssText) {
            el.style.cssText = convertColorInStyle(el.style.cssText);
        }

        // Convert class-based styles by creating a style attribute
        const computedStyle = window.getComputedStyle(el);
        const problematicProperties = [
            "color",
            "background-color",
            "border-color",
            "outline-color",
        ];

        problematicProperties.forEach((prop) => {
            const value = computedStyle.getPropertyValue(prop);
            if (
                value &&
                (value.includes("oklch") ||
                    value.includes("lab") ||
                    value.includes("lch"))
            ) {
                el.style.setProperty(
                    prop,
                    convertColorInStyle(value),
                    "important"
                );
            }
        });
    });

    return clone;
};

/**
 * Remove problematic CSS classes and replace with inline styles
 * @param {HTMLElement} element - Element to process
 * @returns {HTMLElement} Processed element
 */
const sanitizeForPdf = (element) => {
    const clone = convertModernColors(element);

    // Create a temporary container to work with
    const tempContainer = document.createElement("div");
    tempContainer.style.position = "absolute";
    tempContainer.style.left = "-9999px";
    tempContainer.style.top = "-9999px";
    tempContainer.style.width = "210mm"; // A4 width
    tempContainer.appendChild(clone);
    document.body.appendChild(tempContainer);

    // Force layout calculation
    tempContainer.offsetHeight;

    // Clean up
    document.body.removeChild(tempContainer);

    return clone;
};

/**
 * Download HTML content as PDF with color conversion
 * @param {HTMLElement|string} element - HTML element or selector to convert to PDF
 * @param {string} filename - Name of the PDF file to download
 * @param {object} options - Additional options for PDF generation
 * @returns {Promise} Promise that resolves when PDF is generated and downloaded
 */
export const downloadPdf = async (
    element,
    filename = "document.pdf",
    options = {}
) => {
    try {
        // Get the element
        const targetElement =
            typeof element === "string"
                ? document.querySelector(element)
                : element;

        if (!targetElement) {
            throw new Error("Target element not found");
        }

        // Sanitize the element for PDF generation
        const sanitizedElement = sanitizeForPdf(targetElement);

        // Default options with better color handling
        const defaultOptions = {
            margin: 10,
            filename: filename,
            image: {
                type: "jpeg",
                quality: 0.98,
                useCORS: true,
            },
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false,
                allowTaint: true,
                backgroundColor: "#ffffff",
                // Ignore elements that might cause issues
                ignoreElements: (element) => {
                    return (
                        element.tagName === "SCRIPT" ||
                        element.tagName === "STYLE" ||
                        element.classList.contains("no-pdf")
                    );
                },
            },
            jsPDF: {
                unit: "mm",
                format: "a4",
                orientation: "portrait",
            },
            pagebreak: {
                mode: ["avoid-all", "css", "legacy"],
            },
        };

        // Merge options
        const pdfOptions = { ...defaultOptions, ...options };

        // Generate PDF
        const worker = html2pdf().set(pdfOptions).from(sanitizedElement).save();

        return worker;
    } catch (error) {
        console.error("Error in downloadPdf:", error);
        throw error;
    }
};

/**
 * Generate PDF from HTML element and return as blob
 * @param {HTMLElement|string} element - HTML element or selector to convert to PDF
 * @param {object} options - Additional options for PDF generation
 * @returns {Promise} Promise that resolves with PDF blob
 */
export const getPdfBlob = async (element, options = {}) => {
    try {
        const targetElement =
            typeof element === "string"
                ? document.querySelector(element)
                : element;

        if (!targetElement) {
            throw new Error("Target element not found");
        }

        const sanitizedElement = sanitizeForPdf(targetElement);

        const defaultOptions = {
            image: {
                type: "jpeg",
                quality: 0.98,
                useCORS: true,
            },
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false,
                allowTaint: true,
                backgroundColor: "#ffffff",
            },
            jsPDF: {
                unit: "mm",
                format: "a4",
                orientation: "portrait",
            },
            pagebreak: {
                mode: ["avoid-all", "css", "legacy"],
            },
        };

        const pdfOptions = { ...defaultOptions, ...options };

        return new Promise((resolve, reject) => {
            html2pdf()
                .set(pdfOptions)
                .from(sanitizedElement)
                .outputPdf("blob")
                .then(resolve)
                .catch(reject);
        });
    } catch (error) {
        console.error("Error in getPdfBlob:", error);
        throw error;
    }
};
