
<?php

// Locate the private configuration file outside the document root.
$configPath = dirname(__DIR__) . '/config/database.php';

// Load database credentials.
$dbConfig = require $configPath;

try {

    // Enable MySQLi exceptions for database errors.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Establish the database connection.
    $conn = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database'],
        $dbConfig['port']
    );

    // Set character encoding.
    $conn->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {

    // Record the actual error in the server's PHP error log.
    error_log('Database connection failed: ' . $e->getMessage());

    // Show a generic message to the visitor.
    http_response_code(500);
    exit('A database error occurred. Please try again later.');

}