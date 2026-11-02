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

if (empty($name) || empty($phone) || empty($address) || empty($city) || empty($pincode)) {
    die("All fields are required.");
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
        if ($row['stock'] < $row['quantity']) throw new Exception("Insufficient stock for " . htmlspecialchars($row['product_name']));
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
        if (mysqli_stmt_affected_rows($stmt_stock) == 0) throw new Exception("Stock reduction failed for " . htmlspecialchars($item['product_name']));
    }

    $stmt_clear = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id=?");
    mysqli_stmt_bind_param($stmt_clear, 'i', $user_id);
    mysqli_stmt_execute($stmt_clear);

    mysqli_commit($conn);
    header("Location: order_success.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    include '../header.php';
    echo "<div class='container mt-5'><div class='alert alert-danger'>Checkout failed: " . htmlspecialchars($e->getMessage()) . "</div><a href='view_cart.php' class='btn btn-primary'>Back to Cart</a></div>";
    include '../footer.php';
}
?>
