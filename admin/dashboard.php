<?php
session_start();

include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';

require_admin();


/* Fetch stats */
$stats = array(
    'responses' => 0,
    'questions' => 0,
    'unanswered' => 0,
    'users' => 0
);

/* Count responses */
$result = mysqli_query($mysqli, "SELECT COUNT(*) AS c FROM responses");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['responses'] = $row['c'];
}

/* Count questions */
$result = mysqli_query($mysqli, "SELECT COUNT(*) AS c FROM questions");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['questions'] = $row['c'];
}

/* Count unanswered */
$result = mysqli_query($mysqli, "SELECT COUNT(*) AS c FROM unanswered");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['unanswered'] = $row['c'];
}

/* Count users */
$result = mysqli_query($mysqli, "SELECT COUNT(*) AS c FROM users");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['users'] = $row['c'];
}

/* Fetch unanswered list */
$unanswered = array();

$res = mysqli_query($mysqli,
    "SELECT id, query, cnt, created_at 
     FROM unanswered 
     ORDER BY created_at DESC"
);

if ($res) {
    while ($rw = mysqli_fetch_assoc($res)) {
        $unanswered[] = $rw;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard — Talk To Campus</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  :root{
    --purple1:#7b4df4;
    --purple2:#d44fd8;
    --bg:#f6f8fb;
    --card:#ffffff;
    --muted:#6b6f7a;
    --sidebar:#0ea5a4;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family: "Segoe UI", Roboto, Arial, sans-serif;background:var(--bg);color:#222}
  /* Top bar */
  .topbar{background:linear-gradient(90deg,var(--purple1),var(--purple2));color:#fff;padding:12px 20px;
    display:flex;align-items:center;justify-content:space-between;box-shadow:0 6px 18px rgba(20,10,40,0.06)}
  .brand { display:flex; flex-direction:column; }
  .brand .title { font-weight:700; font-size:18px; line-height:1; }
  .brand .subtitle { font-size:12px; opacity:0.95; margin-top:4px; }
  .topbar .right { font-size:14px; }

  .topbar a{color:#fff;text-decoration:underline;margin-left:14px;font-weight:600}

  /* Layout */
  .layout{display:flex;min-height:calc(100vh - 56px)} /* leave topbar */
  .sidebar{width:220px;background:var(--sidebar);color:#fff;padding:22px 18px}
  .sidebar h3{color:#fff;margin:6px 0 18px;font-size:18px}
  .nav{list-style:none;padding:0;margin:12px 0}
  .nav li{margin:8px 0}
  .nav a{display:block;color:#fff;text-decoration:none;padding:12px 12px;border-radius:8px}
  .nav a.active, .nav a:hover{background:rgba(255,255,255,0.08)}

  .content{flex:1;padding:28px;max-width:1200px;margin:0 auto}

  /* Header area */
  .page-head { margin-bottom:18px; }
  .page-head h1 { margin:0 0 6px; font-size:28px; color:#222; }
  .page-head p { margin:0; color:var(--muted); }

  /* Stats row */
  .stats-row { display:flex; gap:18px; margin-bottom:22px; flex-wrap:wrap; }
  .stat-card { flex:1; min-width:180px; background:#fff; padding:18px; border-radius:12px; 
    box-shadow:0 8px 20px rgba(10,10,30,0.04); text-align:left; }
  .stat-card h4{margin:0;font-size:14px;color:var(--muted)}
  .stat-card .num{font-size:28px;font-weight:700;margin-top:8px}

  /* Unanswered panel */
  .panel { background:#fff; padding:20px; border-radius:12px; box-shadow:0 8px 30px rgba(10,10,30,0.04) }
  .panel h3 { margin:0 0 6px; }
  .panel p.desc { color:var(--muted); margin:0 0 12px; }

  table.full { width:100%; border-collapse:collapse; margin-top:10px; }
  table.full thead th { text-align:left; padding:14px 12px; border-bottom:1px solid #eef2f7; font-weight:700;
     color:#333 }
  table.full tbody td { padding:14px 12px; border-bottom:1px solid #f6f7fa; vertical-align:middle }
  .action-btn { background:linear-gradient(90deg,var(--purple1),var(--purple2)); color:#fff; padding:8px 12px;
     border-radius:8px; text-decoration:none; display:inline-block }
  .small { font-size:13px; color:var(--muted) }

  /* responsive */
  @media (max-width:900px){
    .sidebar{display:none}
    .content{padding:18px}
    .stat-card{min-width:45%}
  }
</style>
</head>
<body>

  <div class="topbar" role="banner">
    <div class="brand" aria-label="Site title">
      <div class="title">Talk To Campus</div>
      <div class="subtitle">Smart Campus Assistant</div>
    </div>

    <div class="right">
      WELCOME, <?php echo htmlspecialchars($_SESSION['admin_user']); ?> |
      <a href="/TALK-TO-CAMPUS/">Go to Chatbot</a>
    </div>
  </div>

  <div class="layout">

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar" role="navigation" aria-label="Admin menu">
      <h3>Admin Dashboard</h3>
      <ul class="nav" role="menu">
        <li role="none"><a role="menuitem" href="dashboard.php" class="active">Overview</a></li>
        <li role="none"><a role="menuitem" href="responses.php">Responses List</a></li>
        <li role="none"><a role="menuitem" href="unanswered.php">Unanswered List</a></li>
        <li role="none"><a role="menuitem" href="settings.php">Settings</a></li>
        <li role="none"><a role="menuitem" href="logout.php">Logout</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content" role="main">

      <div class="page-head">
        <h1>Welcome to Admin Dashboard</h1>
        <p>Manage your chatbot!</p>
      </div>

      <!-- Stats cards -->
      <div class="stats-row" aria-hidden="false">
        <div class="stat-card" role="region" aria-label="Responses">
          <h4>Responses</h4>
          <div class="num"><?php echo intval($stats['responses']); ?></div>
          <div class="small">Total prepared replies</div>
        </div>

        <div class="stat-card" role="region" aria-label="FAQ Questions">
          <h4>FAQ Questions</h4>
          <div class="num"><?php echo intval($stats['questions']); ?></div>
          <div class="small">Mapped FAQ / question variants</div>
        </div>

        <div class="stat-card" role="region" aria-label="Unanswered Questions">
          <h4>Unanswered Questions</h4>
          <div class="num"><?php echo intval($stats['unanswered']); ?></div>
          <div class="small">Queries to review</div>
        </div>

        <div class="stat-card" role="region" aria-label="Admin Accounts">
          <h4>Admin Accounts</h4>
          <div class="num"><?php echo intval($stats['users']); ?></div>
          <div class="small">Configured admins</div>
        </div>
      </div>

      <!-- Unanswered table -->
      <div class="panel" role="region" aria-labelledby="unansweredTitle">
        <h3 id="unansweredTitle">Unanswered Questions</h3>
        <p class="desc">List of user queries not yet linked to responses. Use Action →
           Create Response to map answers.</p>

        <table class="full" aria-describedby="unansweredTitle">
          <thead>
            <tr>
              <th style="width:60px">#</th>
              <th>Question</th>
              <th style="width:140px">Total Who Asks</th>
              <th style="width:180px">When</th>
              <th style="width:160px">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (count($unanswered) === 0) {
                echo '<tr><td colspan="5" style="padding:22px;text-align:center;color:var(--muted)">
                No unanswered queries</td></tr>';
            } else {
                $i = 1;
                foreach ($unanswered as $u) {
                    $id = intval($u['id']);
                    $q = htmlspecialchars($u['query']);
                    $cnt = intval($u['cnt']);
                    $when = htmlspecialchars($u['created_at']);
                    echo "<tr>";
                    echo "<td>{$i}</td>";
                    echo "<td>{$q}</td>";
                    echo "<td>{$cnt}</td>";
                    echo "<td>{$when}</td>";
                    echo "<td>";
                    echo "<a class='action-btn' href='manage_response.php?create_from_unanswered={$id}'>
                    Create Response</a> ";
                    echo "<a style='margin-left:10px;color:#b00020;text-decoration:none' 
                    href='unanswered.php?delete={$id}' onclick=\"return confirm('Delete this unanswered query?');\">
                    Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                    $i++;
                }
            }
            ?>
          </tbody>
        </table>
      </div>

    </main>

  </div>
<?php include 'footer.php'; ?>

</body>
</html>
