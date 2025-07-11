import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

/**
 * A professional Vue 3 composable for showing dynamic toast notifications using SweetAlert2.
 * This approach centralizes toast logic, making it reusable and easy to call from any component or store.
 */
const useToast = () => {

  // Create a pre-configured SweetAlert2 instance for toasts
  // This ensures all toasts in your application have a consistent look and feel.
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end', // Position toasts at the top-right corner
    showConfirmButton: false,
    timer: 1000, // Toasts automatically close after 3 seconds
    timerProgressBar: false, // Show a progress bar for the timer
    didOpen: (toast) => {
      // Pause the timer when the user hovers over the toast
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });

  /**
   * Triggers a toast notification with dynamic content.
   * @param {object} options - The configuration for the toast.
   * @param {string} options.icon - The icon to display ('success', 'error', 'warning', 'info', 'question').
   * @param {string} options.title - The message to display in the toast.
   */
  const showToast = ({ icon = 'success', title = 'Success!' }) => {
    Toast.fire({
      icon: icon,
      title: title
    });
  };

  // Expose the showToast function to be used throughout the application
  return {
    showToast,
  };
}


export default useToast;

