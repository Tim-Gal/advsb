<?php
    $pageTitle = "Visual Schedule Builder | Login";
    $pageCSS = [
        '../assets/css/login.css'
    ];
    $pageJS = [
        '../assets/js/login.js'
    ];

    include '../includes/header.php';
?>
    
<section class="login-section d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6" data-aos="fade-up">
                <div class="card p-4">
                    <h2 class="text-center mb-4" style="font-family: 'Oswald', sans-serif;">Login</h2>
                    <form action="authenticate.php" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label" style="font-family: 'Open Sans', sans-serif;">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your school email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label" style="font-family: 'Open Sans', sans-serif;">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me">
                            <label class="form-check-label" for="rememberMe" style="font-family: 'Open Sans', sans-serif;">Remember Me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="register.php" class="text-decoration-none" style="font-family: 'Open Sans', sans-serif;">Don't have an account? Register here</a>
                    </div>
                    <div class="mt-2 text-center">
                        <a href="#" class="text-decoration-none" style="font-family: 'Open Sans', sans-serif;">Forgot Password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
    include '../includes/footer.php';
?>
