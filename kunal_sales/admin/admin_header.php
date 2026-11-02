<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'admin_auth.php';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#050B18">
  <title>Admin Control Center | Kunal Sales &amp; Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <h4>Kunal Control</h4>
    <a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">◈ &nbsp; Dashboard</a>
    <a class="<?= in_array($currentPage, ['add_product.php','manage_products.php','edit_product.php'], true) ? 'active' : '' ?>" href="manage_products.php">▣ &nbsp; Products</a>
    <a class="<?= in_array($currentPage, ['add_category.php','manage_categories.php'], true) ? 'active' : '' ?>" href="manage_categories.php">▤ &nbsp; Categories</a>
    <a class="<?= in_array($currentPage, ['view_orders.php','order_details.php','view_order_items.php'], true) ? 'active' : '' ?>" href="view_orders.php">⇄ &nbsp; Orders</a>
    <a href="../index.php">↗ &nbsp; View storefront</a>
  </aside>
  <div style="flex:1">
    <div class="admin-topbar">
      <span>OPERATIONS / <?= htmlspecialchars(strtoupper(str_replace('.php', '', $currentPage))) ?></span>
      <a href="logout.php" class="btn btn-danger btn-sm">Sign out</a>
    </div>
    <div class="admin-content">