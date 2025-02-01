<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

// Get selected class and date
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch classes for dropdown
$classes_query = "SELECT id, name, section FROM classes ORDER BY name";
$classes_result = $conn->query($classes_query);

// Fetch attendance if class is selected
if ($selected_class) {
    $attendance_query = "SELECT s.id, s.registration_number, s.first_name, s.last_name,
                        COALESCE(a.status, 'absent') as status
                        FROM students s
                        LEFT JOIN attendance a ON s.id = a.student_id 
                        AND a.date = ? AND a.class_id = ?
                        WHERE s.class_id = ?
                        ORDER BY s.first_name";
    
    $stmt = $conn->prepare($attendance_query);
    $stmt->bind_param("sii", $selected_date, $selected_class, $selected_class);
    $stmt->execute();
    $attendance_result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Student Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
                <a href="classes.php" class="nav-link">
                    <i class="fas fa-fw fa-chalkboard"></i>
                    <span>Classes</span>
                </a>
                <a href="attendance.php" class="nav-link active">
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
                <h1 class="h3 mb-4 text-gray-800">Attendance Management</h1>

                <!-- Filter Form -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label for="class_id" class="form-label">Select Class</label>
                                <select class="form-select" id="class_id" name="class_id" required>
                                    <option value="">Choose Class...</option>
                                    <?php while($class = $classes_result->fetch_assoc()): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo ($selected_class == $class['id']) ? 'selected' : ''; ?>>
                                        <?php echo $class['name'] . ' - ' . $class['section']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date" class="form-label">Select Date</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?php echo $selected_date; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Load Attendance</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($selected_class && isset($attendance_result)): ?>
                <!-- Attendance Form -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="actions/save_attendance.php" method="POST">
                            <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                            <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Reg. No.</th>
                                            <th>Student Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($student = $attendance_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $student['registration_number']; ?></td>
                                            <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <input type="hidden" name="student_ids[]" value="<?php echo $student['id']; ?>">
                                                    <input type="radio" class="btn-check" name="status[<?php echo $student['id']; ?>]" 
                                                           id="present_<?php echo $student['id']; ?>" value="present"
                                                           <?php echo ($student['status'] == 'present') ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-success" for="present_<?php echo $student['id']; ?>">Present</label>

                                                    <input type="radio" class="btn-check" name="status[<?php echo $student['id']; ?>]" 
                                                           id="absent_<?php echo $student['id']; ?>" value="absent"
                                                           <?php echo ($student['status'] == 'absent') ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-danger" for="absent_<?php echo $student['id']; ?>">Absent</label>

                                                    <input type="radio" class="btn-check" name="status[<?php echo $student['id']; ?>]" 
                                                           id="late_<?php echo $student['id']; ?>" value="late"
                                                           <?php echo ($student['status'] == 'late') ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-warning" for="late_<?php echo $student['id']; ?>">Late</label>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">Save Attendance</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
