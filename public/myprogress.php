<?php
// myprogress.php

$pageTitle = "My Progress";
$pageCSS = [
    '../assets/css/global.css',
    '../assets/css/my_progress.css'
];

include '../includes/header.php';

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission for adding a completed course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_code'])) {
    // Retrieve and sanitize the course code
    $course_code = strtoupper(trim($_POST['course_code']));

    // Validate input
    if (empty($course_code)) {
        $_SESSION['add_course_error'] = "Course code cannot be empty.";
    } else {
        // Begin Transaction
        $conn->begin_transaction();

        try {
            /**
             * Step 1: Check if the course exists
             */
            $sqlCheckCourse = "SELECT course_code FROM courses WHERE course_code = ?";
            $stmtCheckCourse = $conn->prepare($sqlCheckCourse);
            if (!$stmtCheckCourse) {
                throw new Exception("Database error while checking course existence: " . $conn->error);
            }
            $stmtCheckCourse->bind_param("s", $course_code);
            $stmtCheckCourse->execute();
            $resultCheckCourse = $stmtCheckCourse->get_result();
            if ($resultCheckCourse->num_rows === 0) {
                $stmtCheckCourse->close();
                throw new Exception("The course code '{$course_code}' does not exist.");
            }
            $stmtCheckCourse->close();

            /**
             * Step 2: Insert into coursescompleted
             * Prevent duplicate entries
             */
            $sqlInsertCompleted = "INSERT INTO coursescompleted (student_id, course_code) VALUES (?, ?)";
            $stmtInsertCompleted = $conn->prepare($sqlInsertCompleted);
            if (!$stmtInsertCompleted) {
                throw new Exception("Database error while inserting completed course: " . $conn->error);
            }
            $stmtInsertCompleted->bind_param("is", $user_id, $course_code);

            if (!$stmtInsertCompleted->execute()) {
                // Check for duplicate entry error (assuming UNIQUE constraint on (student_id, course_code))
                if ($conn->errno === 1062) { // MySQL error code for duplicate entry
                    $stmtInsertCompleted->close();
                    throw new Exception("You have already marked '{$course_code}' as completed.");
                } else {
                    throw new Exception("Database error while inserting completed course: " . $stmtInsertCompleted->error);
                }
            }
            $stmtInsertCompleted->close();

            /**
             * Step 3: Check for existing enrollments in the same course across all semesters
             */
            $sqlCheckEnrollment = "
                SELECT ce.section_code, s.semester, c.course_name
                FROM coursesenrolled ce
                JOIN sections s ON ce.section_code = s.section_code
                JOIN courses c ON s.course_code = c.course_code
                WHERE ce.student_id = ? AND c.course_code = ?
            ";
            $stmtCheckEnrollment = $conn->prepare($sqlCheckEnrollment);
            if (!$stmtCheckEnrollment) {
                throw new Exception("Database error while checking existing enrollments: " . $conn->error);
            }
            $stmtCheckEnrollment->bind_param("is", $user_id, $course_code);
            $stmtCheckEnrollment->execute();
            $resultEnrollment = $stmtCheckEnrollment->get_result();

            $existingEnrollments = [];
            while ($row = $resultEnrollment->fetch_assoc()) {
                $existingEnrollments[] = [
                    'section_code' => $row['section_code'],
                    'semester' => ucfirst(strtolower($row['semester'])),
                    'course_name' => $row['course_name']
                ];
            }
            $stmtCheckEnrollment->close();

            /**
             * Step 4: Remove existing enrollments if any
             */
            if (!empty($existingEnrollments)) {
                $section_codes = array_column($existingEnrollments, 'section_code');
                // Prepare placeholders for IN clause
                $placeholders = implode(',', array_fill(0, count($section_codes), '?'));
                $types = str_repeat('i', count($section_codes));
                $sqlDeleteEnrollments = "DELETE FROM coursesenrolled WHERE student_id = ? AND section_code IN ($placeholders)";
                $stmtDeleteEnrollments = $conn->prepare($sqlDeleteEnrollments);
                if (!$stmtDeleteEnrollments) {
                    throw new Exception("Database error while deleting enrollments: " . $conn->error);
                }

                // Bind parameters dynamically
                $params = array_merge([$user_id], $section_codes);
                $types_bind = 'i' . $types;

                // Create a reference array
                $refs = [];
                foreach ($params as $key => $value) {
                    $refs[$key] = &$params[$key];
                }

                // Bind parameters
                array_unshift($refs, $types_bind);
                call_user_func_array([$stmtDeleteEnrollments, 'bind_param'], $refs);

                if (!$stmtDeleteEnrollments->execute()) {
                    throw new Exception("Database error while deleting enrollments: " . $stmtDeleteEnrollments->error);
                }
                $stmtDeleteEnrollments->close();

                // Optionally, log the removal
                foreach ($existingEnrollments as $enrollment) {
                    error_log("Auto-removed enrollment: User ID {$user_id} removed from course '{$course_code}' ({$enrollment['course_name']}) in {$enrollment['semester']} semester (Section Code: {$enrollment['section_code']}).");
                }

                // Prepare a success message including removal info
                $removedCourses = array_map(function($enrollment) use ($course_code) {
                    return "{$course_code} ({$enrollment['course_name']}) in {$enrollment['semester']} semester (Section Code: {$enrollment['section_code']})";
                }, $existingEnrollments);

                $_SESSION['add_course_success'] = "Course '{$course_code}' marked as completed successfully. Existing enrollments for this course have been removed from your schedule: " . implode(', ', $removedCourses) . ".";
            } else {
                // No existing enrollments to remove
                $_SESSION['add_course_success'] = "Course '{$course_code}' marked as completed successfully.";
            }

            // Commit Transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback Transaction on Error
            $conn->rollback();

            // Log the error
            error_log("Error in myprogress.php: " . $e->getMessage());

            // Set error message
            $_SESSION['add_course_error'] = $e->getMessage();
        }
    }

    // Continue with fetching data after handling form submission
}

