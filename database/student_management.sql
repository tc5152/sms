-- Create database
CREATE DATABASE IF NOT EXISTS student_management;
USE student_management;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher') NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    specialization VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    section VARCHAR(10),
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    registration_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    father_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    gender ENUM('male', 'female', 'other'),
    date_of_birth DATE,
    address TEXT,
    photo VARCHAR(255),
    signature VARCHAR(255),
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    class_id INT,
    status ENUM('present', 'absent', 'late') NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Results table
CREATE TABLE IF NOT EXISTS results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    subject_id INT,
    marks DECIMAL(5,2) NOT NULL,
    grade VARCHAR(2),
    exam_type VARCHAR(50) NOT NULL,
    exam_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@school.com');

-- Insert sample teachers (password: teacher123 for all)
INSERT INTO users (username, password, role, email, phone, specialization) VALUES 
('john.doe', '$2y$10$mWZkXJqfx6HRUqGYz0KJyOSxU3HVK0EnDgJ7.O8eBh7F3yJwZhJTi', 'teacher', 'john.doe@school.com', '1234567890', 'Mathematics'),
('jane.smith', '$2y$10$mWZkXJqfx6HRUqGYz0KJyOSxU3HVK0EnDgJ7.O8eBh7F3yJwZhJTi', 'teacher', 'jane.smith@school.com', '2345678901', 'Science'),
('robert.wilson', '$2y$10$mWZkXJqfx6HRUqGYz0KJyOSxU3HVK0EnDgJ7.O8eBh7F3yJwZhJTi', 'teacher', 'robert.wilson@school.com', '3456789012', 'English'),
('mary.johnson', '$2y$10$mWZkXJqfx6HRUqGYz0KJyOSxU3HVK0EnDgJ7.O8eBh7F3yJwZhJTi', 'teacher', 'mary.johnson@school.com', '4567890123', 'History');

-- Insert sample classes
INSERT INTO classes (name, section, teacher_id) VALUES 
('Class 10', 'A', 2),
('Class 10', 'B', 3),
('Class 9', 'A', 4),
('Class 9', 'B', 5);

-- Insert sample subjects
INSERT INTO subjects (name, code, class_id) VALUES 
('Mathematics', 'MATH101', 1),
('Science', 'SCI101', 1),
('English', 'ENG101', 1),
('History', 'HIS101', 1),
('Mathematics', 'MATH102', 2),
('Science', 'SCI102', 2),
('English', 'ENG102', 2),
('History', 'HIS102', 2);

-- Insert sample students
INSERT INTO students (registration_number, first_name, last_name, email, phone, gender, date_of_birth, address, class_id) VALUES 
('2024001', 'Alice', 'Brown', 'alice.brown@student.com', '5678901234', 'female', '2008-05-15', '123 Student St', 1),
('2024002', 'Bob', 'Taylor', 'bob.taylor@student.com', '6789012345', 'male', '2008-07-22', '456 School Ave', 1),
('2024003', 'Charlie', 'Davis', 'charlie.davis@student.com', '7890123456', 'male', '2008-03-10', '789 Education Rd', 2),
('2024004', 'Diana', 'Miller', 'diana.miller@student.com', '8901234567', 'female', '2008-11-30', '321 Learning Ln', 2);

-- Insert sample attendance records
INSERT INTO attendance (student_id, class_id, status, date) VALUES 
(1, 1, 'present', CURRENT_DATE),
(2, 1, 'present', CURRENT_DATE),
(3, 2, 'absent', CURRENT_DATE),
(4, 2, 'present', CURRENT_DATE);

-- Insert sample results
INSERT INTO results (student_id, subject_id, marks, grade, exam_type, exam_date) VALUES 
(1, 1, 85.50, 'A', 'Midterm', '2024-01-10'),
(1, 2, 78.75, 'B+', 'Midterm', '2024-01-10'),
(2, 1, 92.00, 'A+', 'Midterm', '2024-01-10'),
(2, 2, 88.25, 'A', 'Midterm', '2024-01-10');
