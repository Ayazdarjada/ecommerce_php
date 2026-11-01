

<?php
session_start();
include '../config/db.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: ../index.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>

<?php include '../header.php'; ?>

<div class="auth-page">
  <div class="auth-glass">

    <h2>Welcome Back</h2>
    <p class="auth-sub">Login to continue</p>

    <?php if (isset($error)) { ?>
      <div class="auth-error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="post">
      <input type="email" name="email" placeholder="Email address" required>
      <input type="password" name="password" placeholder="Password" required>

      <button type="submit" name="login" class="auth-btn">
        Login
      </button>
    </form>

    <div class="auth-footer">
      New here?
      <a href="register.php">Create an account</a>
    </div>

  </div>
</div>

<?php include '../footer.php'; ?>
