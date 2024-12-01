<?php
// header.php

session_start();

// Check if user is not logged in but has a remember_me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    include 'config.php';

    $remember_token = $_COOKIE['remember_me'];

    // Look up the user by remember_token
    $stmt = $conn->prepare("SELECT id FROM users WHERE remember_token = ?");
    $stmt->bind_param("s", $remember_token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $_SESSION['user_id'] = $user_id;
    }

    $stmt->close();
    $conn->close();
}

// Fetch user details if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    include 'config.php';
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Advanced Schedule Builder'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Rubik:wght@400;500;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/global.css" rel="stylesheet">
    
    <?php
        if (isset($pageCSS)) {
            foreach ($pageCSS as $css) {
                echo '<link href="' . $css . '" rel="stylesheet">';
            }
        }
    ?>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="../assets/images/logo.png" alt="Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <?php if (isset($user)): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn btn-primary text-white px-4" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($username); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="myprogress.php">My Progress</a></li>
                                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                                <li><a class="dropdown-item" href="network.php">Network</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-4" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
