<?php
// includes/config.php
// Basic configuration — update these values for your environment.

// app root
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));

// Database connection settings — change these to your local values
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');         // set your MySQL password if any
define('DB_NAME', 'chatbot');  // database name you created
define('DB_PORT', 3306);

// Other site defaults
define('SITE_TITLE', 'Talk To Campus');

// SESSION setup: only change session settings if session not already started
// This avoids "Session name cannot be changed when a session is active" warnings.
if (session_status() === PHP_SESSION_NONE) {
    // Set a custom session name if you want
    if (!defined('SESSION_NAME')) define('SESSION_NAME', 'talk_to_campus_sess');
    if (defined('SESSION_NAME') && session_name() !== SESSION_NAME) {
        session_name(SESSION_NAME);
    }

    // Recommended cookie settings (only set when session not active)
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // set to 1 if using HTTPS
    // Do NOT call session_start() here — leave pages to start session when needed.
}
