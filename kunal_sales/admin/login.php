<?php
session_start();
include '../config/db.php';
include '../config/csrf.php';

if (isset($_POST['login'])) {
    if (!isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['admin_id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050B18">
    <title>Admin Sign In | Kunal Sales &amp; Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-login-wrapper{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:radial-gradient(circle at 15% 15%,rgba(33,217,245,.15),transparent 30%),radial-gradient(circle at 90% 85%,rgba(154,104,255,.16),transparent 30%),#050B18;
        }
    </style>
</head>
<body>

<div class="admin-login-wrapper">

    <div class="auth-card">
        <span class="eyebrow">KUNAL SALES &amp; SERVICES</span>
        <h2 class="mt-3">Admin Control Center</h2>
        <p class="text-center" style="color:#91A6C4">Sign in to manage power and water products.</p>

        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">

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

            <button type="submit" name="login" class="btn btn-primary w-100">
                Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
