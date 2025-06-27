<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Patient Sign-Up - Vision point</title>
  <link rel="stylesheet" href="css/register.css" />
  <script defer src="js/register_validation.js"></script>
</head>
<body class="signup-body">

  <!-- Display messages here -->
  <?php
  if (isset($_SESSION['error'])) {
      echo '<p style="color:red; text-align:center; margin: 10px 0;">' . $_SESSION['error'] . '</p>';
      unset($_SESSION['error']);
  }

  if (isset($_SESSION['success'])) {
      echo '<p style="color:green; text-align:center; margin: 10px 0;">' . $_SESSION['success'] . '</p>';
      unset($_SESSION['success']);
  }
  ?>

  <div class="dark-toggle">
    <label>
      <input type="checkbox" id="darkModeToggle" />
      🌙 Dark Mode
    </label>
  </div>

  <div class="signup-container">
    <h2>Patient Sign-Up</h2>
    <form action="php/register_patient.php" method="POST" id="signupForm" novalidate>
      <input type="text" name="fullname" id="fullname" placeholder="Full Name (e.g., John Doe)" required />
      <small id="nameError" class="error-message"></small>

      <input type="text" name="nic" id="nic" placeholder="NIC Number" required />
      <small id="nicError" class="error-message"></small>

      <input type="email" name="email" id="email" placeholder="Email Address" required />
      <small id="emailError" class="error-message"></small>

      <input type="tel" name="phone" id="phone" placeholder="MobileNumber" required />
      <small id="phoneError" class="error-message"></small>

      <input type="password" name="password" id="password" placeholder="Password" required />
      <small id="passError" class="error-message"></small>

      <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required />
      <small id="confirmError" class="error-message"></small>

      <button type="submit">Register</button>
      <p class="login-link">Already have an account? <a href="login.php">Login Here</a></p>
    </form>
  </div>
</body>
</html>