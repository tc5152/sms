<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $teacher_id = mysqli_real_escape_string($conn, $_POST['teacher_id']);

    $sql = "INSERT INTO classes (name, section, teacher_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $section, $teacher_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Class added successfully!";
    } else {
        $_SESSION['error'] = "Error adding class: " . $conn->error;
    }

    header("Location: ../classes.php");
    exit();
}
?>
