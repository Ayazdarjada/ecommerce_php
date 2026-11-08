<?php
session_start();
include '../config/db.php';
include '../config/csrf.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$product_id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? ''; // 'increase', 'decrease', 'remove'

if ($action === 'increase') {
    // Check stock
    $stmt = mysqli_prepare($conn, "SELECT c.quantity, p.stock FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ? AND c.product_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    $item = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if ($item && $item['quantity'] < $item['stock']) {
        $stmt_upd = mysqli_prepare($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($stmt_upd, 'ii', $user_id, $product_id);
        mysqli_stmt_execute($stmt_upd);
    }
} elseif ($action === 'decrease') {
    $stmt = mysqli_prepare($conn, "UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ? AND quantity > 1");
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $product_id);
    mysqli_stmt_execute($stmt);
} elseif ($action === 'remove') {
    $stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $product_id);
    mysqli_stmt_execute($stmt);
}
header("Location: view_cart.php");
exit;
