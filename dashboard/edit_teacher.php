<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: teachers.php");
    exit();
}

$teacher_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get teacher details
$sql = "SELECT * FROM users WHERE id = ? AND role = 'teacher'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    header("Location: teachers.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher - Student Management System</title>
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
                <a href="index.php" class="nav-link">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="students.php" class="nav-link">
                    <i class="fas fa-fw fa-user-graduate"></i>
                    <span>Students</span>
                </a>
                <a href="teachers.php" class="nav-link active">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Edit Teacher</h1>
                    <a href="teachers.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Teachers
                    </a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="actions/update_teacher.php" method="POST">
                            <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($teacher['username']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <div class="form-text">Leave blank to keep current password.</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($teacher['phone']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" 
                                       value="<?php echo htmlspecialchars($teacher['specialization']); ?>">
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Update Teacher</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Assigned Classes -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Assigned Classes</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $classes_sql = "SELECT c.*, COUNT(s.id) as student_count 
                                      FROM classes c 
                                      LEFT JOIN students s ON c.id = s.class_id
                                      WHERE c.teacher_id = ?
                                      GROUP BY c.id";
                        $classes_stmt = $conn->prepare($classes_sql);
                        $classes_stmt->bind_param("i", $teacher_id);
                        $classes_stmt->execute();
                        $classes_result = $classes_stmt->get_result();

                        if ($classes_result->num_rows > 0):
                        ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Section</th>
                                        <th>Students</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($class = $classes_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($class['name']); ?></td>
                                        <td><?php echo htmlspecialchars($class['section']); ?></td>
                                        <td><?php echo $class['student_count']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="mb-0">No classes assigned to this teacher.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
