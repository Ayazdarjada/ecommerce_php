<?php
include 'admin_header.php';
include '../config/db.php';

$search = $_GET['search'] ?? '';
$sql = "SELECT orders.*, users.name FROM orders JOIN users ON orders.user_id = users.user_id WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (users.name LIKE ? OR orders.order_id = ?)";
    $s = "%$search%";
    $params[] = $s; $params[] = (int)$search;
    $types .= "si";
}

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);
?>

<div class="admin-content">
    <h2>Orders</h2>
    <form method="GET" class="mb-3">
        <input type="text" name="search" placeholder="Search by name or ID" value="<?= htmlspecialchars($search) ?>" class="form-control d-inline-block w-auto">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>Order ID</th><th>User</th><th>Phone</th><th>Status</th><th>Total</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while($o=mysqli_fetch_assoc($q)){ ?>
            <tr>
                <td>#<?= (int)$o['order_id'] ?></td>
                <td><?= htmlspecialchars($o['name']) ?></td>
                <td><?= htmlspecialchars($o['phone']) ?></td>
                <td><?= htmlspecialchars($o['status']) ?></td>
                <td>₹<?= htmlspecialchars($o['total_amount']) ?></td>
                <td><a href="order_details.php?id=<?= (int)$o['order_id'] ?>" class="btn btn-sm btn-info">View</a></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php include 'admin_footer.php'; ?>
