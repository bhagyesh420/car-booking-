<?php
$page_title = 'System Settings - Admin Panel';
include 'includes/header.php';
include 'includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    flash_message('success', 'System configuration parameters updated successfully.');
    header('Location: ' . base_url('admin/settings.php'));
    exit();
}
?>

<div class="admin-main-wrapper">
    <!-- Top Executive Header -->
    <header class="admin-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <button id="adminSidebarToggle" class="btn btn-outline-secondary d-lg-none"><i class="fas fa-bars"></i></button>
            <div>
                <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-sliders text-cyan"></i>
                    <span>System & Platform Configuration</span>
                </h4>
                <span class="text-secondary small d-none d-sm-block">Operational defaults, tax parameters, branding identity, and central hub address</span>
            </div>
        </div>
        <span class="badge bg-emerald bg-opacity-20 text-emerald border border-emerald border-opacity-30 px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-circle-check me-1"></i> System Online
        </span>
    </header>

    <div class="admin-container">
        <?php render_flash_messages(); ?>

        <div class="admin-table-card p-4 p-md-5">
            <form action="<?php echo base_url('admin/settings.php'); ?>" method="POST">
                <!-- Section 1: Branding -->
                <div class="pb-4 mb-4 border-bottom border-dark-subtle">
                    <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-globe text-accent"></i>
                        <span>1. Platform Identity & Branding</span>
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Platform Brand Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                                <input type="text" class="form-control" value="DriveRent" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Concierge Support Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" value="support@driverent.com" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Pricing & Taxation -->
                <div class="pb-4 mb-4 border-bottom border-dark-subtle">
                    <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-receipt text-emerald"></i>
                        <span>2. Financial & Taxation Defaults</span>
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">GST Rate (%)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                <input type="number" class="form-control" value="18" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Standard Security Deposit (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-shield-halved"></i></span>
                                <input type="number" class="form-control" value="5000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency Symbol</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-indian-rupee-sign"></i></span>
                                <input type="text" class="form-control" value="₹ (INR)" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Surat Operations Hub -->
                <div class="pb-4 mb-4">
                    <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-location-dot text-rose"></i>
                        <span>3. Surat Central Operations Hub</span>
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Primary Operational Hub</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" class="form-control" value="DriveRent Executive Hub, Vesu Main Road, Surat 395007" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Doorstep Delivery Coverage</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map"></i></span>
                                <input type="text" class="form-control" value="Vesu, Piplod, Adajan, Varachha, Dumas Road" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-gradient rounded-pill px-4 py-2.5 fw-bold shadow-cyan">
                        <i class="fas fa-save me-1.5"></i> Save Configuration Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
