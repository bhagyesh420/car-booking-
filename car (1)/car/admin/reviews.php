<?php
$page_title = 'Review Moderation - Admin Panel';
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle Review Deletion
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$del_id]);
        flash_message('success', 'Customer review deleted successfully.');
    } catch (PDOException $e) {
        flash_message('danger', 'Error deleting review.');
    }
    header('Location: ' . base_url('admin/reviews.php'));
    exit();
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT r.*, u.name as customer_name, u.email as customer_email, c.brand, c.model, c.image 
          FROM reviews r 
          JOIN users u ON r.user_id = u.id 
          JOIN cars c ON r.car_id = c.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR c.brand LIKE ? OR c.model LIKE ? OR r.review LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY r.id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll();

    $total_reviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    $avg_rating = $pdo->query("SELECT AVG(rating) FROM reviews")->fetchColumn() ?: 5.0;
    $five_star = $pdo->query("SELECT COUNT(*) FROM reviews WHERE rating = 5")->fetchColumn();
} catch (PDOException $e) {
    $reviews = [];
    $total_reviews = $five_star = 0;
    $avg_rating = 5.0;
}
?>

<div class="admin-main-wrapper">
    <!-- Top Executive Header -->
    <header class="admin-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <button id="adminSidebarToggle" class="btn btn-outline-secondary d-lg-none"><i class="fas fa-bars"></i></button>
            <div>
                <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-star-half-stroke text-amber"></i>
                    <span>Customer Feedback & Reviews Moderation</span>
                </h4>
                <span class="text-secondary small d-none d-sm-block">Customer testimonial monitoring, vehicle ratings, and sentiment moderation</span>
            </div>
        </div>
        <div class="badge bg-amber bg-opacity-20 text-amber border border-amber border-opacity-30 px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-star me-1"></i> Avg Rating: <?php echo number_format($avg_rating, 1); ?> / 5.0
        </div>
    </header>

    <div class="admin-container">
        <?php render_flash_messages(); ?>

        <!-- Quick Sentiment Ribbon -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="p-3 rounded-3 border border-slate-700 d-flex align-items-center justify-content-between" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div>
                        <span class="text-secondary small text-uppercase fw-bold" style="font-size: 0.72rem;">Total Reviews</span>
                        <div class="fs-4 fw-bold text-white"><?php echo $total_reviews; ?></div>
                    </div>
                    <div class="stat-icon-wrapper stat-icon-blue" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 rounded-3 border border-slate-700 d-flex align-items-center justify-content-between" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div>
                        <span class="text-secondary small text-uppercase fw-bold" style="font-size: 0.72rem;">Average Satisfaction</span>
                        <div class="fs-4 fw-bold text-amber d-flex align-items-center gap-1.5">
                            <?php echo number_format($avg_rating, 1); ?>
                            <i class="fas fa-star fs-6"></i>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper stat-icon-amber" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 rounded-3 border border-slate-700 d-flex align-items-center justify-content-between" style="background: linear-gradient(145deg, #1E293B, #131E30);">
                    <div>
                        <span class="text-secondary small text-uppercase fw-bold" style="font-size: 0.72rem;">5-Star Top Ratings</span>
                        <div class="fs-4 fw-bold text-emerald"><?php echo $five_star; ?></div>
                    </div>
                    <div class="stat-icon-wrapper stat-icon-emerald" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="admin-table-card p-3 mb-4">
            <form action="<?php echo base_url('admin/reviews.php'); ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by customer name, vehicle model, or review keywords..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill"><i class="fas fa-filter me-1"></i>Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo base_url('admin/reviews.php'); ?>" class="btn btn-outline-secondary rounded-pill" title="Reset Search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Reviews Table -->
        <div class="admin-table-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-star text-amber"></i>
                    <span>Reviews List (<?php echo count($reviews); ?> Submissions)</span>
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Ref ID</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Rating</th>
                            <th>Feedback Comments</th>
                            <th>Posted Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-star fa-3x mb-3 opacity-30"></i>
                                    <div>No customer reviews recorded yet.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $rev): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-accent font-monospace">#REV-<?php echo str_pad($rev['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-white d-block"><?php echo htmlspecialchars($rev['customer_name']); ?></strong>
                                        <small class="text-secondary"><?php echo htmlspecialchars($rev['customer_email']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-1 rounded bg-slate-900 border border-slate-700" style="width: 44px; height: 28px;">
                                                <img src="<?php echo get_car_image_url($rev['image']); ?>" alt="Car" style="width: 100%; height: 100%; object-fit: contain;">
                                            </div>
                                            <span class="text-white small fw-bold"><?php echo htmlspecialchars($rev['brand'] . ' ' . $rev['model']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-inline-flex align-items-center gap-1.5 bg-slate-900 px-3 py-1.5 rounded-pill border border-slate-700" style="font-size: 0.85rem;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $rev['rating'] ? 'text-amber' : 'text-slate-600'; ?>" style="font-size: 0.85rem;"></i>
                                            <?php endfor; ?>
                                            <span class="ms-1 fw-bold text-white"><?php echo $rev['rating']; ?>.0</span>
                                        </div>
                                    </td>
                                    <td style="max-width: 340px;">
                                        <div class="text-slate-200 small p-2 rounded-2 border border-slate-800" style="background: rgba(15, 23, 42, 0.6); font-style: italic;">
                                            "<?php echo htmlspecialchars($rev['review']); ?>"
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-white small"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></div>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('admin/reviews.php?delete_id=' . $rev['id']); ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 btn-confirm-delete" data-confirm="Are you sure you want to delete this customer review?">
                                            <i class="fas fa-trash-can me-1"></i>Delete
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
