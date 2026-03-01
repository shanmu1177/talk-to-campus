<?php
session_start();

// Prevent caching issues
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// If chat session not set, return empty array
if (!isset($_SESSION['chat']) || !is_array($_SESSION['chat'])) {
    echo json_encode(array());
    exit;
}

// Return chat messages
echo json_encode($_SESSION['chat']);
exit;
?>