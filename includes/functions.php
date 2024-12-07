<?php
// includes/functions.php

/**
 * Generates a random 6-digit verification code.
 *
 * @return string
 */
function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Sanitizes user input.
 *
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
