<?php
include 'admin_header.php';
include '../config/db.php';

$q = mysqli_query($conn,"
    SELECT orders.order_id, users.name, orders.total_amount, orders.order_date
    FROM orders
    JOIN users ON orders.user_id = users.user_id
");
?>

<h2 class="mb-4">Orders</h2>

<div class="admin-table-wrapper">
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>User</th>
          <th>Total Amount</th>
          <th>Date</th>
          <th>Action</th> <!-- ✅ NEW -->
        </tr>
      </thead>

      <tbody>
        <?php if(mysqli_num_rows($q) > 0){ ?>
          <?php while($o = mysqli_fetch_assoc($q)){ ?>
            <tr>
              <td>#<?= $o['order_id'] ?></td>
              <td><?= htmlspecialchars($o['name']) ?></td>
              <td>₹<?= number_format($o['total_amount'], 2) ?></td>
              <td><?= date('d M Y', strtotime($o['order_date'])) ?></td>

              <!-- ✅ DELETE BUTTON -->
              <td>
              <a href="delete_order.php?id=<?= $o['order_id'] ?>"
   class="btn-delete"
   data-id="<?= $o['order_id'] ?>"
   data-url="delete_order.php"
   onclick="return breakRow(this)">
   Delete
</a>


              </td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="5" class="text-muted text-center">
              No orders found
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'admin_footer.php'; ?>
