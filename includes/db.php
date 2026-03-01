<?php
// includes/db.php
// Compatible with PHP 5.6 + MySQL 5.7

if (!file_exists(__DIR__ . '/config.php')) {
    die("Missing includes/config.php — create it and set DB credentials.");
}

require_once __DIR__ . '/config.php';

// Create connection
$mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if (!$mysqli) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
if (!mysqli_set_charset($mysqli, "utf8")) {
    die("Error setting charset: " . mysqli_error($mysqli));
}
?>