<?php
include '../includes/header.php'; 
?>

<link href="../assets/css/dashboard.css" rel="stylesheet">

<div class="dashboard-container">
    <h1>My Schedule Builder</h1>

    <div class="semester-selection">
        <label><input type="radio" name="semester" value="Fall" checked> Fall</label>
        <label><input type="radio" name="semester" value="Winter"> Winter</label>
        <label><input type="radio" name="semester" value="Summer"> Summer</label>
    </div>

    <div class="course-search">
        <input type="text" id="courseSearchInput" placeholder="Enter course code or name...">
        <button id="addCourseButton">Go</button>
        <div id="searchSuggestions" class="suggestions-dropdown"></div>
    </div>

    <!-- Schedule Table -->
    <div class="schedule-container">
        <table class="schedule-table" id="scheduleTable">
            <thead>
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

<script src="../assets/js/dashboard.js"></script>

<?php
include '../includes/footer.php';
?>
