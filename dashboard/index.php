<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

// Get dashboard statistics
$stats = array();

// Total Students
$students_query = "SELECT COUNT(*) as total FROM students";
$result = $conn->query($students_query);
$stats['students'] = $result->fetch_assoc()['total'];

// Total Teachers
$teachers_query = "SELECT COUNT(*) as total FROM users WHERE role = 'teacher'";
$result = $conn->query($teachers_query);
$stats['teachers'] = $result->fetch_assoc()['total'];

// Total Classes
$classes_query = "SELECT COUNT(*) as total FROM classes";
$result = $conn->query($classes_query);
$stats['classes'] = $result->fetch_assoc()['total'];

// Average Attendance Rate
$attendance_query = "SELECT 
    (COUNT(CASE WHEN status = 'present' THEN 1 END) * 100.0 / COUNT(*)) as rate 
    FROM attendance 
    WHERE date = CURRENT_DATE";
$result = $conn->query($attendance_query);
$stats['attendance_rate'] = $result->fetch_assoc()['rate'] ?? 0;

// Recent Activities
$activities_query = "SELECT 
    CASE 
        WHEN a.id IS NOT NULL THEN CONCAT(s.first_name, ' ', s.last_name, ' was marked ', a.status)
        WHEN r.id IS NOT NULL THEN CONCAT(s2.first_name, ' ', s2.last_name, ' received grade ', r.grade)
    END as activity,
    CASE 
        WHEN a.id IS NOT NULL THEN a.created_at
        WHEN r.id IS NOT NULL THEN r.created_at
    END as activity_date
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id
    LEFT JOIN results r ON s.id = r.student_id
    LEFT JOIN students s2 ON r.student_id = s2.id
    WHERE a.id IS NOT NULL OR r.id IS NOT NULL
    ORDER BY activity_date DESC
    LIMIT 5";
$activities_result = $conn->query($activities_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <div class="sidebar-brand d-flex align-items-center justify-content-center">
                <i class="fas fa-graduation-cap fa-2x"></i>
                <span class="ms-2">SMS Dashboard</span>
            </div>
            <hr class="sidebar-divider bg-light">
            <div class="nav flex-column">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="students.php" class="nav-link">
                    <i class="fas fa-fw fa-user-graduate"></i>
                    <span>Students</span>
                </a>
                <a href="teachers.php" class="nav-link">
                    <i class="fas fa-fw fa-chalkboard-teacher"></i>
                    <span>Teachers</span>
                </a>
                <a href="classes.php" class="nav-link">
                    <i class="fas fa-fw fa-chalkboard"></i>
                    <span>Classes</span>
                </a>
                <a href="attendance.php" class="nav-link">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Attendance</span>
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
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['username']; ?></span>
                            <i class="fas fa-user-circle fa-fw"></i>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid px-4">
                <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card primary h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Students</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['students']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card info h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Teachers</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['teachers']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card success h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Classes</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['classes']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chalkboard fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card warning h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Today's Attendance</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo round($stats['attendance_rate'], 1); ?>%</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Activity</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($activity = $activities_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $activity['activity']; ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($activity['activity_date'])); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Teachers Overview -->
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Teachers Overview</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $teachers_overview = $conn->query("
                                    SELECT u.username, u.specialization, 
                                           COUNT(DISTINCT c.id) as class_count,
                                           COUNT(DISTINCT s.id) as student_count
                                    FROM users u
                                    LEFT JOIN classes c ON u.id = c.teacher_id
                                    LEFT JOIN students s ON c.id = s.class_id
                                    WHERE u.role = 'teacher'
                                    GROUP BY u.id
                                    LIMIT 5
                                ");
                                ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Teacher</th>
                                                <th>Specialization</th>
                                                <th>Classes</th>
                                                <th>Students</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($teacher = $teachers_overview->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $teacher['username']; ?></td>
                                                <td><?php echo $teacher['specialization']; ?></td>
                                                <td><?php echo $teacher['class_count']; ?></td>
                                                <td><?php echo $teacher['student_count']; ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
