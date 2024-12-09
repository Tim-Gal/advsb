<?php
$pageTitle = "Settings";
$pageCSS = [
    '../assets/css/settings.css'
];
$pageJS = [
    '../assets/js/settings.js'
];

include '../includes/header.php';

$user_id = $_SESSION['user_id'];

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

    <form action="../api/update_settings.php" method="post" class="settings-form" id="settingsForm">
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
            <input type="email" name="email" id="email" class="input-field" value="<?php echo htmlspecialchars($userData['email']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">New Password</label>
            <input type="password" name="password" id="password" class="input-field">
        </div>
        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="input-field">
        </div>
        <button type="submit" class="btn submit-btn">Update Settings</button>
    </form>
</div>

<div class="modal fade" id="passwordVerificationModal" tabindex="-1" aria-labelledby="passwordVerificationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="../api/verify_password_change.php" method="POST" class="verify-form">
        <div class="modal-header">
          <h5 class="modal-title" id="passwordVerificationModalLabel">Verify Password Change</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>A verification code has been sent to your email. Please enter it below to confirm your password change.</p>
          <div class="mb-3">
              <label for="verification_code" class="form-label">Verification Code</label>
              <input type="text" class="form-control" id="verification_code" name="verification_code" maxlength="6" placeholder="123456" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Verify Password Change</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
include '../includes/footer.php';
?>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm_password = document.getElementById('confirm_password').value;

    if (password || confirm_password) { 
        if (password !== confirm_password) {
            e.preventDefault();
            alert('Passwords do not match.');
            return;
        }

        e.preventDefault();
        const formData = new FormData(this);
        fetch('../.api/send_password_verification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var passwordModal = new bootstrap.Modal(document.getElementById('passwordVerificationModal'));
                passwordModal.show();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending the verification code.');
        });
    }
});
</script>
