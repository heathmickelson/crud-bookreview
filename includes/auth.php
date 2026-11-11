
<?php

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Start a session if one has not already been started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create a CSRF token for forms.
function csrf_token() {

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Verify a submitted CSRF token.
function verify_csrf_token($token) {

    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

// Require the user to be logged in.
function require_login() {

    if (empty($_SESSION['user_id'])) {
        header('Location: /users/index.php');
        exit;
    }
}