// Fetch student's major and minor IDs
$sql_degrees = "SELECT major_id, minor_id FROM students WHERE student_id = ?";
$stmt_degrees = $conn->prepare($sql_degrees);
if (!$stmt_degrees) {
    error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    echo "<div class='container my-5'><div class='alert alert-danger'>An error occurred. Please try again later.</div></div>";
    include '../includes/footer.php';
    exit();
}
$stmt_degrees->bind_param("i", $user_id);
$stmt_degrees->execute();
$stmt_degrees->bind_result($major_id, $minor_id);
$stmt_degrees->fetch();
$stmt_degrees->close();

// Build degrees array with labels
$degrees = [];
$degree_labels = [];

if (!is_null($major_id)) {
    $degrees[] = $major_id;
    $degree_labels[] = "Major";
}

if (!is_null($minor_id)) {
    $degrees[] = $minor_id;
    $degree_labels[] = "Minor";
}

// If no degrees are registered
if (empty($degrees)) {
    echo "<div class='container my-5'><div class='alert alert-warning'>You have not registered for any degree programs yet.</div></div>";
    include '../includes/footer.php';
    exit();
}

// Fetch degree names and types
$placeholders = implode(',', array_fill(0, count($degrees), '?'));
$sql_degree_names = "SELECT degree_id, name, type FROM degrees WHERE degree_id IN ($placeholders)";
$stmt_degree_names = $conn->prepare($sql_degree_names);
if (!$stmt_degree_names) {
    error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    echo "<div class='container my-5'><div class='alert alert-danger'>An error occurred. Please try again later.</div></div>";
    include '../includes/footer.php';
    exit();
}

$types = str_repeat('i', count($degrees));
$stmt_degree_names->bind_param($types, ...$degrees);
$stmt_degree_names->execute();
$res_degree_names = $stmt_degree_names->get_result();
$degree_info = array();
while ($row = $res_degree_names->fetch_assoc()) {
    $degree_info[$row['degree_id']] = [
        'name' => $row['name'],
        'type' => $row['type']
    ];
}
$stmt_degree_names->close();

// Fetch required courses for each degree
$sql_degree_courses = "SELECT degree_id, course_code FROM degree_courses WHERE degree_id IN ($placeholders) AND course_type = 'required'";
$stmt_degree_courses = $conn->prepare($sql_degree_courses);
if (!$stmt_degree_courses) {
    error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    echo "<div class='container my-5'><div class='alert alert-danger'>An error occurred. Please try again later.</div></div>";
    include '../includes/footer.php';
    exit();
}
$stmt_degree_courses->bind_param($types, ...$degrees);
$stmt_degree_courses->execute();
$res_degree_courses = $stmt_degree_courses->get_result();
$required_courses = array();
$courses_to_fetch = array();

