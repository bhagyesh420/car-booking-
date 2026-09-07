<?php
$page_title = 'Payments & Revenue Ledger - Admin Panel';
include 'includes/header.php';
include 'includes/sidebar.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT p.*, b.booking_id as b_code, u.name as customer_name, u.email as customer_email 
          FROM payments p 
          JOIN bookings b ON p.booking_id = b.id 
          JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.transaction_id LIKE ? OR b.booking_id LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();

    $total_rev = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'paid'")->fetchColumn() ?: 0;
    $total_txns = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'paid'")->fetchColumn() ?: 0;
    $avg_txn = $total_txns > 0 ? ($total_rev / $total_txns) : 0;
} catch (PDOException $e) {
    $payments = [];
    $total_rev = $total_txns = $avg_txn = 0;
}
?>

<div class="admin-main-wrapper">
    <!-- Top Executive Header -->
    <header class="admin-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <button id="adminSidebarToggle" class="btn btn-outline-secondary d-lg-none"><i class="fas fa-bars"></i></button>
            <div>
                <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-wallet text-emerald"></i>
                    <span>Financial Transactions & Revenue Ledger</span>
                </h4>
                <span class="text-secondary small d-none d-sm-block">Verified customer transaction logs, payment gateway receipts, and ledger audit</span>
            </div>
        </div>
        <div class="badge bg-emerald bg-opacity-20 text-emerald border border-emerald border-opacity-30 px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-sack-dollar me-1"></i> Total Inflow: <?php echo format_currency($total_rev); ?>
        </div>
    </header>

    <div class="admin-container">
        <?php render_flash_messages(); ?>

        <!-- Quick Financial Metrics Ribbon -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="p-3 rounded-3 border border-slate-700 d-flex align-items-center justify-content-between" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div>
                        <span class="text-secondary small text-uppercase fw-bold" style="font-size: 0.72rem;">Total Revenue Inflow</span>
                        <div class="fs-4 fw-bold text-emerald"><?php echo format_currency($total_rev); ?></div>
                    </div>
                    <div class="stat-icon-wrapper stat-icon-emerald" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 rounded-3 border border-slate-700 d-flex align-items-center justify-content-between" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div>
                        <span class="text-secondary small text-uppercase fw-bold" style="font-size: 0.72rem;">Successful Receipts</span>
                        <div class="fs-4 fw-bold text-cyan"><?php echo $total_txns; ?> Paid</div>
                    </div>
                    <div class="stat-icon-wrapper stat-icon-blue" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 rounded-3 border border-slate-700 d-flex align-items-center justify-content-between" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div>
                        <span class="text-secondary small text-uppercase fw-bold" style="font-size: 0.72rem;">Avg Rental Ticket</span>
                        <div class="fs-4 fw-bold text-white"><?php echo format_currency($avg_txn); ?></div>
                    </div>
                    <div class="stat-icon-wrapper stat-icon-purple" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="admin-table-card p-3 mb-4">
            <form action="<?php echo base_url('admin/payments.php'); ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search Transaction ID, Booking Ref (e.g. TXN, BK-), or Customer Name..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill"><i class="fas fa-filter me-1"></i>Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo base_url('admin/payments.php'); ?>" class="btn btn-outline-secondary rounded-pill" title="Reset Search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="admin-table-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-emerald"></i>
                    <span>Payment Transactions (<?php echo count($payments); ?> Logs)</span>
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Transaction Ref</th>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th>Amount Paid</th>
                            <th>Status</th>
                            <th>Processed Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-receipt fa-3x mb-3 opacity-30"></i>
                                    <div>No financial transaction logs found.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td>
                                        <span class="font-monospace text-cyan fw-bold" style="font-size: 0.92rem;"><?php echo htmlspecialchars($p['transaction_id']); ?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('admin/booking-details.php?id=' . $p['booking_id']); ?>" class="fw-bold text-accent text-decoration-none">
                                            <?php echo htmlspecialchars($p['b_code']); ?> <i class="fas fa-arrow-up-right-from-square small ms-1 opacity-75"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <strong class="text-white d-block"><?php echo htmlspecialchars($p['customer_name']); ?></strong>
                                        <small class="text-secondary"><?php echo htmlspecialchars($p['customer_email']); ?></small>
                                    </td>
                                     <td>
                                         <span class="badge rounded-pill bg-slate-800 text-white border border-slate-700 px-3 py-1.5" style="font-size: 0.85rem; font-weight: 600;">
                                             <i class="fas fa-credit-card me-1.5 text-cyan"></i><?php echo htmlspecialchars($p['payment_method']); ?>
                                         </span>
                                     </td>
                                     <td>
                                         <div class="fw-bold text-emerald fs-6"><?php echo format_currency($p['amount']); ?></div>
                                     </td>
                                     <td>
                                         <span class="badge rounded-pill <?php echo $p['payment_status'] === 'paid' ? 'bg-emerald bg-opacity-20 text-emerald border border-emerald border-opacity-30' : 'bg-rose bg-opacity-20 text-rose border border-rose border-opacity-30'; ?> px-3 py-1.5" style="font-size: 0.85rem; font-weight: 600;">
                                             <i class="fas <?php echo $p['payment_status'] === 'paid' ? 'fa-check-circle' : 'fa-triangle-exclamation'; ?> me-1"></i><?php echo strtoupper($p['payment_status']); ?>
                                         </span>
                                     </td>
                                    <td>
                                        <div class="text-white small"><?php echo date('M d, Y', strtotime($p['created_at'])); ?></div>
                                        <small class="text-secondary"><?php echo date('h:i A', strtotime($p['created_at'])); ?></small>
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
