<?php
// public/dashboard.php
// Assuming header.php includes session_start(), common HTML <head> elements, and navigation.

include '../includes/header.php'; 
?>

<div class="container">
    <h1>My Schedule Builder</h1>

    <!-- Semester Selection -->
    <div class="semester-selection" style="margin-bottom:20px;">
        <label><input type="radio" name="semester" value="Fall" checked> Fall</label>
        <label style="margin-left:10px;"><input type="radio" name="semester" value="Winter"> Winter</label>
        <label style="margin-left:10px;"><input type="radio" name="semester" value="Summer"> Summer</label>
    </div>

    <!-- Course Search -->
    <div class="course-search" style="margin-bottom:20px; position:relative;">
        <input type="text" id="courseSearchInput" placeholder="Enter course code or name..." style="padding:5px; width:250px;">
        <button id="addCourseButton" style="padding:5px 10px; margin-left:5px;">Go</button>
        <div id="searchSuggestions" class="suggestions-dropdown" style="
            position:absolute;
            top:30px;
            left:0;
            width:250px;
            background:#fff;
            border:1px solid #ccc;
            display:none;
            z-index:1000;
        "></div>
    </div>

    <!-- Schedule Table -->
    <div class="schedule-container" style="overflow-x:auto;">
        <table class="schedule-table" id="scheduleTable" border="1" cellspacing="0" cellpadding="5" style="border-collapse:collapse; width:100%; text-align:center;">
            <thead style="background:#f0f0f0;">
                <tr>
                    <th style="width:80px;">Time</th>
                    <th>Monday</th>
                    <th>Tuesday</th>
                    <th>Wednesday</th>
                    <th>Thursday</th>
                    <th>Friday</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Hourly slots from 08:00 to 18:00
                $hours = range(8, 18);
                foreach ($hours as $h) {
                    $timeLabel = sprintf("%02d:00", $h);
                    echo "<tr>";
                    echo "<td style=\"background:#f9f9f9; font-weight:bold;\">$timeLabel</td>";
                    echo "<td class='Mon-$h'></td>";
                    echo "<td class='Tue-$h'></td>";
                    echo "<td class='Wed-$h'></td>";
                    echo "<td class='Thu-$h'></td>";
                    echo "<td class='Fri-$h'></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Include the main JS script -->
<script src="../assets/js/dashboard.js"></script>

<?php
include '../includes/footer.php';
?>
