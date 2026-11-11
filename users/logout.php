
<?php

require_once __DIR__ . '/../includes/auth.php';

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Verify CSRF token.
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}

// Remove session data.
$_SESSION = [];

// Destroy the session.
session_destroy();

// Return to login page.
header('Location: index.php');
exit;

