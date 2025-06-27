<?php
session_start(); // Must be the very first line!

// Enable error reporting for debugging. IMPORTANT: Disable this in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone for consistent date/time handling (optional but good practice)
date_default_timezone_set('Asia/Colombo'); // Adjust to your specific timezone

// --- Database Connection ---
// CORRECTED PATH: If login_admin.php is in the 'php/' folder, and db_connect.php is also in 'php/',
// then the path should simply be 'db_connect.php'.
include 'db_connect.php'; 

// If an admin is already logged in, redirect them to the dashboard
// Check for the consistent 'user_id' and 'role'
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // CORRECTED REDIRECT: If login_admin.php is in 'php/', and admin_dashboard.php is also in 'php/',
    // then the redirect should be directly to 'admin_dashboard.php'.
    header("Location: ../php/admin_dashboard.php");
    exit();
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? ''; // Use null coalescing for safety
    $password = $_POST['password'] ?? '';

    // Basic validation for empty fields
    if (empty($username) || empty($password)) {
        echo "<script>alert('Please enter both username and password.'); window.history.back();</script>";
        exit();
    }

    // Prepare SQL statement to fetch admin user (using prepared statements for security)
    $sql = "SELECT id, username, password FROM vision_admins WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username); // 's' for string type
        $stmt->execute();
        $result = $stmt->get_result(); // Get the result set

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc(); // Fetch the user data

            // !! SECURITY WARNING !!
            // This method of matching plain text passwords is EXTREMELY INSECURE.
            // In a real application, you MUST replace this with password hashing.
            // Example: if (password_verify($password, $row['password']))
            if ($password === $row['password']) { // Plain text password comparison (INSECURE)
                // Clear any existing session data to prevent cross-login issues
                session_unset();
                session_destroy();
                session_start(); // Start a fresh session after clearing

                $_SESSION['role'] = 'admin';
                $_SESSION['user_id'] = $row['id']; // Use 'user_id' consistently for all users
                $_SESSION['username'] = $row['username'];
                $_SESSION['fullname'] = $row['username']; // Using username as fullname for admin for simplicity

                // Redirect to admin dashboard (correct path if login_admin.php is in php/)
                header("Location: ../php/admin_dashboard.php"); 
                exit(); // Crucial: Stop script execution after redirect
            } else {
                echo "<script>alert('Invalid username or password.'); window.history.back();</script>";
            }
        } else {
            // No user found or multiple users (shouldn't happen with unique username)
            echo "<script>alert('Invalid username or password.'); window.history.back();</script>";
        }
        $stmt->close(); // Close the prepared statement
    } else {
        // Error preparing the statement
        error_log("Login Admin: Error preparing statement: " . $conn->error);
        echo "<script>alert('Database error during login preparation. Please try again.'); window.history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Vision Point Eye Clinic</title>
    <!-- Link to your main style.css. If login_admin.php is in 'php/', and css is in 'css/',
         the path needs to go up one level then down into css/. -->
    <link rel="stylesheet" href="../css/style.css"> 
    <style>
        /* Minimal styling for the login form */
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            color: #003366;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: calc(100% - 20px); /* Account for padding */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .login-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-button">Login</button>
        </form>
    </div>
</body>
</html>