while ($row = $res_degree_courses->fetch_assoc()) {
    $required_courses[$row['degree_id']][] = $row['course_code'];
    $courses_to_fetch[] = $row['course_code'];
}
$stmt_degree_courses->close();

// Fetch completed courses
$sql_completed = "
    SELECT c.course_code, c.course_name, c.course_description 
    FROM coursescompleted cc
    JOIN courses c ON cc.course_code = c.course_code
    WHERE cc.student_id = ?
    ORDER BY c.course_code
";
$stmt_completed = $conn->prepare($sql_completed);
if (!$stmt_completed) {
    error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    echo "<div class='container my-5'><div class='alert alert-danger'>An error occurred. Please try again later.</div></div>";
    include '../includes/footer.php';
    exit();
}
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$res_completed = $stmt_completed->get_result();
$completedCourses = array();
$all_completed_course_details = array();
while ($row = $res_completed->fetch_assoc()) {
    $completedCourses[] = $row['course_code'];
    $all_completed_course_details[$row['course_code']] = $row;
}
$stmt_completed->close();

// Encode completed courses as JSON for JavaScript
$completedCoursesJSON = json_encode(array_map('strtoupper', $completedCourses));

// Fetch course details for required courses
$course_details = array();
if (!empty($courses_to_fetch)) {
    $placeholders_courses = implode(',', array_fill(0, count($courses_to_fetch), '?'));
    $sql_courses = "SELECT course_code, course_name, course_description FROM courses WHERE course_code IN ($placeholders_courses)";
    $stmt_courses = $conn->prepare($sql_courses);
    if (!$stmt_courses) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        echo "<div class='container my-5'><div class='alert alert-danger'>An error occurred. Please try again later.</div></div>";
        include '../includes/footer.php';
        exit();
    }
    $types_courses = str_repeat('s', count($courses_to_fetch));
    $stmt_courses->bind_param($types_courses, ...$courses_to_fetch);
    $stmt_courses->execute();
    $res_courses = $stmt_courses->get_result();
    while ($row = $res_courses->fetch_assoc()) {
        $course_details[$row['course_code']] = $row;
    }
    $stmt_courses->close();
}

// Prepare data for each degree
$degree_progress = array();
$completed_course_details = array();
$to_be_completed_course_details = array();

foreach ($degrees as $index => $degree_id) {
    $degree_name = $degree_info[$degree_id]['name'];
    $degree_type = $degree_info[$degree_id]['type'];
    $degree_label = $degree_labels[$index];
    
    $required = $required_courses[$degree_id] ?? [];
    $total_required = count($required);
    $completed_required = array_intersect($required, $completedCourses);
    $completed_required_count = count($completed_required);
    $progress_percentage = ($total_required > 0) ? round(($completed_required_count / $total_required) * 100, 2) : 0;
    
    $degree_progress[$degree_id] = [
        'name' => $degree_name,
        'type' => $degree_type,
        'label' => $degree_label,
        'completed_count' => $completed_required_count,
        'total_required' => $total_required,
        'progress' => $progress_percentage
    ];
    
    // Populate completed courses
    foreach ($completed_required as $course_code) {
        $completed_course_details[$course_code] = $course_details[$course_code];
    }
    
    // Populate to be completed courses
    foreach ($required as $course_code) {
        if (!in_array($course_code, $completedCourses)) {
            $to_be_completed_course_details[$course_code] = $course_details[$course_code];
        }
    }
}
?>
    
