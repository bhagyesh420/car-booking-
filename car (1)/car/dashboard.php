<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

$user = get_logged_user($pdo);
if (!$user) {
    header('Location: ' . base_url('login.php'));
    exit();
}
$profile = get_user_profile($pdo, $user['id']);
$completion = calculate_profile_completion($user, $profile);

// Fetch Booking Stats
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN booking_status IN ('confirmed', 'active', 'pending') THEN 1 ELSE 0 END) as upcoming_bookings,
    SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
    SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
    FROM bookings WHERE user_id = ?");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();

// Fetch Saved Cars Count
$saved_count = 0;
try {
    $saved_stmt = $pdo->prepare("SELECT COUNT(*) as total_saved FROM saved_cars WHERE user_id = ?");
    $saved_stmt->execute([$user['id']]);
    $saved_res = $saved_stmt->fetch();
    if ($saved_res && isset($saved_res['total_saved'])) {
        $saved_count = (int)$saved_res['total_saved'];
    }
} catch (Exception $ex) {
    $saved_count = 0;
}

// Fetch Recent Bookings (Limit 5)
$rec_stmt = $pdo->prepare("SELECT b.*, c.brand, c.model, c.category, c.image 
    FROM bookings b 
    JOIN cars c ON b.car_id = c.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC LIMIT 5");
$rec_stmt->execute([$user['id']]);
$recent_bookings = $rec_stmt->fetchAll();

// Fetch Unread Notifications (Limit 4)
$notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 4");
$notif_stmt->execute([$user['id']]);
$recent_notifs = $notif_stmt->fetchAll();

$page_title = 'User Dashboard - DriveRent';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- User Dashboard Header Banner -->
<section class="py-5 bg-secondary-dark border-bottom border-dark-subtle mt-5">
    <div class="container pt-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <?php 
                    $avatar_path = !empty($user['profile_image']) && file_exists(__DIR__ . '/assets/images/avatars/' . $user['profile_image']) 
                        ? base_url('assets/images/avatars/' . $user['profile_image']) 
                        : (!empty($user['profile_image']) && file_exists(__DIR__ . '/assets/images/' . $user['profile_image'])
                            ? base_url('assets/images/' . $user['profile_image'])
                            : base_url('assets/images/default_avatar.png'));
                    ?>
                    <img src="<?php echo $avatar_path; ?>" alt="User Avatar" class="rounded-circle border border-2 border-accent shadow-lg" style="width: 80px; height: 80px; object-fit: cover;">
                    <span class="position-absolute bottom-0 end-0 bg-success border border-dark rounded-circle p-1.5" title="Active Account"></span>
                </div>
                <div>
                    <h2 class="fw-bold mb-1 text-light">Welcome back, <span class="text-accent"><?php echo htmlspecialchars($user['name']); ?></span>! 👋</h2>
                    <p class="text-secondary mb-0 small">
                        <i class="fas fa-envelope me-1 text-accent"></i><?php echo htmlspecialchars($user['email']); ?> | 
                        <i class="fas fa-phone me-1 text-accent"></i><?php echo htmlspecialchars($user['phone']); ?> | 
                        <span class="badge bg-primary text-cyan border border-secondary px-2 py-1 ms-1">SURAT CUSTOMER</span>
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo base_url('cars.php'); ?>" class="btn btn-gradient px-4 py-2.5 btn-pill shadow-cyan"><i class="fas fa-car me-2"></i>Quick Book Car</a>
                <a href="<?php echo base_url('profile.php'); ?>" class="btn btn-outline-light px-3 py-2.5 btn-pill"><i class="fas fa-user-gear me-1"></i>Edit Profile</a>
            </div>
        </div>

        <!-- Profile Completion Card -->
        <?php if ($completion < 100): ?>
            <div class="glass-card p-3 mt-4 border-primary">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-light fw-semibold small"><i class="fas fa-tasks me-2 text-accent"></i>Profile Completion Status</span>
                            <span class="text-accent fw-bold small"><?php echo $completion; ?>% Completed</span>
                        </div>
                        <div class="progress bg-dark" style="height: 8px;">
                            <div class="progress-bar bg-accent" role="progressbar" style="width: <?php echo $completion; ?>%" aria-valuenow="<?php echo $completion; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <a href="<?php echo base_url('profile.php'); ?>" class="btn btn-sm btn-outline-primary text-light btn-pill px-3 text-nowrap"><i class="fas fa-plus-circle me-1 text-accent"></i>Complete Details</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Dashboard Main Content -->
<section class="py-5 bg-primary min-vh-100">
    <div class="container">
        <?php render_flash_messages(); ?>

        <!-- Quick Stats Overview Row -->
        <div class="row g-3 mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="glass-card p-4 h-100 reveal d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary p-3 text-accent border border-secondary">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-light"><?php echo number_format($stats['total_bookings'] ?? 0); ?></h3>
                        <span class="text-secondary small fw-semibold">Total Reservations</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="glass-card p-4 h-100 reveal d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary p-3 text-info border border-secondary">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-light"><?php echo number_format($stats['upcoming_bookings'] ?? 0); ?></h3>
                        <span class="text-secondary small fw-semibold">Upcoming / Active</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="glass-card p-4 h-100 reveal d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary p-3 text-success border border-secondary">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-light"><?php echo number_format($stats['completed_bookings'] ?? 0); ?></h3>
                        <span class="text-secondary small fw-semibold">Completed Trips</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="glass-card p-4 h-100 reveal d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary p-3 text-warning border border-secondary">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-light"><?php echo number_format($saved_count); ?></h3>
                        <span class="text-secondary small fw-semibold">Saved Vehicles</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Bookings Table / List -->
            <div class="col-lg-8">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-dark-subtle">
                        <h5 class="fw-bold mb-0 text-light"><i class="fas fa-list-check me-2 text-accent"></i>Recent Vehicle Bookings</h5>
                        <a href="<?php echo base_url('my-bookings.php'); ?>" class="btn btn-outline-secondary btn-sm btn-pill px-3">View All Bookings <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>

                    <?php if (empty($recent_bookings)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-car-side fa-3x text-secondary mb-3"></i>
                            <h6 class="fw-bold text-light">No Reservations Yet</h6>
                            <p class="text-secondary small mb-3">Browse our executive Surat fleet and book your first trip!</p>
                            <a href="<?php echo base_url('cars.php'); ?>" class="btn btn-gradient btn-sm px-4 py-2">Explore Fleet</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                                <thead>
                                    <tr class="text-secondary small text-uppercase">
                                        <th>Vehicle</th>
                                        <th>Dates</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $b): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?php echo get_car_image_url($b['image']); ?>" alt="Car" class="rounded border border-secondary" style="width: 48px; height: 36px; object-fit: cover;">
                                                    <div>
                                                        <strong class="d-block text-light" style="font-size: 0.9rem;"><?php echo htmlspecialchars($b['brand'] . ' ' . $b['model']); ?></strong>
                                                        <span class="text-secondary micro-text"><?php echo htmlspecialchars($b['booking_id']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small text-secondary">
                                                <div><i class="fas fa-calendar me-1 text-accent"></i><?php echo date('d M Y', strtotime($b['pickup_date'])); ?></div>
                                                <div class="micro-text">To <?php echo date('d M Y', strtotime($b['return_date'])); ?></div>
                                            </td>
                                            <td>
                                                <strong class="text-accent"><?php echo format_currency($b['total_amount']); ?></strong>
                                            </td>
                                            <td>
                                                <?php 
                                                $b_class = $b['booking_status'] === 'confirmed' ? 'bg-success' : 
                                                    ($b['booking_status'] === 'completed' ? 'bg-primary' : 
                                                    ($b['booking_status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark'));
                                                ?>
                                                <span class="badge <?php echo $b_class; ?> text-capitalize px-2 py-1"><?php echo $b['booking_status']; ?></span>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?php echo base_url('booking-details.php?id=' . $b['id']); ?>" class="btn btn-outline-info btn-sm btn-pill py-1 px-2.5" style="font-size: 0.78rem;">Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notifications & Quick Navigation -->
            <div class="col-lg-4">
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-dark-subtle">
                        <h5 class="fw-bold mb-0 text-light"><i class="fas fa-bell me-2 text-accent"></i>Notifications</h5>
                        <a href="<?php echo base_url('notifications.php'); ?>" class="text-accent small text-decoration-none fw-semibold">View All</a>
                    </div>

                    <?php if (empty($recent_notifs)): ?>
                        <p class="text-secondary small text-center py-3">No new notifications.</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($recent_notifs as $n): ?>
                                <div class="p-2.5 rounded bg-secondary-dark border border-secondary d-flex align-items-start gap-2">
                                    <i class="fas fa-info-circle text-accent mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-light" style="font-size: 0.85rem;"><?php echo htmlspecialchars($n['title']); ?></h6>
                                        <p class="text-secondary micro-text mb-0"><?php echo htmlspecialchars($n['message']); ?></p>
                                        <span class="text-muted" style="font-size: 0.68rem;"><?php echo date('d M, h:i A', strtotime($n['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Account Shortcuts Card -->
                <div class="glass-card p-4">
                    <h6 class="fw-bold text-light mb-3"><i class="fas fa-compass me-2 text-accent"></i>Account Navigation</h6>
                    <div class="list-group list-group-flush bg-transparent">
                        <a href="<?php echo base_url('profile.php'); ?>" class="list-group-item bg-transparent text-secondary hover-accent px-0 py-2 border-secondary d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-user-gear me-2 text-accent"></i>Profile & License Details</span>
                            <i class="fas fa-chevron-right micro-text"></i>
                        </a>
                        <a href="<?php echo base_url('saved-cars.php'); ?>" class="list-group-item bg-transparent text-secondary hover-accent px-0 py-2 border-secondary d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-heart me-2 text-warning"></i>Saved Vehicles</span>
                            <i class="fas fa-chevron-right micro-text"></i>
                        </a>
                        <a href="<?php echo base_url('my-bookings.php'); ?>" class="list-group-item bg-transparent text-secondary hover-accent px-0 py-2 border-secondary d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-calendar-check me-2 text-success"></i>Reservation History</span>
                            <i class="fas fa-chevron-right micro-text"></i>
                        </a>
                        <a href="<?php echo base_url('logout.php'); ?>" class="list-group-item bg-transparent text-danger px-0 py-2 border-0 d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-sign-out-alt me-2"></i>Sign Out Account</span>
                            <i class="fas fa-chevron-right micro-text"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
