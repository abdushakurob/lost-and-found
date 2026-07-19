<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: /pages/dashboard.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashed]);
            $success = "Account created! You can now log in.";
        } catch (PDOException $e) {
            $error = "That email is already registered.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — recov.cv</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="auth-body">

  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-mark">R</div>
      <h1>recov<span>.cv</span></h1>
      <p>Create your account</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <a href="login.php" class="btn btn-primary btn-full">Go to Login</a>
    <?php else: ?>
    <form action="register.php" method="POST">
      <div class="form-group">
        <label class="form-label">Full name</label>
        <input class="form-control" type="text" name="name" placeholder="Jane Doe" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Email address</label>
        <input class="form-control" type="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" placeholder="Min. 6 characters" required>
      </div>
      <button class="btn btn-primary btn-full" type="submit">Create account</button>
    </form>
    <?php endif; ?>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
  </div>

</body>
</html>
