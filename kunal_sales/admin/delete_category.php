<?php
include 'admin_auth.php';
include '../config/db.php';
include '../config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyToken($_POST['csrf_token'])) {
    $id = (int)($_POST['id'] ?? 0);

    $chk = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM products WHERE category_id = ?");
    mysqli_stmt_bind_param($chk, 'i', $id);
    mysqli_stmt_execute($chk);
    $n = (int)mysqli_stmt_get_result($chk)->fetch_assoc()['c'];
    if ($n > 0) {
        http_response_code(409);
        echo "Can't delete: $n product(s) still use this category. Move or delete them first, or just deactivate the category.";
        exit;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE category_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}
header("Location: manage_categories.php");
exit;
