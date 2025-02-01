<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

// Get selected class
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : '';

// Fetch classes for dropdown
$classes_query = "SELECT id, name, section FROM classes ORDER BY name";
$classes_result = $conn->query($classes_query);

// Fetch results if class is selected
if ($selected_class) {
    $results_query = "SELECT s.id, s.registration_number, s.first_name, s.last_name,
                    GROUP_CONCAT(DISTINCT sub.name) as subjects,
                    COUNT(DISTINCT r.subject_id) as subjects_count,
                    ROUND(AVG(r.marks), 2) as average_marks
                    FROM students s
                    LEFT JOIN results r ON s.id = r.student_id
                    LEFT JOIN subjects sub ON r.subject_id = sub.id
                    WHERE s.class_id = ?
                    GROUP BY s.id
                    ORDER BY s.first_name";
    
    $stmt = $conn->prepare($results_query);
    $stmt->bind_param("i", $selected_class);
    $stmt->execute();
    $results_result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Student Management System</title>
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
                <a href="attendance.php" class="nav-link">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Attendance</span>
                </a>
                <a href="results.php" class="nav-link active">
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
                    <h1 class="h3 mb-0 text-gray-800">Results Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResultModal">
                        <i class="fas fa-plus"></i> Add New Result
                    </button>
                </div>

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
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Load Results</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($selected_class && isset($results_result)): ?>
                <!-- Results Table -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="resultsTable">
                                <thead>
                                    <tr>
                                        <th>Reg. No.</th>
                                        <th>Student Name</th>
                                        <th>Subjects</th>
                                        <th>Average Marks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($student = $results_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $student['registration_number']; ?></td>
                                        <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                        <td><?php echo $student['subjects'] ?: 'No subjects'; ?></td>
                                        <td>
                                            <?php 
                                            if ($student['average_marks']) {
                                                echo $student['average_marks'] . '%';
                                                // Add color coding based on marks
                                                $color_class = '';
                                                if ($student['average_marks'] >= 80) $color_class = 'text-success';
                                                elseif ($student['average_marks'] >= 60) $color_class = 'text-primary';
                                                elseif ($student['average_marks'] >= 40) $color_class = 'text-warning';
                                                else $color_class = 'text-danger';
                                                echo " <i class='fas fa-circle $color_class'></i>";
                                            } else {
                                                echo 'No results';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewResults(<?php echo $student['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="addResult(<?php echo $student['id']; ?>)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Result Modal -->
    <div class="modal fade" id="addResultModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addResultForm" action="actions/add_result.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student</label>
                            <select class="form-select" id="student_id" name="student_id" required>
                                <option value="">Select Student...</option>
                                <?php
                                if ($selected_class) {
                                    $students = $conn->query("SELECT id, first_name, last_name FROM students WHERE class_id = $selected_class");
                                    while($student = $students->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo $student['first_name'] . ' ' . $student['last_name']; ?>
                                    </option>
                                    <?php endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Subject</label>
                            <select class="form-select" id="subject_id" name="subject_id" required>
                                <option value="">Select Subject...</option>
                                <?php
                                if ($selected_class) {
                                    $subjects = $conn->query("SELECT id, name FROM subjects WHERE class_id = $selected_class");
                                    while($subject = $subjects->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name']; ?></option>
                                    <?php endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="marks" class="form-label">Marks</label>
                            <input type="number" class="form-control" id="marks" name="marks" min="0" max="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="exam_type" class="form-label">Exam Type</label>
                            <select class="form-select" id="exam_type" name="exam_type" required>
                                <option value="Midterm">Midterm</option>
                                <option value="Final">Final</option>
                                <option value="Quiz">Quiz</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="exam_date" class="form-label">Exam Date</label>
                            <input type="date" class="form-control" id="exam_date" name="exam_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#resultsTable').DataTable();
        });

        function viewResults(studentId) {
            // Implement view detailed results
        }

        function addResult(studentId) {
            $('#student_id').val(studentId);
            $('#addResultModal').modal('show');
        }
    </script>
</body>
</html>
