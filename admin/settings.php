<?php
// admin/settings.php - edit system_info table (intro, no-result, site title)
session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';

require_admin();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
if (file_exists(__DIR__ . '/../includes/db.php')) include_once __DIR__ . '/../includes/db.php'; else die("Missing includes/db.php");

$msg = '';
$res = $mysqli->query("SELECT * FROM system_info LIMIT 1");
$row = $res ? $res->fetch_assoc() : null;

$site_title = $row ? $row['site_title'] : 'Talk To Campus';
$intro_msg = $row ? $row['intro_msg'] : "Hello! I'm Talk To Campus Bot. How can I assist you today?";
$no_result = $row ? $row['no_result_msg'] : "I couldn't find an answer for that. We'll check with admin.";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = trim($_POST['site_title']);
    $intro_msg = trim($_POST['intro_msg']);
    $no_result = trim($_POST['no_result_msg']);

    if ($row) {
        $stmt = $mysqli->prepare("UPDATE system_info SET site_title = ?, intro_msg = ?, no_result_msg = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param('sssi', $site_title, $intro_msg, $no_result, $row['id']);
        $stmt->execute(); $stmt->close();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO system_info (site_title, intro_msg, no_result_msg) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $site_title, $intro_msg, $no_result);
        $stmt->execute(); $stmt->close();
    }
    $msg = "Saved.";
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Settings — Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{--purple1:#7b4df4;--purple2:#d44fd8;--teal:#0ea5a4;--bg:#f6f8fb}
*{box-sizing:border-box}
body{margin:0;font-family:"Segoe UI",Roboto,Arial;background:var(--bg)}
/* Top bar */
  .topbar{background:linear-gradient(90deg,var(--purple1),var(--purple2));color:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 6px 18px rgba(20,10,40,0.06)}
  .brand { display:flex; flex-direction:column; }
  .brand .title { font-weight:700; font-size:18px; line-height:1; }
  .brand .subtitle { font-size:12px; opacity:0.95; margin-top:4px; }
  .topbar .right { font-size:14px; }

  .topbar a{color:#fff;text-decoration:underline;margin-left:14px;font-weight:600}

    /* layout */
    .layout{display:flex;min-height:calc(100vh - 56px)}
    .sidebar{width:220px;background:var(--teal);color:#fff;padding:22px 18px}
    .sidebar h3{color:#fff;margin:6px 0 18px;font-size:18px}
    .nav{list-style:none;padding:0;margin:12px 0}
    .nav li{margin:8px 0}
    .nav a{display:block;color:#fff;text-decoration:none;padding:12px;border-radius:8px}
    .nav a.active, .nav a:hover{background:rgba(255,255,255,0.08)}

    .container{flex:1;padding:28px;max-width:1200px;margin:0 auto}

    /* Boxed panel header */
    .panel{background:var(--card);padding:0;border-radius:10px;box-shadow:0 8px 30px rgba(10,10,30,0.04);overflow:hidden}
    .panel-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #eef2f7;background:linear-gradient(180deg,#fff,#fafbff)}
    .panel-head h2{margin:0;font-size:18px;color:var(--text)}
    .panel-head .sub { color:var(--muted); font-size:13px; margin-left:6px }
    .panel-head .create { background:linear-gradient(90deg,var(--purple1),var(--purple2)); color:#fff; padding:8px 12px; border-radius:8px; text-decoration:none; font-weight:600; box-shadow:0 8px 18px rgba(120,60,200,0.12); }

    /* controls */
    .controls { display:flex; align-items:center; justify-content:space-between; padding:12px 18px; background:#fbfdff; border-bottom:1px solid #f1f3f7; gap:12px; flex-wrap:wrap }
    .controls .left { display:flex; align-items:center; gap:10px; color:var(--muted); }
    .controls select, .controls input[type="search"] { padding:8px 10px; border-radius:6px; border:1px solid #e6e9f0; outline:none; }


  /* Header area */
  .page-head { margin-bottom:18px; }
  .page-head h1 { margin:0 0 6px; font-size:28px; color:#222; }
  .page-head p { margin:0; color:var(--muted); }
.sidebar{width:220px;background:var(--teal);color:#fff;padding:22px}
.content{flex:1;padding:28px;max-width:900px;margin:0 auto}
.panel{background:#fff;padding:18px;border-radius:12px;box-shadow:0 8px 30px rgba(10,10,30,0.04)}
label{display:block;margin:10px 0 6px;font-weight:600}
input,textarea{width:100%;padding:12px;border-radius:8px;border:1px solid #eef2f7}
textarea{min-height:120px}
.btn{background:linear-gradient(90deg,var(--purple1),var(--purple2));color:#fff;padding:10px 14px;border-radius:8px;border:0;cursor:pointer}
.msg{color:green;margin-bottom:8px}
.small{color:#666;font-size:13px;margin-top:8px}
@media (max-width:900px){ .sidebar{display:none} }
</style>
</head>
<body>
  <div class="topbar" role="banner">
    <div class="brand" aria-label="Site title">
      <div class="title">Talk To Campus</div>
      <div class="subtitle">Smart Campus Assistant</div>
    </div>
    
    <div class="right">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | <a style="color:#fff;text-decoration:underline" href="/TALK-TO-CAMPUS">Go to Chatbot</a></div>
  </div>

  <div class="layout">
    <aside class="sidebar" role="navigation" aria-label="Admin menu">
      <h3>Admin Dashboard</h3>
      <ul class="nav" role="menu">
        <li role="none"><a role="menuitem" href="dashboard.php">Overview</a></li>
        <li role="none"><a role="menuitem" href="responses.php">Responses List</a></li>
        <li role="none"><a role="menuitem" href="unanswered.php">Unanswered List</a></li>
        <li role="none"><a role="menuitem" href="settings.php" class="active">Settings</a></li>
        <li role="none"><a role="menuitem" href="logout.php">Logout</a></li>
      </ul>
    </aside>

    <main class="content">
      <h2 style="margin:6px 0 12px">System Settings</h2>
       <?php if ($msg): ?>
    <script>
      window.onload = function () {
        alert("System Info Successfully Updated.");
      };
    </script>
  <?php endif; ?>
      <div class="panel">
        <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <form method="post" action="">
          <label>Site Title</label>
          <input name="site_title" value="<?php echo htmlspecialchars($site_title); ?>" />

          <label>Intro Message (shown when chat opens)</label>
          <textarea name="intro_msg"><?php echo htmlspecialchars($intro_msg); ?></textarea>

          <label>No-result Message (when bot can't find answer)</label>
          <textarea name="no_result_msg"><?php echo htmlspecialchars($no_result); ?></textarea>

          <div style="margin-top:12px">
            <button type="submit" class="btn">Save Settings</button>
          </div>
        </form>
      </div>
    </main>

  </div>
</body>
</html>
