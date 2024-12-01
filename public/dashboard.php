
<?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
?>



<?php
    $pageTitle = "Visual Schedule Builder | Dashboard";
    $pageCSS = [
        '../assets/css/dashboard.css'
    ];
    $pageJS = [
        '../assets/js/dashboard.js'
    ];

    include '../includes/header.php';
    include '../includes/functions.php';

    checkAuth();
?>
    
<section class="dashboard-section">
    <div class="container">
        <h1>Welcome to Your Dashboard</h1>
    </div>
</section>

<?php
    include '../includes/footer.php';
?>
