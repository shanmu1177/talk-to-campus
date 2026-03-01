<?php
// includes/auth.php
// PHP 5.6 + MySQL 5.7 compatible (NORMAL PASSWORD VERSION)

// Start session safely
if (session_id() == '') {
    session_start();
}

/**
 * Check if admin logged in
 */
function is_admin_logged_in() {
    return (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] != '');
}

/**
 * Require admin login
 */
function require_admin() {
    if (!is_admin_logged_in()) {
        header("Location: /admin/login.php");
        exit;
    }
}

/**
 * Login admin (NORMAL PASSWORD MATCH)
 */
function login_admin($username, $password) {

    global $mysqli;

    if (!isset($mysqli)) return false;

    $u = trim($username);
    $p = trim($password);

    if ($u == '' || $p == '') return false;

    $stmt = mysqli_prepare($mysqli,
        "SELECT id, username FROM users WHERE username = ? AND password = ? LIMIT 1"
    );

    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, "ss", $u, $p);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 1) {

        mysqli_stmt_bind_result($stmt, $id, $username_db);
        mysqli_stmt_fetch($stmt);

        $_SESSION['admin_id'] = $id;
        $_SESSION['admin_user'] = $username_db;

        mysqli_stmt_close($stmt);
        return true;
    }

    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Logout admin
 */
function logout_admin() {

    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
?>