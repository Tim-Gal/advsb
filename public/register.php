<?php
    $pageTitle = "Visual Schedule Builder | Register";
    $pageCSS = [
        '../assets/css/register.css'
    ];
    $pageJS = [
        '../assets/js/register.js'
    ];

    include '../includes/header.php';
?>

<section class="register-section d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6" data-aos="fade-up">
                <div class="card p-4">
                    <h2 class="text-center mb-4" style="font-family: 'Oswald', sans-serif;">Register</h2>

                    <?php
                        if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success" role="alert" style="font-family: \'Open Sans\', sans-serif;">' . $_SESSION['success'] . '</div>';
                            unset($_SESSION['success']);
                        }

                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger" role="alert" style="font-family: \'Open Sans\', sans-serif;">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        }
                    ?>

                    <form action="process_register.php" method="POST" id="registerForm">
                        <div class="mb-3">
                            <label for="student_id" class="form-label" style="font-family: 'Open Sans', sans-serif;">Student ID</label>
                            <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter your Student ID" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label" style="font-family: 'Open Sans', sans-serif;">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your Email" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label" style="font-family: 'Open Sans', sans-serif;">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label" style="font-family: 'Open Sans', sans-serif;">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label" style="font-family: 'Open Sans', sans-serif;">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Register</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="login.php" class="text-decoration-none" style="font-family: 'Open Sans', sans-serif;">Already have an account? Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
    include '../includes/footer.php';
?>
