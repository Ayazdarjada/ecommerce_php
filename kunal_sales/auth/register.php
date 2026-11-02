<?php
include '../config/db.php';
include '../header.php'; // Includes session and CSRF

if (isset($_POST['register'])) {
    if (!isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?,?,?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>

<div class="auth-page">
  <div class="auth-glass">

    <h2>Create Account</h2>
    <p class="auth-sub">Join Kunal Sales & Services</p>

    <?php if (isset($error)) { ?>
      <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
      <input type="text" name="name" placeholder="Full name" required>
      <input type="email" name="email" placeholder="Email address" required>
      <input type="password" name="password" placeholder="Password (min 8 chars)" minlength="8" required>

      <button type="submit" name="register" class="auth-btn">
        Register
      </button>
    </form>

    <div class="auth-footer">
      Already have an account?
      <a href="login.php">Login</a>
    </div>

  </div>
</div>

<?php include '../footer.php'; ?>
