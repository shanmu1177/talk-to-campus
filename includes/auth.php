<?php
// includes/auth.php
// Simple admin session helpers (designed to match older DB which uses MD5 passwords)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * is_admin_logged_in
 * returns boolean
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}


/**
 * require_admin
 * redirect to login if not logged in
 */
function require_admin() {
    if (!is_admin_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * login_admin
 * Attempts login; returns true on success, false on failure.
 * Expects $mysqli (include includes/db.php before calling)
 */
function login_admin($username, $password) {
    global $mysqli;
    if (!isset($mysqli)) return false;

    $u = trim($username);
    $p = trim($password);
    if ($u === '' || $p === '') return false;

    // legacy MD5 check (match the SQL seed)
    $pass_md5 = md5($p);

    $stmt = $mysqli->prepare("SELECT id, username FROM users WHERE username = ? AND password = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('ss', $u, $pass_md5);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_user'] = $row['username'];
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

/**
 * logout_admin
 */
function logout_admin() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