<div class="main-content myprogress-container">
    <h1 class="mb-4">My Progress</h1>
    <div class="row">
        <!-- Left Column: Progress Charts -->
        <div class="col-md-4 mb-4">
            <?php foreach ($degrees as $index => $degree_id): ?>
                <div class="card text-center mb-4">
                    <div class="card-body">
                        <canvas id="progressChart_<?php echo $degree_id; ?>" width="200" height="200"></canvas>
                        <h3 class="mt-3"><?php echo htmlspecialchars($degree_progress[$degree_id]['label'] . ": " . $degree_progress[$degree_id]['name']); ?></h3>
                        <p>Required Courses Completion: <?php echo $degree_progress[$degree_id]['progress']; ?>%</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Right Column: Courses List and Add/Remove Completed Course Form -->
        <div class="col-md-8">
            <!-- Add Completed Course Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Add Completed Course</h3>
                </div>
                <div class="card-body">
                    <?php
                        // Display error message from backend
                        if (isset($_SESSION['add_course_error'])) {
                            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['add_course_error']) . '</div>';
                            unset($_SESSION['add_course_error']);
                        }

                        // Display success message from backend
                        if (isset($_SESSION['add_course_success'])) {
                            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['add_course_success']) . '</div>';
                            unset($_SESSION['add_course_success']);
                        }
                    ?>
                    <form action="myprogress.php" method="POST" class="add-course-form" autocomplete="off">
                        <div class="mb-3 position-relative">
                            <label for="course_code" class="form-label">Course Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="course_code" name="course_code" placeholder="e.g., COMP-101" required>
                            <div id="autocomplete-list" class="autocomplete-items"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Completed Course</button>
                    </form>
                </div>
            </div>

            <!-- Completed Courses -->
            <h3>Completed Courses</h3>
            <?php if (!empty($all_completed_course_details)): ?>
                <div class="accordion mb-4" id="completedCoursesAccordion">
                    <?php foreach ($all_completed_course_details as $index => $course): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingCompleted<?php echo $index; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompleted<?php echo $index; ?>" aria-expanded="false" aria-controls="collapseCompleted<?php echo $index; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </button>
                            </h2>
                            <div id="collapseCompleted<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="headingCompleted<?php echo $index; ?>" data-bs-parent="#completedCoursesAccordion">
                                <div class="accordion-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php 
                                            // Display course description if available
                                            echo htmlspecialchars($course['course_description'] ?? 'No description available.');
                                        ?>
                                    </div>
                                    <form action="../api/remove_completed_course.php" method="POST" class="mb-0">
                                        <input type="hidden" name="course_code" value="<?php echo htmlspecialchars($course['course_code']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>You haven't completed any required courses yet.</p>
            <?php endif; ?>

            <!-- To Be Completed Courses -->
            <h3>To Be Completed</h3>
            <?php if (!empty($to_be_completed_course_details)): ?>
                <div class="accordion" id="toBeCompletedCoursesAccordion">
                    <?php foreach ($to_be_completed_course_details as $index => $course): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingToBeCompleted<?php echo $index; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseToBeCompleted<?php echo $index; ?>" aria-expanded="false" aria-controls="collapseToBeCompleted<?php echo $index; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </button>
                            </h2>
                            <div id="collapseToBeCompleted<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="headingToBeCompleted<?php echo $index; ?>" data-bs-parent="#toBeCompletedCoursesAccordion">
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
                <p>All required courses completed!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Autocomplete Styles -->
<style>
    /* Autocomplete items */
    .autocomplete-items {
        position: absolute;
        border: 1px solid #d4d4d4;
        border-bottom: none;
        border-top: none;
        z-index: 99;
        /* Position the autocomplete items to be the same width as the container: */
        top: 100%;
        left: 0;
        right: 0;
    }

    .autocomplete-items div {
        padding: 10px;
        cursor: pointer;
        background-color: #fff; 
        border-bottom: 1px solid #d4d4d4; 
    }

    /* When hovering an item: */
    .autocomplete-items div:hover {
        background-color: #e9e9e9; 
    }

    /* When navigating through the items using the arrow keys: */
    .autocomplete-active {
        background-color: DodgerBlue !important; 
        color: #ffffff; 
    }

    /* Style for the Completed badge within suggestions */
    .suggestion-item .badge {
        font-size: 0.8em;
        vertical-align: middle;
    }

    /* Optional: Gray out completed courses */
    .suggestion-item.completed {
        background-color: #f8f9fa; /* Light gray background */
        color: #6c757d; /* Gray text */
        cursor: not-allowed;
    }

    .suggestion-item.completed:hover {
        background-color: #f8f9fa;
    }
</style>

<!-- Completed Courses JSON for JavaScript -->
<script>
    const completedCourses = <?php echo $completedCoursesJSON; ?>;
