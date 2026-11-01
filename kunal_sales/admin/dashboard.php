<?php
include 'admin_header.php';
include '../config/db.php';

$products = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM products"))['c'];
$orders   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM orders"))['c'];
?>

<h2>Dashboard</h2>

<div class="row g-4 mt-3">
    <div class="col-md-4">
        <div class="stat-card">
            <h5>Total Products</h5>
            <h2><?= $products ?></h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <h5>Total Orders</h5>
            <h2><?= $orders ?></h2>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
