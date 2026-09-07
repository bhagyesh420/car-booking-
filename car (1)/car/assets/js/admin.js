/**
 * DriveRent - Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // Admin Sidebar Toggle for Mobile
    const sidebarToggleBtn = document.getElementById('adminSidebarToggle');
    const adminSidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggleBtn && adminSidebar) {
        sidebarToggleBtn.addEventListener('click', () => {
            adminSidebar.classList.toggle('show');
        });
    }

    // Image Upload Preview
    const carImageInput = document.getElementById('car_image_input');
    const carImagePreview = document.getElementById('car_image_preview');

    if (carImageInput && carImagePreview) {
        carImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    carImagePreview.src = event.target.result;
                    carImagePreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Delete Confirmation Helper
    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const confirmMsg = btn.getAttribute('data-confirm') || 'Are you sure you want to delete this record?';
            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        });
    });
});
