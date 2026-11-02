<?php
require 'admin_auth.php';
include '../config/db.php';
include 'admin_header.php';

$stmt = mysqli_prepare($conn, "
    SELECT orders.*, users.name 
    FROM orders 
    JOIN users ON orders.user_id = users.user_id
");
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);
?>

<div class="admin-content">
<h2>Orders</h2>

<table class="table table-bordered table-striped mt-4">
<thead class="table-light">
<tr>
    <th>Order ID</th>
    <th>User</th>
    <th>Total Amount</th>
    <th>Date</th>
</tr>
</thead>

<tbody>
<?php while($o=mysqli_fetch_assoc($q)){ ?>
<tr>
    <td><?php echo (int)$o['order_id']; ?></td>
    <td><?php echo htmlspecialchars($o['name']); ?></td>
    <td>₹<?php echo htmlspecialchars(number_format($o['total_amount'], 2)); ?></td>
    <td><?php echo htmlspecialchars($o['order_date']); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<?php include 'admin_footer.php'; ?>
