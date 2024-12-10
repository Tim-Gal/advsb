<?php
// public/dashboard.php

// Include necessary configuration and function files
include '../includes/header.php'; 

// Define days of the week for the schedule
$daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

$startHour = 8;  
$endHour = 18;   

// Ensure the user is authenticated
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not authenticated
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<head>
</head>
<div class="container my-4">
    <h1 class="text-center mb-4">My Schedule Builder</h1>

    <!-- Semester Selection -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Select Semester</h5>
        </div>
        <div class="card-body">
            <div class="btn-group" role="group" aria-label="Semester selection">
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

    <!-- Course Search and Add -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Add a Course</h5>
        </div>
        <div class="card-body">
            <div class="position-relative">
                <div class="input-group">
                    <input type="text" id="courseSearchInput" class="form-control" placeholder="Enter course code or name..." aria-label="Course search" autocomplete="off">
                </div>
                <div id="searchSuggestions" class="list-group suggestions-dropdown" style="position: absolute; width: 100%; z-index: 1000;"></div>
            </div>
        </div>
    </div>

    <!-- Selected Course Display -->
    <div id="selectedCourseContainer" class="card mb-4" style="display: none;">
        <div class="card-body d-flex align-items-center">
            <span id="selectedCourseText" class="me-3"></span>
            <button id="removeSelectedCourse" class="btn btn-sm btn-danger me-2">Remove</button>
            <button id="confirmAddCourse" class="btn btn-sm btn-primary">Confirm Add</button>
        </div>
    </div>

    <!-- Schedule Table -->
    <div class="card">
        <div class="card-header">
            <h5>Your Schedule</h5>
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
                        // Generate time label
                        $timeLabel = sprintf("%02d:00", $hour);
                        echo "<tr>";
                        echo "<td class='time-cell'>$timeLabel</td>";
                        foreach ($daysOfWeek as $day) {
                            // Unique class for each cell based on day and hour
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

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body" id="toastBody">
            <!-- Toast message will be injected here -->
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

<!-- Google Maps Modal (Optional) -->
<div class="modal fade" id="mapsModal" tabindex="-1" aria-labelledby="mapsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="mapsModalLabel">Class Locations</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="map" style="height: 500px; width: 100%;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Action Buttons -->
<button type="button" class="btn btn-outline-secondary position-fixed" id="mapButton" style="bottom: 80px; left: 20px;">
    <i class="bi bi-geo-alt-fill"></i> Show Class Locations
</button>

<button type="button" class="btn btn-outline-success position-fixed" id="downloadPdfButton" style="top: 80px; right: 20px;">
    <i class="bi bi-file-earmark-pdf-fill"></i> Download Schedule
</button>

<script src="../assets/js/dashboard.js"></script>

<?php
include '../includes/footer.php';

?>
