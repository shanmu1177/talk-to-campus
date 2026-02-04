<?php
// admin/logout.php
session_start();

// If the user already confirmed via the query string, destroy the session server-side
if (isset($_GET['confirm']) && $_GET['confirm'] === '1') {
    // clear session array
    $_SESSION = array();

    // remove session cookie if used
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // destroy session
    session_destroy();

    // redirect to login
    header('Location: login.php');
    exit;
}

// Otherwise show a small page that asks for confirmation in JS
// If JS not available, show simple links to confirm or cancel.
$ref = !empty($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'dashboard.php';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Logout — Confirm</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f6f8fb;margin:0;height:100vh;
    display:flex;align-items:center;justify-content:center}
    .card{background:#fff;padding:22px;border-radius:12px;box-shadow:0 12px 30px rgba(10,10,30,0.06);
    max-width:420px;text-align:center}
    .btn{display:inline-block;padding:10px 14px;border-radius:8px;margin:8px 6px;text-decoration:none;font-weight:600}
    .btn-yes{background:linear-gradient(90deg,#6f4df4,#c44ed8);color:#fff}
    .btn-no{background:#fff;color:#6f4df4;border:1px solid #e6e9f2}
    p{color:#333}
  </style>
</head>
<body>
  <div class="card" role="dialog" aria-labelledby="logoutTitle" aria-describedby="logoutDesc">
    <h2 id="logoutTitle">Confirm logout</h2>
    <p id="logoutDesc">Are you sure you want to log out?</p>

    <div>
      <a id="yesBtn" class="btn btn-yes" href="logout.php?confirm=1">Yes, log me out</a>
      <a id="noBtn" class="btn btn-no" href="<?php echo $ref; ?>">No, take me back</a>
    </div>

    <noscript style="display:block;margin-top:12px;color:#666">
      JavaScript is disabled in your browser. Click "Yes, log me out" to end the session or "No" to cancel.
    </noscript>
  </div>

  <script>
    // show native confirm dialog immediately (optional)
    // If user accepts, navigate to ?confirm=1 to trigger server-side logout.
    // If cancels, go back to referrer or dashboard.
    (function(){
      try {
        var res = confirm('Are you sure you want to log out?');
        if (res) {
          // proceed to server logout
          window.location = 'logout.php?confirm=1';
        } else {
          // go back to referring page or dashboard
          var back = '<?php echo $ref; ?>';
          window.location = back;
        }
      } catch (e) {
        // if confirm not available, do nothing (user can use the buttons)
      }
    })();
  </script>
</body>
</html>
