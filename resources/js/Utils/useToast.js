import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

const useToast = () => {
    // Pre-configured Toast
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    const showToast = ({ icon = "success", title = "Success!" }) => {
        Toast.fire({ icon, title });
    };

    // ✅ Full confirm modal
    const showConfirm = async (
        text = "Are you sure?",
        title = "Confirmation"
    ) => {
        return await Swal.fire({
            title,
            text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e3342f",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes",
            cancelButtonText: "Cancel",
        });
    };

    // ✅ Full error/success alert (non-toast)
    const showAlert = ({ icon = "info", title = "", text = "" }) => {
        return Swal.fire({ icon, title, text });
    };

    return {
        showToast,
        showConfirm,
        showAlert,
    };
};

export default useToast;
