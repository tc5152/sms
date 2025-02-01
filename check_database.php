<?php
require_once 'config/database.php';

echo "Checking database setup...\n\n";

// Check if the database exists
$result = $conn->query("SELECT DATABASE()");
$row = $result->fetch_row();
echo "Current database: " . $row[0] . "\n";

// Check students table structure
echo "\nChecking students table structure:\n";
$result = $conn->query("SHOW CREATE TABLE students");
if ($result) {
    $row = $result->fetch_row();
    echo $row[1] . "\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

// Check if classes table exists and has data
echo "\nChecking classes table:\n";
$result = $conn->query("SELECT * FROM classes");
if ($result) {
    echo "Number of classes: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        echo "Class ID: " . $row['id'] . ", Name: " . $row['name'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

// Check for any existing students
echo "\nChecking existing students:\n";
$result = $conn->query("SELECT * FROM students");
if ($result) {
    echo "Number of students: " . $result->num_rows . "\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>
