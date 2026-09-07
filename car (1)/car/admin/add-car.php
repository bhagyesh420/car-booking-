<?php
$page_title = 'Add New Vehicle - Admin Panel';
include 'includes/header.php';
include 'includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = sanitize($_POST['brand']);
    $model = sanitize($_POST['model']);
    $category = sanitize($_POST['category']);
    $year = (int)$_POST['year'];
    $price_per_day = (float)$_POST['price_per_day'];
    $price_per_hour = (float)$_POST['price_per_hour'];
    $seats = (int)$_POST['seats'];
    $transmission = sanitize($_POST['transmission']);
    $fuel_type = sanitize($_POST['fuel_type']);
    $mileage = sanitize($_POST['mileage']);
    $color = sanitize($_POST['color']);
    $description = sanitize($_POST['description']);
    $status = sanitize($_POST['status']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle Image Upload
    $image_name = 'bmw_5series.jpg'; // default fallback
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['car_image']['name'], PATHINFO_EXTENSION);
        $new_filename = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $brand . '_' . $model)) . '_' . time() . '.' . $ext;
        $target = __DIR__ . '/../assets/images/cars/' . $new_filename;
        if (move_uploaded_file($_FILES['car_image']['tmp_name'], $target)) {
            $image_name = $new_filename;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO cars 
            (brand, model, category, year, price_per_day, price_per_hour, seats, transmission, fuel_type, mileage, color, description, image, status, featured) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $brand, $model, $category, $year, $price_per_day, $price_per_hour,
            $seats, $transmission, $fuel_type, $mileage, $color, $description,
            $image_name, $status, $featured
        ]);

        flash_message('success', 'Vehicle ' . htmlspecialchars($brand . ' ' . $model) . ' added successfully!');
        header('Location: ' . base_url('admin/cars.php'));
        exit();
    } catch (PDOException $e) {
        $error = 'Database error adding vehicle: ' . $e->getMessage();
    }
}
?>

<div class="admin-main-wrapper">
    <header class="admin-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button id="adminSidebarToggle" class="btn btn-outline-secondary d-lg-none"><i class="fas fa-bars"></i></button>
            <h4 class="fw-bold mb-0 text-white"><i class="fas fa-plus-circle text-cyan me-2"></i>Add New Vehicle</h4>
        </div>
        <a href="<?php echo base_url('admin/cars.php'); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Back to Fleet</a>
    </header>

    <div class="admin-container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger mb-4 rounded-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="admin-table-card p-4 p-md-5">
            <form action="<?php echo base_url('admin/add-car.php'); ?>" method="POST" enctype="multipart/form-data">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Brand</label>
                        <input type="text" name="brand" class="form-control" placeholder="e.g. BMW" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Model</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g. M5 Competition" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Luxury">Luxury</option>
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Sports">Sports</option>
                            <option value="Economy">Economy</option>
                            <option value="Electric">Electric</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Model Year</label>
                        <input type="number" name="year" class="form-control" value="2025" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Price / Day (₹)</label>
                        <input type="number" step="0.01" name="price_per_day" class="form-control" placeholder="6999.00" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Price / Hour (₹)</label>
                        <input type="number" step="0.01" name="price_per_hour" class="form-control" placeholder="799.00" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Seats</label>
                        <select name="seats" class="form-select" required>
                            <option value="2">2 Seats</option>
                            <option value="5" selected>5 Seats</option>
                            <option value="7">7 Seats</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Transmission</label>
                        <select name="transmission" class="form-select" required>
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fuel Type</label>
                        <select name="fuel_type" class="form-select" required>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Mileage</label>
                        <input type="text" name="mileage" class="form-control" placeholder="15 KM/L" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Exterior Color</label>
                        <input type="text" name="color" class="form-control" placeholder="Phytonic Blue" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Vehicle Description</label>
                    <textarea name="description" rows="4" class="form-control" placeholder="Enter executive features, digital cockpit specs, air suspension options..." required></textarea>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="booked">Booked</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Upload Vehicle Image</label>
                        <input type="file" name="car_image" id="car_image_input" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch pb-2">
                            <input class="form-check-input" type="checkbox" name="featured" id="featuredSwitch">
                            <label class="form-check-label text-white fw-bold ms-2" for="featuredSwitch">Showcase as Featured Car</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <img id="car_image_preview" src="" alt="Preview" class="img-fluid rounded border border-slate-700 p-1 bg-slate-900 d-none" style="max-height: 140px;">
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow"><i class="fas fa-plus-circle me-2"></i>Add Vehicle to Fleet</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
