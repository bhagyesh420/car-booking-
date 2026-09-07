<?php
$page_title = 'Admin Executive Dashboard';
include 'includes/header.php';
include 'includes/sidebar.php';

// Calculate Dashboard Metrics
try {
    $total_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
    $avail_cars = $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'available'")->fetchColumn();
    $booked_cars = $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'booked'")->fetchColumn();
    $total_cust = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $total_book = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $total_rev = $pdo->query("SELECT SUM(total_amount) FROM bookings WHERE payment_status = 'paid'")->fetchColumn() ?: 0;

    // Fetch Recent 6 Bookings
    $recent_stmt = $pdo->query("SELECT b.*, u.name as customer_name, c.brand, c.model 
                                FROM bookings b 
                                JOIN users u ON b.user_id = u.id 
                                JOIN cars c ON b.car_id = c.id 
                                ORDER BY b.id DESC LIMIT 6");
    $recent_bookings = $recent_stmt->fetchAll();
} catch (PDOException $e) {
    $total_cars = $avail_cars = $booked_cars = $total_cust = $total_book = $total_rev = 0;
    $recent_bookings = [];
}
?>

<div class="admin-main-wrapper">
    <!-- Top Executive Header -->
    <header class="admin-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button id="adminSidebarToggle" class="btn btn-outline-secondary d-lg-none"><i class="fas fa-bars"></i></button>
            <div>
                <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-gauge-high text-cyan"></i>
                    <span>Executive Dashboard</span>
                </h4>
                <span class="text-secondary small d-none d-sm-block">Surat Central Fleet & Operations Hub</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill border border-slate-700" style="background: rgba(15, 23, 42, 0.85);">
                <div class="rounded-circle bg-primary text-cyan fw-bold d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem; background: rgba(14, 165, 233, 0.15) !important;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="text-white small fw-bold line-height-1" style="font-size: 0.82rem;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="text-cyan" style="font-size: 0.68rem; font-weight: 600;">SUPER ADMIN</div>
                </div>
            </div>
            <a href="<?php echo base_url('admin/logout.php'); ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5">
                <i class="fas fa-right-from-bracket me-1"></i> Logout
            </a>
        </div>
    </header>

    <div class="admin-container">
        <?php render_flash_messages(); ?>

        <!-- Stat Cards Grid -->
        <div class="row g-4 mb-4">
            <!-- Total Fleet -->
            <div class="col-xl-3 col-md-6">
                <div class="admin-stat-card stat-card-blue">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1 overflow-hidden">
                            <span class="text-uppercase fw-bold text-accent" style="letter-spacing: 0.6px; font-size: 0.84rem;">Total Fleet Vehicles</span>
                            <div class="admin-stat-number"><?php echo number_format($total_cars); ?></div>
                            <span class="badge rounded-pill bg-emerald bg-opacity-15 text-emerald border border-emerald border-opacity-35 px-3 py-1.5" style="font-size: 0.86rem; font-weight: 600;">
                                <i class="fas fa-circle-check me-1.5"></i><?php echo $avail_cars; ?> Ready to Rent
                            </span>
                        </div>
                        <div class="stat-icon-wrapper stat-icon-blue">
                            <i class="fas fa-car-rear"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservations -->
            <div class="col-xl-3 col-md-6">
                <div class="admin-stat-card stat-card-amber">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1 overflow-hidden">
                            <span class="text-uppercase fw-bold text-amber" style="letter-spacing: 0.6px; font-size: 0.84rem;">Active Reservations</span>
                            <div class="admin-stat-number"><?php echo number_format($total_book); ?></div>
                            <span class="badge rounded-pill bg-amber bg-opacity-15 text-amber border border-amber border-opacity-35 px-3 py-1.5" style="font-size: 0.86rem; font-weight: 600;">
                                <i class="fas fa-key me-1.5"></i><?php echo $booked_cars; ?> On Trip Right Now
                            </span>
                        </div>
                        <div class="stat-icon-wrapper stat-icon-amber">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="col-xl-3 col-md-6">
                <div class="admin-stat-card stat-card-emerald">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1 overflow-hidden">
                            <span class="text-uppercase fw-bold text-emerald" style="letter-spacing: 0.6px; font-size: 0.84rem;">Total Revenue</span>
                            <div class="admin-stat-number text-emerald" style="font-size: clamp(1.45rem, 1.8vw, 1.95rem);"><?php echo format_currency($total_rev); ?></div>
                            <span class="badge rounded-pill bg-emerald bg-opacity-15 text-emerald border border-emerald border-opacity-35 px-3 py-1.5" style="font-size: 0.86rem; font-weight: 600;">
                                <i class="fas fa-shield-halved me-1.5"></i>Verified Payments
                            </span>
                        </div>
                        <div class="stat-icon-wrapper stat-icon-emerald">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registered Users -->
            <div class="col-xl-3 col-md-6">
                <div class="admin-stat-card stat-card-purple">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1 overflow-hidden">
                            <span class="text-uppercase fw-bold text-purple" style="letter-spacing: 0.6px; font-size: 0.84rem;">Customer Accounts</span>
                            <div class="admin-stat-number"><?php echo number_format($total_cust); ?></div>
                            <span class="badge rounded-pill bg-cyan bg-opacity-15 text-cyan border border-cyan border-opacity-35 px-3 py-1.5" style="font-size: 0.86rem; font-weight: 600;">
                                <i class="fas fa-user-shield me-1.5"></i><?php echo $total_cust; ?> Verified Profiles
                            </span>
                        </div>
                        <div class="stat-icon-wrapper stat-icon-purple">
                            <i class="fas fa-users-gear"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Fleet Status Bar -->
        <div class="row g-4 mb-4">
            <!-- Quick Actions -->
            <div class="col-lg-8">
                <div class="admin-table-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                            <i class="fas fa-bolt-lightning text-amber"></i>
                            <span>Admin Quick Actions</span>
                        </h5>
                        <span class="badge bg-primary bg-opacity-20 text-cyan border border-cyan border-opacity-35 px-3.5 py-2 rounded-pill" style="font-size: 0.88rem; font-weight: 600;">
                            <i class="fas fa-city me-1.5"></i> Surat Operations Console
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <a href="<?php echo base_url('admin/add-car.php'); ?>" class="admin-quick-action-btn">
                                <div class="quick-action-icon" style="background: rgba(14, 165, 233, 0.15); color: #38BDF8; border: 1px solid rgba(14, 165, 233, 0.35);">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div>
                                    <div class="action-title">Add Vehicle</div>
                                    <div class="action-desc">Create fleet listing</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="<?php echo base_url('admin/bookings.php'); ?>" class="admin-quick-action-btn">
                                <div class="quick-action-icon" style="background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.35);">
                                    <i class="fas fa-calendar-days"></i>
                                </div>
                                <div>
                                    <div class="action-title">Manage Trips</div>
                                    <div class="action-desc">Reservations & status</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="<?php echo base_url('admin/payments.php'); ?>" class="admin-quick-action-btn">
                                <div class="quick-action-icon" style="background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <div class="action-title">Revenue Ledger</div>
                                    <div class="action-desc">Invoices & earnings</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="<?php echo base_url('admin/messages.php'); ?>" class="admin-quick-action-btn">
                                <div class="quick-action-icon" style="background: rgba(245, 158, 11, 0.15); color: #FBBF24; border: 1px solid rgba(245, 158, 11, 0.35);">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div>
                                    <div class="action-title">Inquiries</div>
                                    <div class="action-desc">Customer messages</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fleet Availability -->
            <div class="col-lg-4">
                <div class="admin-table-card p-4 h-100">
                    <h5 class="fw-bold mb-3 text-white d-flex align-items-center gap-2">
                        <i class="fas fa-chart-pie text-cyan"></i>
                        <span>Fleet Availability</span>
                    </h5>

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary fw-medium">Available Fleet</span>
                        <strong class="text-emerald"><?php echo $avail_cars; ?> Cars (<?php echo $total_cars > 0 ? round(($avail_cars / $total_cars) * 100) : 0; ?>%)</strong>
                    </div>
                    <div class="progress mb-3" style="height: 8px; background: rgba(255,255,255,0.08);">
                        <div class="progress-bar" style="width: <?php echo $total_cars > 0 ? ($avail_cars / $total_cars) * 100 : 0; ?>%; background: linear-gradient(90deg, #10B981, #06B6D4);"></div>
                    </div>

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary fw-medium">Currently Rented</span>
                        <strong class="text-amber"><?php echo $booked_cars; ?> Cars (<?php echo $total_cars > 0 ? round(($booked_cars / $total_cars) * 100) : 0; ?>%)</strong>
                    </div>
                    <div class="progress mb-3" style="height: 8px; background: rgba(255,255,255,0.08);">
                        <div class="progress-bar" style="width: <?php echo $total_cars > 0 ? ($booked_cars / $total_cars) * 100 : 0; ?>%; background: linear-gradient(90deg, #F59E0B, #EF4444);"></div>
                    </div>

                    <div class="p-3 rounded-3 mt-3 d-flex align-items-center gap-3 border border-slate-700" style="background: rgba(15, 23, 42, 0.7);">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(14, 165, 233, 0.15); color: #38BDF8;">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white small">100% Doorstep Delivery Active</div>
                            <div class="text-secondary" style="font-size: 0.74rem;">Live across Vesu, Piplod, Adajan & Varachha.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reservations Table -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="admin-table-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                            <i class="fas fa-clock-rotate-left text-accent"></i>
                            <span>Recent Rental Reservations</span>
                        </h5>
                        <a href="<?php echo base_url('admin/bookings.php'); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            View All Bookings <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Pickup Date</th>
                                    <th>Total Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_bookings)): ?>
                                    <tr><td colspan="8" class="text-center py-4 text-secondary">No booking transactions recorded yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recent_bookings as $b): ?>
                                        <tr>
                                            <td class="fw-bold text-accent"><?php echo $b['booking_id']; ?></td>
                                            <td><i class="fas fa-user-circle text-secondary me-1"></i><?php echo htmlspecialchars($b['customer_name']); ?></td>
                                            <td><i class="fas fa-car text-cyan me-1"></i><?php echo htmlspecialchars($b['brand'] . ' ' . $b['model']); ?></td>
                                            <td><i class="fas fa-calendar me-1 text-secondary"></i><?php echo date('M d, Y', strtotime($b['pickup_date'])); ?></td>
                                            <td class="fw-bold text-emerald"><?php echo format_currency($b['total_amount']); ?></td>
                                            <td>
                                                <span class="badge rounded-pill <?php echo $b['payment_status'] === 'paid' ? 'bg-emerald bg-opacity-20 text-emerald border border-emerald border-opacity-30' : 'bg-amber bg-opacity-20 text-amber border border-amber border-opacity-30'; ?> px-2.5 py-1">
                                                    <i class="fas <?php echo $b['payment_status'] === 'paid' ? 'fa-check' : 'fa-clock'; ?> me-1"></i><?php echo strtoupper($b['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge-status <?php echo strtolower($b['booking_status']); ?>"><?php echo strtoupper($b['booking_status']); ?></span>
                                            </td>
                                            <td>
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
    </div>
</div>

<?php include 'includes/footer.php'; ?>
