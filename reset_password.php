<?php
// php/reset_password.php
session_start();
$conn = new mysqli("localhost", "root", "", "vision_point");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nic = $_POST['nic'];

    $query = "SELECT * FROM vision_patients WHERE nic_num = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['reset_nic'] = $nic;
        ?>
        <!DOCTYPE html>
        <html>
        <head>
          <title>Reset Password</title>
          <link rel="stylesheet" href="../css/style.css">
          <style>/* General body and font styles for reset password pages */
/* General body and font styles for reset password pages */
/* General body and font styles for reset password pages */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
   background: linear-gradient(to right, #222831, #948979);
    display: flex;
    justify-content: center; /* Centers horizontally */
    align-items: flex-start; /* Aligns items to the top, allowing margin-top to push down */
    min-height: 100vh; /* Full viewport height */
    color: #333; /* Default text color */
}

/* Container for the form */
.form-container {
    background: #ffffff; /* White background for the form box */
    padding: 40px;
    border-radius: 12px; /* Rounded corners */
    box-shadow: 0 10px 30px rgba(0,0,0,0.2); /* Soft shadow for depth */
    width: 100%;
    max-width: 400px; /* Max width to keep the form compact */
    text-align: center;
    box-sizing: border-box; /* Include padding in width */
    margin: 80px auto 20px auto; /* Added 80px top margin to push it down, auto for horizontal centering, 20px bottom */
}

.form-container h2 {
    color: #393E46; /* Dark text for heading */
    margin-bottom: 25px;
    font-size: 28px;
    font-weight: 700;
}

/* Input fields styling */
.form-container label {
    display: block; /* Make labels take full width */
    text-align: left; /* Align label text to the left */
    margin-bottom: 8px;
    color: #555;
    font-weight: 600;
    font-size: 15px;
}

.form-container input[type="password"],
.form-container input[type="text"] { /* In case you use text for NIC in reset_password.php */
    width: calc(100% - 24px); /* Full width minus padding */
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    color: #333;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-container input[type="password"]:focus,
.form-container input[type="text"]:focus {
    border-color: #948979; /* Highlight border on focus */
    box-shadow: 0 0 8px rgba(148, 137, 121, 0.2); /* Soft shadow on focus */
    outline: none; /* Remove default outline */
}

/* Submit button styling */
.form-container button[type="submit"] {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 8px;
    background-color: #393E46; /* Dark button background */
    color: white;
    font-weight: bold;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    margin-top: 10px; /* Space above button */
}

.form-container button[type="submit"]:hover {
    background-color: #948979; /* Lighter on hover */
    transform: translateY(-2px); /* Slight lift effect */
}

.form-container button[type="submit"]:active {
    transform: translateY(0); /* Press effect */
}

/* Message box for success/error */
.message-box {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 15px;
    text-align: center;
    font-weight: 500;
}

.message-success {
    background-color: #d4edda; /* Light green */
    color: #155724; /* Dark green */
    border: 1px solid #c3e6cb;
}

.message-error {
    background-color: #f8d7da; /* Light red */
    color: #721c24; /* Dark red */
    border: 1px solid #f5c6cb;
}


/* Back to login link */
.form-container p {
    margin-top: 20px;
}

.form-container p a {
    color: #948979; /* Link color */
    text-decoration: none;
    font-weight: 500;
}

.form-container p a:hover {
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .form-container {
        padding: 30px 20px;
        margin: 15px auto; /* Keep margin for small screens, ensure horizontal centering */
    }

    .form-container h2 {
        font-size: 24px;
    }

    .form-container input,
    .form-container button {
        font-size: 15px;
        padding: 10px;
    }
}


</style>
        </head>
        <body>
          <div class="form-container">
            <h2>Reset Your Password</h2>
            <form action="update_password.php" method="POST" onsubmit="return validatePassword();">
              <label for="password">New Password:</label>
              <input type="password" id="password" name="password" required>
              <label for="confirm_password">Confirm Password:</label>
              <input type="password" id="confirm_password" required>
              <button type="submit">Update Password</button>
            </form>
          </div>

          <script>
            function validatePassword() {
              const password = document.getElementById("password").value;
              const confirmPassword = document.getElementById("confirm_password").value;
              if (password.length < 6) {
                alert("Password must be at least 6 characters.");
                return false;
              }
              if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
              }
              return true;
            }
          </script>
        </body>
        </html>
        <?php
    } else {
        echo "<script>alert('NIC number not found.');window.location.href='forgot_password.php';</script>";
    }
}
?>