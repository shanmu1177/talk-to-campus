<?php
// includes/config.php
// Compatible with PHP 5.6 + Ubuntu 18.04

// App root
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Database settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // set MySQL password if any
define('DB_NAME', 'chatbot');
define('DB_PORT', 3306);

// Site title
define('SITE_TITLE', 'Talk To Campus');

/*
|--------------------------------------------------------------------------
| SESSION SETUP (PHP 5.6 SAFE VERSION)
|--------------------------------------------------------------------------
*/

// Only configure session if not already started
if (session_id() == '') {

    // Custom session name
    if (!defined('SESSION_NAME')) {
        define('SESSION_NAME', 'talk_to_campus_sess');
    }

    session_name(SESSION_NAME);

    // Secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // set to 1 if HTTPS

    // Do NOT start session here
    // session_start() should be called in pages where needed
}
?>