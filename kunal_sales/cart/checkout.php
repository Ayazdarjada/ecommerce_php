<?php
session_start();
include '../config/db.php';
include '../config/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT cart.*, products.price 
    FROM cart 
    JOIN products ON cart.product_id = products.product_id
    WHERE cart.user_id = ?
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($q) == 0) {
    header("Location: view_cart.php");
    exit;
}

$total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($q)) {
    $total += $row['price'] * $row['quantity'];
    $items[] = $row;
}

/* INSERT ORDER */
$stmt_order = mysqli_prepare($conn, "INSERT INTO orders (user_id, total_amount, order_date) VALUES (?, ?, NOW())");
mysqli_stmt_bind_param($stmt_order, 'id', $user_id, $total);
mysqli_stmt_execute($stmt_order);

$order_id = mysqli_insert_id($conn);

/* INSERT ORDER ITEMS */
$stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
foreach ($items as $item) {
    mysqli_stmt_bind_param($stmt_item, 'iidi', $order_id, $item['product_id'], $item['price'], $item['quantity']);
    mysqli_stmt_execute($stmt_item);
}

/* CLEAR CART */
$stmt_clear = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id=?");
mysqli_stmt_bind_param($stmt_clear, 'i', $user_id);
mysqli_stmt_execute($stmt_clear);

header("Location: order_success.php");
exit;
?>
