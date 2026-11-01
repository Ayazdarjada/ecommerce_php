<?php
session_start();
include '../config/db.php';

// Security check (admin must be logged in)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Validate product ID
if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit;
}

$product_id = intval($_GET['id']);

// 1️⃣ Get product image name
$get = mysqli_query($conn, "SELECT image FROM products WHERE product_id = $product_id");
$product = mysqli_fetch_assoc($get);

if ($product) {

    // 2️⃣ Delete image from uploads folder
    $image_path = "../uploads/" . $product['image'];
    if (!empty($product['image']) && file_exists($image_path)) {
        unlink($image_path);
    }

    // 3️⃣ Delete product from database
    mysqli_query($conn, "DELETE FROM products WHERE product_id = $product_id");
}

// 4️⃣ Redirect back
header("Location: manage_products.php");
exit;
?>
