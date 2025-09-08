import PizZip from "pizzip";
import Docxtemplater from "docxtemplater";

// Utility to load binary files (e.g. .docx template)
async function loadFile(url) {
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error(`Failed to load template: ${url}`);
    }
    return await response.arrayBuffer();
}

/**
 * Generate a certificate from a .docx template
 * @param {string} templatePath - Path to the .docx template
 * @param {object} data - Object with placeholders and values (e.g., { name: "Juan", address: "Barangay 123" })
 * @returns {Blob} - Generated docx file as a Blob
 */
export async function generateCertificate(templatePath, data) {
    try {
        // Load the template as binary
        const content = await loadFile(templatePath);

        // Initialize PizZip + Docxtemplater
        const zip = new PizZip(content);
        const doc = new Docxtemplater(zip, {
            paragraphLoop: true,
            linebreaks: true,
        });

        // Render the document with provided data
        doc.render(data);

        // Generate the document
        const out = doc.getZip().generate({
            type: "blob",
            mimeType:
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        });

        return out; // Blob (can be downloaded, previewed, or printed)
    } catch (error) {
        console.error("Certificate generation failed:", error);
        throw error;
    }
}

/**
 * Helper to download the generated file
 * @param {Blob} blob
 * @param {string} filename
 */
export function downloadCertificate(blob, filename = "certificate.docx") {
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}
