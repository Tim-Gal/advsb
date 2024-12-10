<?php
// myprogress.php

$pageTitle = "My Progress";
$pageCSS = [
    '../assets/css/global.css',
    '../assets/css/my_progress.css'
];

include '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or display an error
    echo "<div class='container my-5'><div class='alert alert-danger'>You must be logged in to view this page.</div></div>";
    include '../includes/footer.php';
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch total number of courses
$sql_total = "SELECT COUNT(*) as total FROM courses";
$res_total = $conn->query($sql_total);
$totalCourses = $res_total->fetch_assoc()['total'] ?? 0;

// Fetch completed courses
$sql_completed = "
    SELECT c.course_code, c.course_name, c.course_description
    FROM coursescompleted cc
    JOIN courses c ON cc.course_code = c.course_code
    WHERE cc.student_id = ?
";
$stmt_completed = $conn->prepare($sql_completed);
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$res_completed = $stmt_completed->get_result();
$completedCourses = [];
while ($row = $res_completed->fetch_assoc()) {
    $completedCourses[] = $row;
}
$stmt_completed->close();

$completedCount = count($completedCourses);
$progress = ($totalCourses > 0) ? ($completedCount / $totalCourses) * 100 : 0;
?>

<div class="main-content myprogress-container">
    <h1 class="mb-4">My Progress</h1>
    <div class="row">
        <!-- Left Column: Circular Progress Chart -->
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <canvas id="progressChart" width="200" height="200"></canvas>
                    <h3 class="mt-3"><?php echo round($progress, 2); ?>%</h3>
                    <p>Completion</p>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Completed Courses List -->
        <div class="col-md-8">
            <h3>Completed Courses</h3>
            <?php if ($completedCount > 0): ?>
                <div class="accordion" id="completedCoursesAccordion">
                    <?php foreach ($completedCourses as $index => $course): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#completedCoursesAccordion">
                                <div class="accordion-body">
                                    <?php 
                                        // Display course description if available
                                        echo htmlspecialchars($course['course_description'] ?? 'No description available.');
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>You haven't completed any courses yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('progressChart').getContext('2d');
        const progressPercentage = <?php echo round($progress, 2); ?>;
        
        const data = {
            labels: ['Completed', 'Remaining'],
            datasets: [{
                data: [<?php echo $completedCount; ?>, <?php echo $totalCourses - $completedCount; ?>],
                backgroundColor: ['#007EA7', '#e0e0e0'],
                borderWidth: 0
            }]
        };
        
        const options = {
            cutout: '70%',
            rotation: -90,
            circumference: 180,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                },
                // Custom plugin to display percentage in the center
                beforeDraw: function(chart) {
                    const width = chart.width,
                          height = chart.height,
                          ctx = chart.ctx;
                    ctx.restore();
                    const fontSize = (height / 114).toFixed(2);
                    ctx.font = fontSize + "em sans-serif";
                    ctx.textBaseline = "middle";
        
                    const text = "<?php echo round($progress, 2); ?>%",
                          textX = Math.round((width - ctx.measureText(text).width) / 2),
                          textY = height / 1.5;
        
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }
        };
        
        const progressChart = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: options
        });
    });
</script>

<?php
include '../includes/footer.php';
?>
