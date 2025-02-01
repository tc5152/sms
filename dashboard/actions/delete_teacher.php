<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $teacher_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // First update classes to remove teacher assignment
        $update_sql = "UPDATE classes SET teacher_id = NULL WHERE teacher_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $teacher_id);
        $update_stmt->execute();

        // Then delete the teacher
        $delete_sql = "DELETE FROM users WHERE id = ? AND role = 'teacher'";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $teacher_id);
        $delete_stmt->execute();

        if ($delete_stmt->affected_rows > 0) {
            $conn->commit();
            $_SESSION['success'] = "Teacher deleted successfully!";
        } else {
            throw new Exception("Teacher not found!");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error deleting teacher: " . $e->getMessage();
    }

    header("Location: ../teachers.php");
    exit();
}
?>
