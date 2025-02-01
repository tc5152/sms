<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $student_ids = $_POST['student_ids'];
    $statuses = $_POST['status'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete existing attendance records for this class and date
        $delete_sql = "DELETE FROM attendance WHERE class_id = ? AND date = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("is", $class_id, $date);
        $delete_stmt->execute();

        // Insert new attendance records
        $insert_sql = "INSERT INTO attendance (student_id, class_id, date, status) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);

        foreach ($student_ids as $student_id) {
            $status = $statuses[$student_id];
            $insert_stmt->bind_param("iiss", $student_id, $class_id, $date, $status);
            $insert_stmt->execute();
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Attendance saved successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Error saving attendance: " . $e->getMessage();
    }

    header("Location: ../attendance.php?class_id=$class_id&date=$date");
    exit();
}
?>
