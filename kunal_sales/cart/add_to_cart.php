<?php
session_start();
include '../config/db.php';
include '../config/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && verifyToken($_POST['csrf_token'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = (int) $_POST['id'];

    /* Check if product already in cart */
    $stmt_check = mysqli_prepare($conn, "SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt_check, 'ii', $user_id, $product_id);
    mysqli_stmt_execute($stmt_check);
    $check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($check) > 0) {
        /* Increase quantity */
        $stmt_update = mysqli_prepare($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($stmt_update, 'ii', $user_id, $product_id);
        mysqli_stmt_execute($stmt_update);
    } else {
        /* Insert new item */
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        mysqli_stmt_bind_param($stmt_insert, 'ii', $user_id, $product_id);
        mysqli_stmt_execute($stmt_insert);
    }
}

header("Location: view_cart.php");
exit;
?>
