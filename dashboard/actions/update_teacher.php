<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = mysqli_real_escape_string($conn, $_POST['teacher_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Check if username exists for other users
    $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $username, $teacher_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "Username already exists!";
        header("Location: ../edit_teacher.php?id=" . $teacher_id);
        exit();
    }

    // Update teacher details
    if ($password) {
        $sql = "UPDATE users SET username = ?, password = ?, email = ?, phone = ?, specialization = ? 
                WHERE id = ? AND role = 'teacher'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $password, $email, $phone, $specialization, $teacher_id);
    } else {
        $sql = "UPDATE users SET username = ?, email = ?, phone = ?, specialization = ? 
                WHERE id = ? AND role = 'teacher'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $email, $phone, $specialization, $teacher_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Teacher updated successfully!";
        header("Location: ../teachers.php");
    } else {
        $_SESSION['error'] = "Error updating teacher: " . $conn->error;
        header("Location: ../edit_teacher.php?id=" . $teacher_id);
    }
    exit();
}
?>
