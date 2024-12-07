<?php
$pageTitle = "Advanced Schedule Builder | Home";
$pageCSS = [
    '../assets/css/index.css'
];
$pageJS = [
    '../assets/js/index.js'
];

include '../includes/header.php';
?>

<section class="hero-section d-flex align-items-center">
    <div class="container text-center">
        <h1 class="hero-title">Welcome to Your Future</h1>
        <p class="hero-subtitle mt-4">
            Build and manage your schedule effortlessly with our intuitive Advanced Schedule Builder.
        </p>
        <a href="register.php" class="btn btn-success btn-lg mt-3">Get Started</a>
    </div>
</section>

<section class="features-section py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <h3 class="feature-title mt-3">Intuitive Design</h3>
                <p class="feature-text">Easily create and customize your schedules with our user-friendly interface.</p>
            </div>
            <div class="col-md-4">
                <h3 class="feature-title mt-3">Track Progress</h3>
                <p class="feature-text">Monitor your academic progress and stay on top of your goals.</p>
            </div>
            <div class="col-md-4">
                <h3 class="feature-title mt-3">Collaborate</h3>
                <p class="feature-text">Connect with peers and manage group schedules seamlessly.</p>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
