<?php
session_start();

/* If confirmed → logout */
if (isset($_GET['confirm']) && $_GET['confirm'] == '1') {

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

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Logout</title>
</head>
<body>

<script>
if (confirm("Are you sure you want to logout?")) {
    window.location = "logout.php?confirm=1";
} else {
    window.location = "dashboard.php";
}
</script>

</body>
</html>