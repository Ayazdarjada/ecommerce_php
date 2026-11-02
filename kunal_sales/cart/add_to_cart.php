<?php
session_start();
include '../config/db.php';
include '../config/csrf.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['id'];

// Check stock first
$stmt_stock = mysqli_prepare($conn, "SELECT stock FROM products WHERE product_id = ?");
mysqli_stmt_bind_param($stmt_stock, 'i', $product_id);
mysqli_stmt_execute($stmt_stock);
$product = mysqli_stmt_get_result($stmt_stock)->fetch_assoc();

if (!$product || $product['stock'] <= 0) {
    die("Product out of stock.");
}

/* Check if product already in cart */
$stmt_check = mysqli_prepare($conn, "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
mysqli_stmt_bind_param($stmt_check, 'ii', $user_id, $product_id);
mysqli_stmt_execute($stmt_check);
$cart_item = mysqli_stmt_get_result($stmt_check)->fetch_assoc();

if ($cart_item) {
    if ($cart_item['quantity'] < $product['stock']) {
        $stmt_update = mysqli_prepare($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($stmt_update, 'ii', $user_id, $product_id);
        mysqli_stmt_execute($stmt_update);
    } else {
        die("Cannot add more: insufficient stock.");
    }
} else {
    $stmt_insert = mysqli_prepare($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    mysqli_stmt_bind_param($stmt_insert, 'ii', $user_id, $product_id);
    mysqli_stmt_execute($stmt_insert);
}

header("Location: view_cart.php");
exit;
