<!-- forgot_password.php -->
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - Vision Point Eye Clinic</title>
  <!-- Link to your main style.css for general styling.
       Assumes forgot_password.php is in the root directory. -->
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Specific styles for the form container, similar to login/register pages */
    body {
      font-family: Arial, sans-serif;
      /* Using a linear gradient background for a modern look */
      background: linear-gradient(to right, #222831, #948979);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh; /* Full viewport height */
      margin: 0;
      color: #333; /* Default text color */
    }
    .form-container {
      background: #ffffff; /* White background for the form box */
      padding: 30px;
      border-radius: 12px; /* Rounded corners */
      box-shadow: 0 0 15px rgba(0,0,0,0.2); /* Soft shadow for depth */
      width: 100%;
      max-width: 450px; /* Max width for larger screens */
      text-align: center;
    }
    .form-container h2 {
      color: #222831;
      margin-bottom: 25px;
      font-size: 28px;
    }
    .form-container label {
      display: block; /* Make label take its own line */
      margin-bottom: 10px;
      font-weight: bold;
      color: #555;
      text-align: left; /* Align label text to the left */
    }
    .form-container input[type="text"] {
      width: calc(100% - 24px); /* Full width minus padding for box-sizing */
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ccc; /* Light grey border */
      border-radius: 8px; /* Rounded input fields */
      font-size: 16px;
      box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }
    .form-container button {
      width: 100%;
      padding: 12px;
      background-color: #222831;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 18px;
      cursor: pointer;
      transition: background-color 0.3s ease; /* Smooth hover effect */
    }
    .form-container button:hover {
    background-color: #948979;
    }
    .form-container p {
      margin-top: 20px;
    }
    .form-container a {
      color: #948979; /* Link color */
      text-decoration: none;
      transition: color 0.3s ease;
    }
    .form-container a:hover {
      color: #948979;
      text-decoration: underline;
    }
    /* Alert message styling (for PHP session messages) */
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        text-align: center;
        font-weight: bold;
        animation: fadeIn 0.5s ease-out; /* Simple fade-in effect */
    }
    .alert-danger {
        background-color: #f8d7da; /* Light red background */
        color: #721c24; /* Dark red text */
        border: 1px solid #f5c6cb; /* Red border */
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive adjustments */
    @media (max-width: 600px) {
        .form-container {
            margin: 20px; /* Add some margin on smaller screens */
            padding: 20px;
        }
        .form-container h2 {
            font-size: 24px;
        }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Forgot Password</h2>
    <?php 
    // Display error messages from session if any (e.g., from a previous failed attempt)
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']); // Clear the message after displaying it
    }
    ?>
    <form action="reset_password.php" method="POST" onsubmit="return validateNIC();">
      <label for="nic">Enter your registered NIC number:</label>
      <input type="text" id="nic" name="nic" placeholder="e.g., 123456789V or 199012345678" required>
      <button type="submit">Submit</button>
      <p><a href="login.php">Back to Login</a></p>
    </form>
  </div>

  <script>
    // JavaScript for NIC validation
    function validateNIC() {
      const nicInput = document.getElementById("nic");
      const nic = nicInput.value.trim();

      // Basic validation for NIC: 10 characters (old) or 12 digits (new)
      const isOldNic = nic.length === 10 && (nic.endsWith('V') || nic.endsWith('v'));
      const isNewNic = nic.length === 12 && /^\d{12}$/.test(nic);

      if (isOldNic || isNewNic) {
        return true;
      } else {
        // Use custom message box or alternative if alert() is forbidden in your environment
        alert("Please enter a valid 10-digit (ending with V/v) or 12-digit NIC number.");
        nicInput.focus(); // Keep focus on the input field for user convenience
        return false;
      }
    }
  </script>
</body>
</html>
