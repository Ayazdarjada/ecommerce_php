<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'admin_auth.php';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel - Kunal Sales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="admin-wrapper">

  <!-- SIDEBAR -->
  <div class="admin-sidebar">
    <h4>Admin Panel</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_product.php">Add Product</a>
    <a href="manage_products.php">Manage Products</a>

    <a href="add_category.php">Add Category</a>
    <a href="manage_categories.php">Manage Categories</a>

<a href="view_orders.php">View Orders</a>

  </div>

  <!-- MAIN AREA -->
  <div style="flex:1">
    <div class="admin-topbar">
      <span>Dashboard</span>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <div class="admin-content">
