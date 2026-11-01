<?php
include 'admin_auth.php';
include '../config/db.php';
include '../config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyToken($_POST['csrf_token'])) {
    $id = $_POST['id'];
    
    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE category_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    
    header("Location: manage_categories.php");
    exit;
}
header("Location: manage_categories.php");
exit;
?>
