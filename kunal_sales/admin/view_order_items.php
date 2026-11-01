<?php
require 'admin_auth.php';
include '../config/db.php';
include 'admin_header.php';

$q=mysqli_query($conn,"
SELECT orders.*, users.name 
FROM orders 
JOIN users ON orders.user_id = users.user_id
");
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
    <td><?php echo $o['order_id']; ?></td>
    <td><?php echo $o['name']; ?></td>
    <td>₹<?php echo $o['total_amount']; ?></td>
    <td><?php echo $o['order_date']; ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<?php include 'admin_footer.php'; ?>
