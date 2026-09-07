<?php
$page_title = 'Customer Accounts - Admin Panel';
include 'includes/header.php';
include 'includes/sidebar.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT u.*, p.city, p.driving_license, COUNT(b.id) as total_bookings, COALESCE(SUM(b.total_amount), 0) as total_spent
          FROM users u 
          LEFT JOIN user_profiles p ON u.id = p.user_id
          LEFT JOIN bookings b ON u.id = b.user_id 
          WHERE u.role = 'customer'";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " GROUP BY u.id ORDER BY u.id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();

    $total_cust_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $total_cust_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
} catch (PDOException $e) {
    $customers = [];
    $total_cust_count = $total_cust_bookings = 0;
}
?>

<div class="admin-main-wrapper">
    <!-- Top Executive Header -->
    <header class="admin-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <button id="adminSidebarToggle" class="btn btn-outline-secondary d-lg-none"><i class="fas fa-bars"></i></button>
            <div>
                <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-users-gear text-purple"></i>
                    <span>Customer Account Directory</span>
                </h4>
                <span class="text-secondary small d-none d-sm-block">Verified customer profiles, rental histories, and driver credentials</span>
            </div>
        </div>
        <span class="badge bg-purple bg-opacity-20 text-purple border border-purple border-opacity-30 px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-user-check me-1"></i> Total Users: <?php echo $total_cust_count; ?>
        </span>
    </header>

    <div class="admin-container">
        <?php render_flash_messages(); ?>

        <!-- Summary Cards Ribbon -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <div class="p-3 rounded-3 border border-slate-700 d-flex align-items-center justify-content-between" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div>
                        <span class="text-secondary small text-uppercase fw-bold" style="font-size: 0.72rem;">Verified Customer Accounts</span>
                        <div class="fs-4 fw-bold text-white"><?php echo $total_cust_count; ?></div>
                    </div>
                    <div class="stat-icon-wrapper stat-icon-purple" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="p-3 rounded-3 border border-slate-700 d-flex align-items-center justify-content-between" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div>
                        <span class="text-secondary small text-uppercase fw-bold" style="font-size: 0.72rem;">Customer Trip Orders</span>
                        <div class="fs-4 fw-bold text-cyan"><?php echo $total_cust_bookings; ?></div>
                    </div>
                    <div class="stat-icon-wrapper stat-icon-blue" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-road"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="admin-table-card p-3 mb-4">
            <form action="<?php echo base_url('admin/customers.php'); ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search customer name, email address, or mobile phone..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill"><i class="fas fa-filter me-1"></i>Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo base_url('admin/customers.php'); ?>" class="btn btn-outline-secondary rounded-pill" title="Reset Search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Customer Directory Table -->
        <div class="admin-table-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-address-book text-cyan"></i>
                    <span>Customer List (<?php echo count($customers); ?> Accounts)</span>
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Customer Name</th>
                            <th>Contact Information</th>
                            <th>Location</th>
                            <th>Driving License</th>
                            <th>Bookings Made</th>
                            <th>Account Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-users-slash fa-3x mb-3 opacity-30"></i>
                                    <div>No customer accounts match your search query.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $c): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-accent font-monospace">#USR-<?php echo str_pad($c['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                        <div class="text-secondary small" style="font-size: 0.72rem;">Joined <?php echo date('M Y', strtotime($c['created_at'])); ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle bg-purple bg-opacity-20 border border-purple border-opacity-30 d-flex align-items-center justify-content-center text-purple fw-bold" style="width: 38px; height: 38px; font-size: 0.95rem;">
                                                <?php echo strtoupper(substr($c['name'], 0, 1)); ?>
                                            </div>
                                             <div>
                                                 <strong class="text-white d-block"><?php echo htmlspecialchars($c['name']); ?></strong>
                                                 <span class="badge bg-slate-800 text-slate-300 border border-slate-700 px-2 py-0.5" style="font-size: 0.80rem;">Customer ID #<?php echo $c['id']; ?></span>
                                             </div>
                                         </div>
                                     </td>
                                     <td>
                                         <div class="text-white small">
                                             <i class="fas fa-envelope text-slate-400 me-1"></i><?php echo htmlspecialchars($c['email']); ?>
                                         </div>
                                         <div class="text-secondary small mt-0.5">
                                             <i class="fas fa-phone text-slate-400 me-1"></i><?php echo htmlspecialchars($c['phone']); ?>
                                         </div>
                                     </td>
                                     <td>
                                         <span class="small text-secondary">
                                             <i class="fas fa-location-dot text-rose me-1"></i><?php echo !empty($c['city']) ? htmlspecialchars($c['city']) : 'Surat'; ?>
                                         </span>
                                     </td>
                                     <td>
                                         <?php if (!empty($c['driving_license'])): ?>
                                             <span class="badge rounded-pill bg-cyan bg-opacity-15 text-cyan border border-cyan border-opacity-30 px-3 py-1 font-monospace" style="font-size: 0.84rem; font-weight: 600;">
                                                 <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($c['driving_license']); ?>
                                             </span>
                                         <?php else: ?>
                                             <span class="text-secondary small" style="font-size: 0.82rem;">Pending Upload</span>
                                         <?php endif; ?>
                                     </td>
                                     <td>
                                         <span class="badge rounded-pill bg-slate-800 text-cyan border border-slate-700 px-3 py-1.5 fw-bold" style="font-size: 0.85rem;">
                                             <i class="fas fa-key me-1"></i><?php echo $c['total_bookings']; ?> Trips
                                         </span>
                                     </td>
                                     <td>
                                         <span class="badge rounded-pill bg-emerald bg-opacity-20 text-emerald border border-emerald border-opacity-30 px-3 py-1.5" style="font-size: 0.85rem; font-weight: 600;">
                                             <i class="fas fa-check-circle me-1"></i>VERIFIED
                                         </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
