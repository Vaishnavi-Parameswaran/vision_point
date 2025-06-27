<?php
session_start();
include 'db_connect.php'; // Ensure this path is correct relative to this script

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Correct input name to match your form: 'nic' not 'nic_num'
    $nic = trim($_POST['nic']); // Use 'nic' to match the form input name
    $password = $_POST['password'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, full_name, password FROM vision_patients WHERE nic_num = ?");
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // *CRITICAL CHANGE: Use password_verify() for hashed passwords*
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = 'patient'; // Assuming 'patient' role for patient login
            $_SESSION['fullname'] = $row['full_name'];

            header("Location: patient_dashboard.php");
            exit();
        } else {
            // This will trigger if the entered password doesn't match the hash,
            // or if the password in the DB was NOT hashed correctly during registration.
            echo "<script>alert('Incorrect password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid NIC or account not found.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // If someone tries to access this script directly without a POST request
    header("Location: ../login.php"); // Redirect them to your login form page
    exit();
}
?>