<?php
$pageCSS = [
    '../assets/css/global.css'
];

include '../includes/header.php'; 
?>

<div class="main-content">
    <div class="container my-4">
        <h1 class="text-center mb-4">My Schedule Builder</h1>

        <!-- Semester Selection -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Select Semester</h5>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group" aria-label="Semester selection">
                    <input type="radio" class="btn-check" name="semester" id="semesterFall" value="Fall" autocomplete="off" checked>
                    <label class="btn btn-outline-primary" for="semesterFall">Fall</label>

                    <input type="radio" class="btn-check" name="semester" id="semesterWinter" value="Winter" autocomplete="off">
                    <label class="btn btn-outline-primary" for="semesterWinter">Winter</label>

                    <input type="radio" class="btn-check" name="semester" id="semesterSummer" value="Summer" autocomplete="off">
                    <label class="btn btn-outline-primary" for="semesterSummer">Summer</label>
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
                        <button class="btn btn-success" id="addCourseButton" type="button">Add Course</button>
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
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hours = range(8, 18);
                        foreach ($hours as $h) {
                            $timeLabel = sprintf("%02d:00", $h);
                            echo "<tr>";
                            echo "<td class='time-cell'>$timeLabel</td>";
                            echo "<td class='Mon-$h day-cell'></td>";
                            echo "<td class='Tue-$h day-cell'></td>";
                            echo "<td class='Wed-$h day-cell'></td>";
                            echo "<td class='Thu-$h day-cell'></td>";
                            echo "<td class='Fri-$h day-cell'></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="notificationToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
        <div class="toast-body" id="toastBody">
            <!-- Toast message will be injected here -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    </div>

    <!-- Google Maps Modal -->
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
</div>


<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!-- External Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAc_2sgP5U-SRL68dw2XrUVq2ptSBl-3JI"></script>

<!-- Custom JavaScript -->
<script src="../assets/js/dashboard.js"></script>

<?php
include '../includes/footer.php';
?>
