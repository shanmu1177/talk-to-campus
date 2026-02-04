<?php
// includes/db.php
// Creates a mysqli connection in $mysqli (for PHP 5.6)

if (!file_exists(__DIR__ . '/config.php')) {
    die("Missing includes/config.php — create it and set DB credentials.");
}
require_once __DIR__ . '/config.php';

// Create connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($mysqli->connect_errno) {
    // For production, avoid showing raw errors. For local dev, this is convenient.
    die("Database connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

// Ensure UTF-8
$mysqli->set_charset("utf8");
