<?php
session_start();
require_once '../../config/database.php';

// Function to validate file upload
function validateFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
    if ($file['error'] !== 0) {
        return null;
    }

    // Check file size (2MB max)
    if ($file['size'] > $maxSize) {
        throw new Exception("File size too large. Maximum size allowed is " . ($maxSize / 1048576) . "MB");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        throw new Exception("Invalid file type. Allowed types: " . implode(", ", $allowedTypes));
    }

    return $ext;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        $required_fields = ['registration_number', 'first_name', 'last_name', 'father_name', 'date_of_birth', 'class_id'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields. Missing: " . str_replace('_', ' ', $field));
            }
        }

        // Sanitize input
        $registration_number = mysqli_real_escape_string($conn, trim($_POST['registration_number']));
        $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
        $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
        $father_name = mysqli_real_escape_string($conn, trim($_POST['father_name']));
        $date_of_birth = mysqli_real_escape_string($conn, trim($_POST['date_of_birth']));
        $email = !empty($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : null;
        $phone = !empty($_POST['phone']) ? mysqli_real_escape_string($conn, trim($_POST['phone'])) : null;
        $class_id = intval($_POST['class_id']);

        // Validate email if provided
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate phone if provided
        if ($phone && !preg_match("/^[0-9\-\(\)\/\+\s]*$/", $phone)) {
            throw new Exception("Invalid phone number format");
        }

        // Check if class exists
        $class_check = $conn->prepare("SELECT id FROM classes WHERE id = ?");
        $class_check->bind_param("i", $class_id);
        $class_check->execute();
        if ($class_check->get_result()->num_rows === 0) {
            throw new Exception("Selected class does not exist");
        }
        $class_check->close();

        // Handle photo upload
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== 4) {
            try {
                $photo_ext = validateFile($_FILES['photo']);
                if ($photo_ext) {
                    $photo_name = uniqid('photo_') . '.' . $photo_ext;
                    $photo_path = '../../uploads/photos/' . $photo_name;
                    
                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                        throw new Exception("Failed to save photo file");
                    }
                    $photo = $photo_name;
                }
            } catch (Exception $e) {
                throw new Exception("Photo Error: " . $e->getMessage());
            }
        }

        // Handle signature upload
        $signature = null;
        if (isset($_FILES['signature']) && $_FILES['signature']['error'] !== 4) {
            try {
                $signature_ext = validateFile($_FILES['signature']);
                if ($signature_ext) {
                    $signature_name = uniqid('sign_') . '.' . $signature_ext;
                    $signature_path = '../../uploads/signatures/' . $signature_name;
                    
                    if (!move_uploaded_file($_FILES['signature']['tmp_name'], $signature_path)) {
                        throw new Exception("Failed to save signature file");
                    }
                    $signature = $signature_name;
                }
            } catch (Exception $e) {
                throw new Exception("Signature Error: " . $e->getMessage());
            }
        }

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Check if registration number already exists
            $check_stmt = $conn->prepare("SELECT id FROM students WHERE registration_number = ?");
            $check_stmt->bind_param("s", $registration_number);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Registration number already exists!");
            }
            $check_stmt->close();

            // Insert new student
            $sql = "INSERT INTO students (registration_number, first_name, last_name, father_name, date_of_birth, email, phone, class_id, photo, signature) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Database Error: " . $conn->error);
            }

            $stmt->bind_param("sssssssis", 
                $registration_number, 
                $first_name, 
                $last_name, 
                $father_name,
                $date_of_birth, 
                $email, 
                $phone, 
                $class_id, 
                $photo, 
                $signature
            );

            if (!$stmt->execute()) {
                throw new Exception("Error adding student: " . $stmt->error);
            }

            $stmt->close();
            $conn->commit();
            $_SESSION['success'] = "Student added successfully!";

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        
        // Clean up uploaded files if there was an error
        if (isset($photo) && file_exists('../../uploads/photos/' . $photo)) {
            unlink('../../uploads/photos/' . $photo);
        }
        if (isset($signature) && file_exists('../../uploads/signatures/' . $signature)) {
            unlink('../../uploads/signatures/' . $signature);
        }
    }
    
    header("Location: ../students.php");
    exit();
}

header("Location: ../students.php");
exit();
