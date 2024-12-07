<?php
// public/settings.php
$pageTitle = "Settings";
$pageCSS = [
    '../assets/css/settings.css'
];
// If you have page-specific JS for settings, you can add:
// $pageJS = ['../assets/js/settings.js'];

include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Fetch user data from students table
$sql_user = "SELECT fname, lname, email FROM students WHERE student_id = $user_id LIMIT 1";
$res_user = $conn->query($sql_user);
$userData = $res_user->fetch_assoc();
?>

<div class="settings-container">
    <h1>Settings</h1>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }

    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <form action="update_settings.php" method="post" class="settings-form">
        <div class="form-group">
            <label for="fname" class="form-label">First Name</label>
            <input type="text" name="fname" id="fname" class="input-field" value="<?php echo htmlspecialchars($userData['fname']); ?>" required>
        </div>
        <div class="form-group">
            <label for="lname" class="form-label">Last Name</label>
            <input type="text" name="lname" id="lname" class="input-field" value="<?php echo htmlspecialchars($userData['lname']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="input-field" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="password" id="password" class="input-field">
        </div>
        <button type="submit" class="btn submit-btn">Update Settings</button>
    </form>
</div>

<?php
include '../includes/footer.php';
