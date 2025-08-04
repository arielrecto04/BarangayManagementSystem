export const DocumentTypeEnum = {
    BarangayClearance: 'Barangay Clearance',
    BarangayID: 'Barangay ID',
    BusinessPermit: 'Business Permit',
    CertificateOfIndigency: 'Certificate of Indigency',
    CertificateOfResidency: 'Certificate of Residency',
    BarangayCertification: 'Barangay Certification',
    BusinessPermit: 'Business Permit',
    Other: 'Other',
    toArray : () => {
        return [
            DocumentTypeEnum.BarangayClearance,
            DocumentTypeEnum.BarangayID,
            DocumentTypeEnum.BusinessPermit,
            DocumentTypeEnum.CertificateOfIndigency,
            DocumentTypeEnum.CertificateOfResidency,
            DocumentTypeEnum.BarangayCertification,
            DocumentTypeEnum.Other,
        ]
    }
};
