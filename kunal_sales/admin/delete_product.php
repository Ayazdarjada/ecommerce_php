<?php
include 'admin_auth.php';
include '../config/db.php';
include '../config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyToken($_POST['csrf_token'])) {
    $id = (int)$_POST['id'];
    
    // Fetch image filename first
    $stmt_fetch = mysqli_prepare($conn, "SELECT image FROM products WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt_fetch, 'i', $id);
    mysqli_stmt_execute($stmt_fetch);
    $res = mysqli_stmt_get_result($stmt_fetch);
    $product = mysqli_fetch_assoc($res);
    
    // Delete DB row
    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    
    // Unlink image file if exists
    if ($product && !empty($product['image'])) {
        $image_path = "../uploads/" . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    header("Location: manage_products.php");
    exit;
}
header("Location: manage_products.php");
exit;
?>
