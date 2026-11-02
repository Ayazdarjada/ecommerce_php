<?php
include 'admin_header.php';
include '../config/db.php';
include '../config/csrf.php';

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
          <th>Stock</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        <?php if(mysqli_num_rows($q) > 0){ ?>
          <?php while($p = mysqli_fetch_assoc($q)){ 
              $stock_class = ($p['stock'] < 5) ? 'text-danger fw-bold' : '';
          ?>
            <tr>
              <td><?= htmlspecialchars($p['product_name']) ?></td>
              <td><?= (int)$p['category_id'] ?></td>
              <td>₹<?= number_format($p['price'], 2) ?></td>
              <td class="<?= $stock_class ?>"><?= (int)$p['stock'] ?></td>
              <td>
                <a href="edit_product.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-info">Edit</a>
                <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?= (int)$p['product_id'] ?>" data-url="delete_product.php" data-csrf="<?= generateToken() ?>" onclick="return breakRow(this)">Delete</a>
              </td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr><td colspan="5" class="text-muted text-center">No products found</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'admin_footer.php'; ?>
