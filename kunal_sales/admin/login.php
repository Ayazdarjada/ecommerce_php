<?php
session_start();
include '../config/db.php';

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- ✅ LOGIN ALIGNMENT FIX -->
    <style>
        .admin-login-wrapper{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg,#0284C7,#0F172A);
        }
    </style>
</head>
<body>

<div class="admin-login-wrapper">

    <div class="auth-card">
        <h2>Admin Login</h2>

        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form method="post">

            <input
                type="text"
                name="username"
                class="form-control mb-3"
                placeholder="Admin Username"
                required
            >

            <input
                type="password"
                name="password"
                class="form-control mb-3"
                placeholder="Password"
                required
            >

            <button type="submit" name="login" class="btn btn-dark w-100">
                Login
            </button>

        </form>
    </div>

</div>

</body>
</html>
