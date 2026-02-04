<?php
// admin/login.php
session_start();

include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';

$error = '';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php'); exit;
}

// Ensure DB connection (redundant include safe)
if (!file_exists(__DIR__ . '/../includes/db.php')) {
    die("Missing includes/db.php - create it with mysqli connection.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        $error = "Enter username and password.";
    } else {
        // NOTE: legacy MD5 from your SQL. If you change DB hashing update this.
        $pass_md5 = md5($password);
        $stmt = $mysqli->prepare("SELECT id, username FROM users WHERE username = ? AND password = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('ss', $username, $pass_md5);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_user'] = $row['username'];
                $stmt->close();
               echo "<script>
        alert('Login Successfully!!');
        window.location.href = 'dashboard.php';
      </script>";
 exit;
            } else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            $error = "Database error. Please check connection.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login — Talk To Campus</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* Page background */
    :root{
      --accent1:#7b4df4;
      --accent2:#c44ed8;
      --muted:#7b7f89;
      --card-bg:#fff;
    }
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;font-family: "Segoe UI", Roboto, Arial, sans-serif;
      background:linear-gradient(135deg,var(--accent1),var(--accent2));}

    /* Center wrapper to mimic your screenshot */
    .page {
      min-height:100%;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:40px;
    }

    .card {
      border:5-x solid #c44ed8;
      width:520px;
      background:var(--card-bg);
      border-radius:22px;
      box-shadow: 0 18px 50px rgba(10,10,30,0.18);
      padding:36px 44px;
      position:relative;
      overflow:hidden;
    }

    /* subtle top gradient panel inside card */
    .card::before{
      content:'';
      position:absolute;
      top:-120px;
      right:-120px;
      width:360px;height:360px;
      background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.06), rgba(255,255,255,0));
      transform:rotate(25deg);
      pointer-events:none;
    }

    h1 { margin:0 0 6px; font-size:36px; color:var(--accent1); text-align:center; font-weight:700; }
    p.lead { margin:0 0 22px; color:var(--muted); text-align:center; }

    form { margin-top:6px; }
    label { display:block; font-weight:600; margin-bottom:8px; color:#333; }
    .field {
      margin-bottom:18px;
    }
    input[type="text"], input[type="password"] {
      width:100%;
      padding:14px 16px;
      border-radius:12px;
      border:1px solid #7b7f89;
      font-size:15px;
      outline:none;
      box-shadow:none;
    }
    .note { font-size:13px; color:var(--muted); margin-top:6px; }

    .btn {
      width:100%;
      display:inline-block;
      padding:14px 18px;
      font-size:16px;
      font-weight:700;
      color:#fff;
      border-radius:14px;
      border:0;
      cursor:pointer;
      background: linear-gradient(90deg,var(--accent1),var(--accent2));
      box-shadow: 0 10px 30px rgba(120,50,200,0.18);
    }

    .helper {
      margin-top:12px;
      text-align:center;
      font-size:14px;
    }

    .helper a {
      color:var(--accent1);
      text-decoration:none;
    }

    .err {
      background: #fff0f0;
      color:#b00020;
      padding:10px 12px;
      border-radius:8px;
      margin-bottom:12px;
      border:1px solid rgba(176,0,32,0.08);
    }

    /* Responsive */
    @media (max-width:600px){
      .card{ width:92%; padding:22px; border-radius:14px; }
      h1{ font-size:28px; }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="card" role="main" aria-labelledby="loginTitle">
      <h1 id="loginTitle">Welcome Back!</h1>
      <p class="lead">Access the dashboard to manage your chatbot</p>

      <?php if ($error): ?>
        <div class="err"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" action="" novalidate>
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" type="text" placeholder="Enter your username" autocomplete="username" />
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" />
        </div>

        <button class="btn" type="submit">Login</button>
      </form>

      <div class="helper">
  Don't have an account?
  <a href="register.php"> Sign Up</a><br><br>

  <a href="/TALK-TO-CAMPUS/">← Back to Home</a>
</div>
    </div>
  </div>
</body>
</html>
