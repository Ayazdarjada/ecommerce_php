<?php
include 'admin_auth.php';
include '../config/db.php';
include '../config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyToken($_POST['csrf_token'])) {
    $id = (int)($_POST['id'] ?? 0);

    // Products that appear in past orders must stay (order history needs them).
    $chk = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM order_items WHERE product_id = ?");
    mysqli_stmt_bind_param($chk, 'i', $id);
    mysqli_stmt_execute($chk);
    $used = (int)mysqli_stmt_get_result($chk)->fetch_assoc()['c'];
    if ($used > 0) {
        http_response_code(409);
        echo "Can't delete: this product is part of $used past order item(s). Set its stock to 0 instead (Edit).";
        exit;
    }

    $stmt_fetch = mysqli_prepare($conn, "SELECT image FROM products WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt_fetch, 'i', $id);
    mysqli_stmt_execute($stmt_fetch);
    $product = mysqli_stmt_get_result($stmt_fetch)->fetch_assoc();

    // Remove it from any carts first, then the product itself
    $stmt_cart = mysqli_prepare($conn, "DELETE FROM cart WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt_cart, 'i', $id);
    mysqli_stmt_execute($stmt_cart);

    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    if ($product && !empty($product['image'])) {
        $image_path = "../uploads/" . basename($product['image']);
        if (is_file($image_path)) unlink($image_path);
    }
}
header("Location: manage_products.php");
exit;
