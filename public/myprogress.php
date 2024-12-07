<?php
$pageTitle = "My Progress";
$pageCSS = [
    '../assets/css/myprogress.css'
];

include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$sql_total = "SELECT COUNT(*) as total FROM courses";
$res_total = $conn->query($sql_total);
$totalCourses = $res_total->fetch_assoc()['total'] ?? 0;

$sql_completed = "
SELECT c.course_code, c.course_name
FROM coursesCompleted cc
JOIN courses c ON cc.course_code = c.course_code
WHERE cc.student_id = $user_id
";
$res_completed = $conn->query($sql_completed);
$completedCourses = [];
while ($row = $res_completed->fetch_assoc()) {
    $completedCourses[] = $row;
}

$completedCount = count($completedCourses);
$progress = ($totalCourses > 0) ? ($completedCount / $totalCourses) * 100 : 0;
?>

<div class="myprogress-container">
    <h1>My Progress</h1>
    <p>Your progress in the program:</p>
    <div class="progress-bar-background">
        <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%;"></div>
    </div>
    <p><?php echo $completedCount; ?> out of <?php echo $totalCourses; ?> courses completed (<?php echo round($progress, 2); ?>%)</p>

    <h3>Completed Courses</h3>
    <?php if ($completedCount > 0): ?>
        <ul class="completed-courses-list">
            <?php foreach ($completedCourses as $course): ?>
                <li><?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You haven't completed any courses yet.</p>
    <?php endif; ?>
</div>

<?php
include '../includes/footer.php';
?>
