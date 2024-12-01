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
    <div class="container text-center" data-aos="fade-up">
        <h1 class="display-4 text-white" style="font-family: 'Oswald', sans-serif;">Welcome to Your Future</h1>
        <p class="lead text-white mt-4" style="font-family: 'Open Sans', sans-serif;">
            Build and manage your schedule effortlessly with our intuitive Advanced Schedule Builder.
        </p>
        <a href="register.php" class="btn btn-success btn-lg mt-3">Get Started</a>
    </div>
</section>

<section class="features-section py-5">
    <div class="container">
        <div class="row text-center" data-aos="fade-up">
            <div class="col-md-4">
                <i class="bi bi-calendar-event display-4 text-white"></i>
                <h3 class="text-white mt-3" style="font-family: 'Rubik', sans-serif;">Intuitive Design</h3>
                <p class="text-white">Easily create and customize your schedules with our user-friendly interface.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-graph-up display-4 text-white"></i>
                <h3 class="text-white mt-3" style="font-family: 'Rubik', sans-serif;">Track Progress</h3>
                <p class="text-white">Monitor your academic progress and stay on top of your goals.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-people display-4 text-white"></i>
                <h3 class="text-white mt-3" style="font-family: 'Rubik', sans-serif;">Collaborate</h3>
                <p class="text-white">Connect with peers and manage group schedules seamlessly.</p>
            </div>
        </div>
    </div>
</section>

<?php
    include '../includes/footer.php';
?>
