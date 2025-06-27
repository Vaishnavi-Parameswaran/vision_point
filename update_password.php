<?php
// php/update_password.php
session_start();
$conn = new mysqli("localhost", "root", "", "vision_point");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_nic'])) {
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nic = $_SESSION['reset_nic'];

    $update = "UPDATE vision_patients SET password = ? WHERE nic_num = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ss", $newPassword, $nic);

    if ($stmt->execute()) {
        unset($_SESSION['reset_nic']);
        echo "<script>alert('Password updated successfully!');window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Failed to update password.');window.location.href='forgot_password.php';</script>";
    }
} else {
    echo "<script>alert('Invalid access.');window.location.href='login.php';</script>";
}
?>