<?php
$page_title = 'Contact Inquiries - Admin Panel';
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle Message Deletion
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$del_id]);
        flash_message('success', 'Inquiry message deleted.');
    } catch (PDOException $e) {
        flash_message('danger', 'Error deleting message.');
    }
    header('Location: ' . base_url('admin/messages.php'));
    exit();
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT * FROM contacts WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();

    $total_msgs = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
} catch (PDOException $e) {
    $messages = [];
    $total_msgs = 0;
}
?>

<div class="admin-main-wrapper">
    <!-- Top Executive Header -->
    <header class="admin-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <button id="adminSidebarToggle" class="btn btn-outline-secondary d-lg-none"><i class="fas fa-bars"></i></button>
            <div>
                <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-envelope text-cyan"></i>
                    <span>Concierge Contact Messages</span>
                </h4>
                <span class="text-secondary small d-none d-sm-block">User inquiries, special requests, and luxury concierge communication logs</span>
            </div>
        </div>
        <span class="badge bg-primary bg-opacity-20 text-cyan border border-cyan px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-inbox me-1"></i> Total Inquiries: <?php echo $total_msgs; ?>
        </span>
    </header>

    <div class="admin-container">
        <?php render_flash_messages(); ?>

        <!-- Search Bar -->
        <div class="admin-table-card p-3 mb-4">
            <form action="<?php echo base_url('admin/messages.php'); ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search inquiry by sender name, email, subject, or message content..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill"><i class="fas fa-filter me-1"></i>Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo base_url('admin/messages.php'); ?>" class="btn btn-outline-secondary rounded-pill" title="Reset Search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Messages Table Card -->
        <div class="admin-table-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fas fa-envelopes-bulk text-cyan"></i>
                    <span>Inquiry Messages (<?php echo count($messages); ?> Records)</span>
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Message Ref</th>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Message Content</th>
                            <th>Timestamp</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    <i class="fas fa-envelope-open fa-3x mb-3 opacity-30"></i>
                                    <div>No concierge contact inquiries recorded.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $m): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-accent font-monospace">#MSG-<?php echo str_pad($m['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-white d-block"><?php echo htmlspecialchars($m['name']); ?></strong>
                                        <div class="small mt-0.5">
                                            <a href="mailto:<?php echo htmlspecialchars($m['email']); ?>" class="text-cyan text-decoration-none d-block">
                                                <i class="fas fa-envelope me-1 text-slate-400"></i><?php echo htmlspecialchars($m['email']); ?>
                                            </a>
                                            <a href="tel:<?php echo htmlspecialchars($m['phone']); ?>" class="text-secondary text-decoration-none">
                                                <i class="fas fa-phone me-1 text-slate-400"></i><?php echo htmlspecialchars($m['phone']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-slate-800 text-cyan border border-slate-700 px-3 py-1.5" style="font-size: 0.78rem;">
                                            <?php echo htmlspecialchars($m['subject']); ?>
                                        </span>
                                    </td>
                                    <td style="max-width: 380px;">
                                        <div class="text-slate-200 small p-2 rounded-2 border border-slate-800" style="background: rgba(15, 23, 42, 0.6); line-height: 1.5;">
                                            <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-white small"><?php echo date('M d, Y', strtotime($m['created_at'])); ?></div>
                                        <small class="text-secondary"><?php echo date('h:i A', strtotime($m['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('admin/messages.php?delete_id=' . $m['id']); ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 btn-confirm-delete" data-confirm="Delete this customer inquiry?">
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
