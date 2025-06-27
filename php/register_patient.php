<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get input values
    $fullname = trim($_POST['fullname']);
    $nic = trim($_POST['nic']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Added confirm password

    // Validate empty fields
    if (empty($fullname) || empty($nic) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Please fill all the fields.";
        header("Location: ../register.php");
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../register.php");
        exit();
    }

    // Check NIC duplication
    $stmt = $conn->prepare("SELECT id FROM vision_patients WHERE nic_num = ?");
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "NIC already registered.";
        $stmt->close();
        header("Location: ../register.php");
        exit();
    }
    $stmt->close();

    // Hash password and insert
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO vision_patients (full_name, nic_num, email_id, mobile_number, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $nic, $email, $phone, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful!";
        header("Location: ../login.php");
    } else {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header("Location: ../register.php");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../register.php");
    exit();
}
?>
