<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $registration_number = mysqli_real_escape_string($conn, $_POST['registration_number']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);

    // Validate input
    if (empty($registration_number) || empty($dob)) {
        $_SESSION['error'] = "Please fill in all fields";
        header("Location: ../exam_login.php");
        exit();
    }

    // Check if student exists and credentials are correct
    $sql = "SELECT id, first_name, last_name, class_id FROM students 
            WHERE registration_number = ? AND date_of_birth = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "Database error. Please try again.";
        header("Location: ../exam_login.php");
        exit();
    }

    $stmt->bind_param("ss", $registration_number, $dob);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        
        // Set student session variables
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
        $_SESSION['student_class'] = $student['class_id'];
        $_SESSION['is_student'] = true;

        // Redirect to student dashboard
        header("Location: ../student/dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid registration number or date of birth";
        header("Location: ../exam_login.php");
        exit();
    }
}

header("Location: ../exam_login.php");
exit();
