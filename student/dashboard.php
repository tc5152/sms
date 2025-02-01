<?php
session_start();
if (!isset($_SESSION['student_id']) || !isset($_SESSION['is_student'])) {
    header("Location: ../exam_login.php");
    exit();
}
require_once '../config/database.php';

// Fetch student's details
$stmt = $conn->prepare("SELECT s.*, c.name as class_name 
                       FROM students s 
                       LEFT JOIN classes c ON s.class_id = c.id 
                       WHERE s.id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Fetch available exams for student's class
$stmt = $conn->prepare("SELECT * FROM exams 
                       WHERE class_id = ? 
                       AND status = 'active' 
                       AND start_time <= NOW() 
                       AND end_time >= NOW()");
$stmt->bind_param("i", $_SESSION['student_class']);
$stmt->execute();
$exams = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <div class="sidebar-brand d-flex align-items-center justify-content-center">
                <i class="fas fa-graduation-cap fa-2x"></i>
                <span class="ms-2">Student Portal</span>
            </div>
            <hr class="sidebar-divider bg-light">
            <div class="nav flex-column">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="exams.php" class="nav-link">
                    <i class="fas fa-fw fa-edit"></i>
                    <span>My Exams</span>
                </a>
                <a href="results.php" class="nav-link">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Results</span>
                </a>
                <a href="../auth/logout.php" class="nav-link">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?php echo htmlspecialchars($_SESSION['student_name']); ?>
                            </span>
                            <i class="fas fa-user-circle fa-fw"></i>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid px-4">
                <h1 class="h3 mb-4 text-gray-800">Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>!</h1>

                <!-- Student Information Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Student Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($student['registration_number']); ?></p>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email'] ?? 'Not provided'); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Exams -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Available Exams</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($exams->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Exam Name</th>
                                            <th>Subject</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Duration</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($exam = $exams->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($exam['name']); ?></td>
                                            <td><?php echo htmlspecialchars($exam['subject']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($exam['start_time'])); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($exam['end_time'])); ?></td>
                                            <td><?php echo $exam['duration']; ?> minutes</td>
                                            <td>
                                                <a href="take_exam.php?id=<?php echo $exam['id']; ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    Start Exam
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">No exams are currently available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