</script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Chart Initialization Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php foreach ($degrees as $index => $degree_id): ?>
            const ctx_<?php echo $degree_id; ?> = document.getElementById('progressChart_<?php echo $degree_id; ?>').getContext('2d');
            const completed_<?php echo $degree_id; ?> = <?php echo $degree_progress[$degree_id]['completed_count']; ?>;
            const total_<?php echo $degree_id; ?> = <?php echo $degree_progress[$degree_id]['total_required']; ?>;
            const progress_<?php echo $degree_id; ?> = <?php echo $degree_progress[$degree_id]['progress']; ?>;

            const data_<?php echo $degree_id; ?> = {
                labels: ['Completed', 'Remaining'],
                datasets: [{
                    data: [completed_<?php echo $degree_id; ?>, total_<?php echo $degree_id; ?> - completed_<?php echo $degree_id; ?>],
                    backgroundColor: ['#4caf50', '#e0e0e0'],
                    borderWidth: 0
                }]
            };

            const options_<?php echo $degree_id; ?> = {
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

                        const text = "<?php echo $degree_progress[$degree_id]['progress']; ?>%",
                              textX = Math.round((width - ctx.measureText(text).width) / 2),
                              textY = height / 1.5;

                        ctx.fillText(text, textX, textY);
                        ctx.save();
                    }
                }
            };

            const progressChart_<?php echo $degree_id; ?> = new Chart(ctx_<?php echo $degree_id; ?>, {
                type: 'doughnut',
                data: data_<?php echo $degree_id; ?>,
                options: options_<?php echo $degree_id; ?>
            });
        <?php endforeach; ?>
    });
</script>

<!-- Autocomplete JavaScript -->
<script>
    // Autocomplete function
    function autocomplete(inp, fetchURL) {
        let currentFocus;

        inp.addEventListener("input", function(e) {
            let a, b, i, val = this.value;
            closeAllLists();
            if (!val) { return false;}
            currentFocus = -1;
            a = document.createElement("DIV");
            a.setAttribute("id", this.id + "autocomplete-list");
            a.setAttribute("class", "autocomplete-items");
            this.parentNode.appendChild(a);

            // Fetch matching courses from the server
            fetch(fetchURL + "?query=" + encodeURIComponent(val))
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        // No matches found
                        const noMatchDiv = document.createElement("DIV");
                        noMatchDiv.innerHTML = "<strong>No matches found</strong>";
                        a.appendChild(noMatchDiv);
                        return;
                    }
                    // Create DIV elements for each matching course
                    data.forEach(course => {
                        b = document.createElement("DIV");
                        // Highlight the matching part
                        const regex = new RegExp("(" + val + ")", "gi");
                        const courseCode = course.course_code.replace(regex, "<strong>$1</strong>");
                        const courseName = course.course_name.replace(regex, "<strong>$1</strong>");
                        b.innerHTML = courseCode + " - " + courseName;

                        // Check if the course is already completed
                        const isCompleted = completedCourses.includes(course.course_code.toUpperCase());

                        if (isCompleted) {
                            // Append a "Completed" badge
                            b.innerHTML += ' <span class="badge bg-success ms-2">Completed</span>';
                            // Add a class to style completed courses
                            b.classList.add('completed');
                        }

                        // Hidden input to store the actual course code
                        b.innerHTML += "<input type='hidden' value='" + course.course_code + "'>";
                        b.setAttribute('data-section-code', course.section_code); // Store section_code

                        if (!isCompleted) {
                            b.addEventListener("click", function(e) {
                                inp.value = this.getElementsByTagName("input")[0].value;
                                closeAllLists();
                            });
                        }
                        a.appendChild(b);
                    });
                })
                .catch(error => {
                    console.error('Error fetching course data:', error);
                });
        });

        inp.addEventListener("keydown", function(e) {
            let x = document.getElementById(this.id + "autocomplete-list");
            if (x) x = x.getElementsByTagName("div");
            if (e.keyCode == 40) {
                // Down key
                currentFocus++;
                addActive(x);
            } else if (e.keyCode == 38) { //up
                currentFocus--;
                addActive(x);
            } else if (e.keyCode == 13) {
                // Enter key
                e.preventDefault();
                if (currentFocus > -1) {
                    if (x) x[currentFocus].click();
                }
            }
        });

        function addActive(x) {
            if (!x) return false;
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (x.length - 1);
            x[currentFocus].classList.add("autocomplete-active");
        }

        function removeActive(x) {
            for (let i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active");
            }
        }

        function closeAllLists(elmnt) {
            const x = document.getElementsByClassName("autocomplete-items");
            for (let i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                    x[i].parentNode.removeChild(x[i]);
                }
            }
        }

        // Close all autocomplete lists when clicking outside
        document.addEventListener("click", function (e) {
            closeAllLists(e.target);
        });
    }

    // Initialize autocomplete on the course_code input
    autocomplete(document.getElementById("course_code"), "../api/search_courses.php");
</script>

<?php
include '../includes/footer.php';
?>
