<?php

include '../includes/header.php'; 

$daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
$startHour = 8;  
$endHour = 18;




if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid my-4">
        <h1 class="text-center mb-4">My Schedule Builder</h1>

        <div class="row">
            <!-- Left Side -->
            <div class="col-md-4">
                <!-- Semester Selector -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Select Semester</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group w-100" role="group" aria-label="Semester selection">
                            <?php
                            $semesters = ['Fall', 'Winter', 'Summer'];
                            foreach ($semesters as $semester) {
                                echo '<input type="radio" class="btn-check" name="semester" id="semester' . $semester . '" value="' . $semester . '"' . ($semester === 'Fall' ? ' checked' : '') . '>';
                                echo '<label class="btn btn-outline-primary" for="semester' . $semester . '">' . $semester . '</label>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Enrolled Courses -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Enrolled Courses</h5>
                    </div>
                    <div class="card-body">
                        <div id="enrolledCoursesList" class="list-group">
                        </div>
                        <div id="enrolledCoursesLoading" class="text-center my-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Adder -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Add a Course</h5>
                    </div>
                    <div class="card-body">
                        <div class="position-relative">
                            <div class="input-group">
                                <input type="text" id="courseSearchInput" class="form-control" placeholder="Enter course code or name..." aria-label="Course search" autocomplete="off">
                            </div>
                            <div id="searchSuggestions" class="list-group suggestions-dropdown"></div>
                        </div>
                    </div>
                </div>

                <!-- Selected Course Container -->
                <div id="selectedCourseContainer" class="card mb-4" style="display: none;">
                    <div class="card-body d-flex align-items-center">
                        <span id="selectedCourseText" class="me-3"></span>
                        <button id="removeSelectedCourse" class="btn btn-sm btn-danger me-2">Remove</button>
                        <button id="confirmAddCourse" class="btn btn-sm btn-primary">Confirm Add</button>
                        <input type="hidden" id="csrfToken" value="<?php echo $csrf_token; ?>">
                    </div>
                </div>
            </div>

            <!-- Right Side - Schedule Table -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Your Schedule</h5>
                        <button type="button" class="btn btn-outline-success" id="downloadPdfButton">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Download Schedule
                        </button>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover" id="scheduleTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Time</th>
                                    <?php foreach ($daysOfWeek as $day): ?>
                                        <th><?php echo $day; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                for ($hour = $startHour; $hour <= $endHour; $hour++) {
                                    $timeLabel = sprintf("%02d:00", $hour);
                                    echo "<tr>";
                                    echo "<td class='time-cell'>$timeLabel</td>";
                                    foreach ($daysOfWeek as $day) {
                                        echo "<td class='{$day}-{$hour} day-cell'><div class='course-block-container'></div></td>";
                                    }
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body" id="toastBody"></div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"></script>
</body>
</html>
<?php
include '../includes/footer.php';
?>