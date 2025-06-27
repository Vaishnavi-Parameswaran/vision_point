<?php 
session_start();
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - Vision Point</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/login.css" /> <!-- NEW: login page styles -->
</head>
<body class="login-body">
 
  <div class="login-container">
    <div class="login-tabs">
      <div class="login-tab active" data-target="patient-form">Patient Login</div>
      <div class="login-tab" data-target="admin-form">Admin Login</div>
    </div>

    <form id="patient-form" class="active" action="php/login_patient.php" method="POST">
      <input type="text" name="nic" placeholder="NIC Number" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
      <div class="login-links">
        <a href="register.php">Sign Up</a> | 
        <a href="forgot_password.php">Forgot Password?</a>
      </div>
    </form>

    <form id="admin-form" action="php/login_admin.php" method="POST">
      <input type="text" name="username" placeholder="Admin Username" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
    </form>
  </div>

  <script src="js/login.js"></script> <!-- NEW: login tab-switch logic -->
</body>
</html>