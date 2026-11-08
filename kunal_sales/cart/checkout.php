<?php
session_start();
include '../config/db.php';
include '../config/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');
$payment = $_POST['payment_method'] ?? 'COD';
if (!in_array($payment, ['COD'], true)) $payment = 'COD';   // only COD supported

$form_error = null;
if ($name === '' || $phone === '' || $address === '' || $city === '' || $pincode === '') {
    $form_error = "All fields are required.";
} elseif (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) {
    $form_error = "Please enter a valid phone number.";
} elseif (!preg_match('/^[0-9]{6}$/', $pincode)) {
    $form_error = "Pincode must be 6 digits.";
}
if ($form_error !== null) {
    include '../header.php';
    echo "<div class='container mt-5'><div class='alert alert-danger'>" . htmlspecialchars($form_error) . "</div><a href='checkout_form.php' class='btn btn-primary'>Back to checkout</a></div>";
    include '../footer.php';
    exit;
}

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare($conn, "
        SELECT cart.*, products.price, products.stock, products.product_name
        FROM cart 
        JOIN products ON cart.product_id = products.product_id
        WHERE cart.user_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $q = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($q) == 0) throw new Exception("Cart is empty");

    $total = 0;
    $items = [];
    while ($row = mysqli_fetch_assoc($q)) {
        if ($row['stock'] < $row['quantity']) throw new Exception("Insufficient stock for " . $row['product_name']);
        $total += $row['price'] * $row['quantity'];
        $items[] = $row;
    }

    $stmt_order = mysqli_prepare($conn, "INSERT INTO orders (user_id, total_amount, order_date, phone, address, city, pincode, payment_method) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_order, 'idsssss', $user_id, $total, $phone, $address, $city, $pincode, $payment);
    mysqli_stmt_execute($stmt_order);
    $order_id = mysqli_insert_id($conn);

    $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt_stock = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?");
    
    foreach ($items as $item) {
        mysqli_stmt_bind_param($stmt_item, 'iidi', $order_id, $item['product_id'], $item['price'], $item['quantity']);
        mysqli_stmt_execute($stmt_item);

        mysqli_stmt_bind_param($stmt_stock, 'iii', $item['quantity'], $item['product_id'], $item['quantity']);
        mysqli_stmt_execute($stmt_stock);
        if (mysqli_stmt_affected_rows($stmt_stock) == 0) throw new Exception("Stock reduction failed for " . $item['product_name']);
    }

    $stmt_clear = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id=?");
    mysqli_stmt_bind_param($stmt_clear, 'i', $user_id);
    mysqli_stmt_execute($stmt_clear);

    mysqli_commit($conn);
    header("Location: order_success.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    // Business errors (stock/empty cart) are safe to show; DB errors are not.
    if ($e instanceof mysqli_sql_exception) {
        error_log('Checkout DB error: ' . $e->getMessage());
        $msg = "Something went wrong while placing your order. Please try again.";
    } else {
        $msg = $e->getMessage();
    }
    include '../header.php';
    echo "<div class='container mt-5'><div class='alert alert-danger'>Checkout failed: " . htmlspecialchars($msg) . "</div><a href='view_cart.php' class='btn btn-primary'>Back to Cart</a></div>";
    include '../footer.php';
}
?>
