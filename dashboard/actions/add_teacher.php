<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);

    // Check if username already exists
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "Username already exists!";
        header("Location: ../teachers.php");
        exit();
    }

    // Insert new teacher
    $sql = "INSERT INTO users (username, password, role, email, phone, specialization) VALUES (?, ?, 'teacher', ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $password, $email, $phone, $specialization);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Teacher added successfully!";
    } else {
        $_SESSION['error'] = "Error adding teacher: " . $conn->error;
    }

    header("Location: ../teachers.php");
    exit();
}
?>
