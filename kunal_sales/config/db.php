<?php

// Create database connection
$conn = mysqli_connect("localhost", "root", "", "kunal_sales");

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
