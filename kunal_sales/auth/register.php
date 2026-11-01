<?php
session_start();
include '../config/db.php';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (name,email,password) VALUES (?,?,?)"
    );
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        $error = "Registration failed";
    }
}
?>

<?php include '../header.php'; ?>

<div class="auth-page">
  <div class="auth-glass">

    <h2>Create Account</h2>
    <p class="auth-sub">Join Kunal Sales & Services</p>

    <?php if (isset($error)) { ?>
      <div class="auth-error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="post">
      <input type="text" name="name" placeholder="Full name" required>
      <input type="email" name="email" placeholder="Email address" required>
      <input type="password" name="password" placeholder="Password" required>

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
