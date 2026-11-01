<?php
include 'admin_header.php';
include '../config/db.php';

$q = mysqli_query($conn, "SELECT * FROM products");
?>

<h2 class="mb-4">Manage Products</h2>

<div class="admin-table-wrapper">
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Category</th>
          <th>Price (₹)</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        <?php if(mysqli_num_rows($q) > 0){ ?>
          <?php while($p = mysqli_fetch_assoc($q)){ ?>
            <tr>
              <td><?= htmlspecialchars($p['product_name']) ?></td>
              <td><?= htmlspecialchars($p['category']) ?></td>
              <td>₹<?= number_format($p['price'], 2) ?></td>
              <td>
             <a href="delete_product.php?id=<?= $p['product_id'] ?>"
   class="btn-delete"
   data-id="<?= $p['product_id'] ?>"
   data-url="delete_product.php"
   onclick="return breakRow(this)">
   Delete
</a>


              </td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="4" class="text-muted text-center">
              No products found
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'admin_footer.php'; ?>

