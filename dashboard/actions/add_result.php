<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
    $marks = mysqli_real_escape_string($conn, $_POST['marks']);
    $exam_type = mysqli_real_escape_string($conn, $_POST['exam_type']);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);

    // Calculate grade based on marks
    $grade = '';
    if ($marks >= 90) $grade = 'A+';
    elseif ($marks >= 80) $grade = 'A';
    elseif ($marks >= 70) $grade = 'B+';
    elseif ($marks >= 60) $grade = 'B';
    elseif ($marks >= 50) $grade = 'C+';
    elseif ($marks >= 40) $grade = 'C';
    else $grade = 'F';

    $sql = "INSERT INTO results (student_id, subject_id, marks, grade, exam_type, exam_date) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidsss", $student_id, $subject_id, $marks, $grade, $exam_type, $exam_date);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Result added successfully!";
    } else {
        $_SESSION['error'] = "Error adding result: " . $conn->error;
    }

    // Get class_id for redirect
    $class_query = "SELECT class_id FROM students WHERE id = ?";
    $class_stmt = $conn->prepare($class_query);
    $class_stmt->bind_param("i", $student_id);
    $class_stmt->execute();
    $class_result = $class_stmt->get_result();
    $class_id = $class_result->fetch_assoc()['class_id'];

    header("Location: ../results.php?class_id=$class_id");
    exit();
}
?>
