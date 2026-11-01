<?php
include 'admin_auth.php';
include '../config/db.php';
include '../config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyToken($_POST['csrf_token'])) {
    $id = $_POST['id'];
    
    /* Delete order items first */
    $stmt1 = mysqli_prepare($conn, "DELETE FROM order_items WHERE order_id=?");
    mysqli_stmt_bind_param($stmt1, 'i', $id);
    mysqli_stmt_execute($stmt1);

    /* Delete order */
    $stmt2 = mysqli_prepare($conn, "DELETE FROM orders WHERE order_id=?");
    mysqli_stmt_bind_param($stmt2, 'i', $id);
    mysqli_stmt_execute($stmt2);
    
    header("Location: view_orders.php");
    exit;
}
header("Location: view_orders.php");
exit;
?>
