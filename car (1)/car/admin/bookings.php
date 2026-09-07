<?php
$page_title = 'Reservations Management - Admin Panel';
include 'includes/header.php';
include 'includes/sidebar.php';

$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, c.brand, c.model, c.image 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN cars c ON b.car_id = c.id WHERE 1=1";
$params = [];

if (!empty($filter_status)) {
    $query .= " AND b.booking_status = ?";
    $params[] = $filter_status;
}

if (!empty($search)) {
    $query .= " AND (b.booking_id LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY b.id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    // Counts for status chips
    $total_count = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $confirmed_count = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'confirmed'")->fetchColumn();
    $active_count = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'active'")->fetchColumn();
    $completed_count = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'completed'")->fetchColumn();
    $cancelled_count = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'cancelled'")->fetchColumn();
} catch (PDOException $e) {
    $bookings = [];
    $total_count = $confirmed_count = $active_count = $completed_count = $cancelled_count = 0;
}
?>

<div class="admin-main-wrapper">
    <!-- Top Executive Header -->
    <header class="admin-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <button id="adminSidebarToggle" class="btn btn-outline-secondary d-lg-none"><i class="fas fa-bars"></i></button>
            <div>
                <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-check text-accent"></i>
                    <span>Customer Reservations Management</span>
                </h4>
                <span class="text-secondary small d-none d-sm-block">Real-time trip dispatch, customer rental logs, and status approval</span>
            </div>
        </div>
        <div class="badge bg-primary bg-opacity-20 text-cyan border border-cyan px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-receipt me-1"></i> Total Bookings: <?php echo $total_count; ?>
        </div>
    </header>

    <div class="admin-container">
        <?php render_flash_messages(); ?>

        <!-- Quick Status Ribbon -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border border-slate-700" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div class="text-secondary text-uppercase fw-bold" style="font-size: 0.84rem; letter-spacing: 0.5px;">Confirmed</div>
                    <div class="fs-4 fw-bold text-cyan mt-1"><?php echo $confirmed_count; ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border border-slate-700" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div class="text-secondary text-uppercase fw-bold" style="font-size: 0.84rem; letter-spacing: 0.5px;">Active Trips</div>
                    <div class="fs-4 fw-bold text-amber mt-1"><?php echo $active_count; ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border border-slate-700" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div class="text-secondary text-uppercase fw-bold" style="font-size: 0.84rem; letter-spacing: 0.5px;">Completed</div>
                    <div class="fs-4 fw-bold text-emerald mt-1"><?php echo $completed_count; ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border border-slate-700" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div class="text-secondary text-uppercase fw-bold" style="font-size: 0.84rem; letter-spacing: 0.5px;">Cancelled</div>
                    <div class="fs-4 fw-bold text-rose mt-1"><?php echo $cancelled_count; ?></div>
                </div>
            </div>
        </div>

        <!-- Filter Navigation & Search Bar -->
        <div class="admin-table-card p-3 mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <!-- Status Pills -->
                <div class="btn-group bg-slate-900 p-1 border border-slate-700 rounded-pill">
                    <a href="<?php echo base_url('admin/bookings.php'); ?>" class="btn btn-sm rounded-pill px-3.5 py-1.5 <?php echo empty($filter_status) ? 'btn-primary text-white' : 'text-slate-400'; ?>" style="font-size: 0.85rem; font-weight: 600;">
                        All (<?php echo $total_count; ?>)
                    </a>
                    <a href="<?php echo base_url('admin/bookings.php?status=confirmed'); ?>" class="btn btn-sm rounded-pill px-3.5 py-1.5 <?php echo $filter_status === 'confirmed' ? 'btn-primary text-white' : 'text-slate-400'; ?>" style="font-size: 0.85rem; font-weight: 600;">
                        Confirmed
                    </a>
                    <a href="<?php echo base_url('admin/bookings.php?status=active'); ?>" class="btn btn-sm rounded-pill px-3.5 py-1.5 <?php echo $filter_status === 'active' ? 'btn-primary text-white' : 'text-slate-400'; ?>" style="font-size: 0.85rem; font-weight: 600;">
                        Active
                    </a>
                    <a href="<?php echo base_url('admin/bookings.php?status=completed'); ?>" class="btn btn-sm rounded-pill px-3.5 py-1.5 <?php echo $filter_status === 'completed' ? 'btn-primary text-white' : 'text-slate-400'; ?>" style="font-size: 0.85rem; font-weight: 600;">
                        Completed
                    </a>
                    <a href="<?php echo base_url('admin/bookings.php?status=cancelled'); ?>" class="btn btn-sm rounded-pill px-3.5 py-1.5 <?php echo $filter_status === 'cancelled' ? 'btn-primary text-white' : 'text-slate-400'; ?>" style="font-size: 0.85rem; font-weight: 600;">
                        Cancelled
                    </a>
                </div>

                <!-- Search Input -->
                <form action="<?php echo base_url('admin/bookings.php'); ?>" method="GET" class="d-flex gap-2" style="max-width: 320px;">
                    <?php if (!empty($filter_status)): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                    <?php endif; ?>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search ID, customer, phone..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo base_url('admin/bookings.php' . (!empty($filter_status) ? '?status=' . urlencode($filter_status) : '')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill" title="Clear Search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Bookings Table Card -->
        <div class="admin-table-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-list-check text-cyan"></i>
                    <span>Reservation Ledger (<?php echo count($bookings); ?> Records)</span>
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Rental Period</th>
                            <th>Payment</th>
                            <th>Booking Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-calendar-xmark fa-3x mb-3 opacity-30"></i>
                                    <div>No reservation records match your filter criteria.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-accent font-monospace" style="font-size: 0.95rem;"><?php echo $b['booking_id']; ?></span>
                                        <div class="text-secondary small" style="font-size: 0.72rem;"><?php echo date('d M Y, h:i A', strtotime($b['created_at'])); ?></div>
                                    </td>
                                    <td>
                                        <strong class="text-white d-block"><?php echo htmlspecialchars($b['customer_name']); ?></strong>
                                        <div class="text-secondary small">
                                            <i class="fas fa-envelope text-slate-500 me-1"></i><?php echo htmlspecialchars($b['customer_email']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-1 rounded bg-slate-900 border border-slate-700" style="width: 48px; height: 32px;">
                                                <img src="<?php echo get_car_image_url($b['image']); ?>" alt="Car" style="width: 100%; height: 100%; object-fit: contain;">
                                            </div>
                                            <div>
                                                <strong class="text-white d-block" style="font-size: 0.88rem;"><?php echo htmlspecialchars($b['brand'] . ' ' . $b['model']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-white">
                                            <i class="fas fa-calendar-day text-cyan me-1"></i><?php echo date('M d, Y', strtotime($b['pickup_date'])); ?>
                                        </div>
                                        <div class="small text-secondary mt-0.5">
                                            <i class="fas fa-arrow-right text-slate-500 me-1"></i><?php echo date('M d, Y', strtotime($b['return_date'])); ?>
                                            <span class="badge bg-slate-800 text-slate-300 ms-1 px-2 py-0.5" style="font-size: 0.82rem;"><?php echo $b['rental_days']; ?> Days</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-emerald fs-6"><?php echo format_currency($b['total_amount']); ?></div>
                                        <div class="mt-1">
                                            <span class="badge rounded-pill <?php echo $b['payment_status'] === 'paid' ? 'bg-emerald bg-opacity-20 text-emerald border border-emerald border-opacity-30' : 'bg-amber bg-opacity-20 text-amber border border-amber border-opacity-30'; ?> px-2.5 py-1" style="font-size: 0.85rem; font-weight: 600;">
                                                <i class="fas <?php echo $b['payment_status'] === 'paid' ? 'fa-check' : 'fa-clock'; ?> me-1"></i><?php echo strtoupper($b['payment_status']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo strtolower($b['booking_status']); ?>"><?php echo strtoupper($b['booking_status']); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?php echo base_url('admin/booking-details.php?id=' . $b['id']); ?>" class="btn btn-outline-info btn-sm rounded-pill px-3 py-1">
                                            <i class="fas fa-sliders me-1"></i> Manage
                                        </a>
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
