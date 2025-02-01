<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $teacher_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Get teacher details with assigned classes and students
    $sql = "SELECT u.*, 
            GROUP_CONCAT(DISTINCT c.name) as assigned_classes,
            COUNT(DISTINCT c.id) as total_classes,
            COUNT(DISTINCT s.id) as total_students
            FROM users u 
            LEFT JOIN classes c ON u.id = c.teacher_id
            LEFT JOIN students s ON c.id = s.class_id
            WHERE u.id = ? AND u.role = 'teacher'
            GROUP BY u.id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();

    if ($teacher) {
        // Get class-wise student count
        $classes_sql = "SELECT c.name, COUNT(s.id) as student_count
                       FROM classes c
                       LEFT JOIN students s ON c.id = s.class_id
                       WHERE c.teacher_id = ?
                       GROUP BY c.id";
        $classes_stmt = $conn->prepare($classes_sql);
        $classes_stmt->bind_param("i", $teacher_id);
        $classes_stmt->execute();
        $classes_result = $classes_stmt->get_result();

        // Output teacher details
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h5>Personal Information</h5>';
        echo '<table class="table">';
        echo '<tr><th>Username:</th><td>' . htmlspecialchars($teacher['username']) . '</td></tr>';
        echo '<tr><th>Email:</th><td>' . htmlspecialchars($teacher['email']) . '</td></tr>';
        echo '<tr><th>Phone:</th><td>' . htmlspecialchars($teacher['phone']) . '</td></tr>';
        echo '<tr><th>Specialization:</th><td>' . htmlspecialchars($teacher['specialization']) . '</td></tr>';
        echo '<tr><th>Joined Date:</th><td>' . date('Y-m-d', strtotime($teacher['created_at'])) . '</td></tr>';
        echo '</table>';
        echo '</div>';

        echo '<div class="col-md-6">';
        echo '<h5>Class Information</h5>';
        echo '<table class="table">';
        echo '<tr><th>Total Classes:</th><td>' . $teacher['total_classes'] . '</td></tr>';
        echo '<tr><th>Total Students:</th><td>' . $teacher['total_students'] . '</td></tr>';
        echo '</table>';

        if ($classes_result->num_rows > 0) {
            echo '<h6>Classes and Students</h6>';
            echo '<table class="table table-sm">';
            echo '<thead><tr><th>Class</th><th>Students</th></tr></thead>';
            echo '<tbody>';
            while ($class = $classes_result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($class['name']) . '</td>';
                echo '<td>' . $class['student_count'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">Teacher not found!</div>';
    }
}
?>
