<?php
session_start();

include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';

require_admin();

/* HANDLE CONVERT TO RESPONSE */
if (isset($_GET['handle']) && is_numeric($_GET['handle'])) {

    $id = intval($_GET['handle']);

    $stmt = mysqli_prepare($mysqli,
        "SELECT id, query FROM unanswered WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $uid, $query_text);

    if (mysqli_stmt_fetch($stmt)) {

        mysqli_stmt_close($stmt);

        $prepTitle = "Response for: " . substr($query_text, 0, 40);
        $genericReply = "Answer for: " . $query_text;

        /* Insert into responses */
        $stmt2 = mysqli_prepare($mysqli,
            "INSERT INTO responses (title, reply, created_at)
             VALUES (?, ?, NOW())"
        );

        mysqli_stmt_bind_param($stmt2, "ss", $prepTitle, $genericReply);
        mysqli_stmt_execute($stmt2);

        $newId = mysqli_insert_id($mysqli);

        mysqli_stmt_close($stmt2);

        /* Insert question variant */
        $stmt3 = mysqli_prepare($mysqli,
            "INSERT INTO questions (response_id, question)
             VALUES (?, ?)"
        );

        mysqli_stmt_bind_param($stmt3, "is", $newId, $query_text);
        mysqli_stmt_execute($stmt3);
        mysqli_stmt_close($stmt3);

        /* Delete unanswered */
        $stmt4 = mysqli_prepare($mysqli,
            "DELETE FROM unanswered WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt4, "i", $id);
        mysqli_stmt_execute($stmt4);
        mysqli_stmt_close($stmt4);

        header("Location: manage_response.php?id=" . $newId);
        exit;

    } else {
        mysqli_stmt_close($stmt);
        header("Location: unanswered.php");
        exit;
    }
}


/* HANDLE DELETE */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = mysqli_prepare($mysqli,
        "DELETE FROM unanswered WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: unanswered.php");
    exit;
}


/* FETCH UNANSWERED LIST */
$unanswered = array();

$result = mysqli_query($mysqli,
    "SELECT id, query, cnt, created_at
     FROM unanswered
     ORDER BY cnt DESC, created_at DESC"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $unanswered[] = $row;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><title>Unanswered Queries — Talk To Campus</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
      :root{
      --purple1:#7b4df4; --purple2:#d44fd8;
      --teal:#0ea5a4;
      --bg:#f6f8fb;
      --card:#ffffff;
      --muted:#6b6f7a;
      --text:#222;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:"Segoe UI", Roboto, Arial, sans-serif;background:var(--bg);color:var(--text)}
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

    .content{flex:1;padding:28px;max-width:1100px;margin:0 auto}
    .panel{background:#fff;padding:18px;border-radius:12px;box-shadow:0 8px 30px rgba(10,10,30,0.04)}
    h1{margin:0 0 12px}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    thead th{padding:12px 10px;text-align:left;border-bottom:1px solid #eef2f7;font-weight:700;color:#333}
    tbody td{padding:12px 10px;border-bottom:1px solid #f6f7fa}
    .btn{background:linear-gradient(90deg,var(--purple1),var(--purple2));color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none}
    .link-delete{color:#b00020;text-decoration:underline;margin-left:10px}
    .note{color:var(--muted);margin-top:8px}
    @media (max-width:900px){ .sidebar{display:none} }
  </style>
</head>
<body>
  <div class="topbar" role="banner">
    <div class="brand" aria-label="Site title">
      <div class="title">Talk To Campus</div>
      <div class="subtitle">Smart Campus Assistant</div>
    </div>
    
    <div class="right">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | <a style="color:#fff;text-decoration:underline" href="/TALK-TO-CAMPUS/">Go to Chatbot</a></div>
  </div>

  <div class="layout">
    <aside class="sidebar" role="navigation" aria-label="Admin menu">
      <h3>Admin Dashboard</h3>
      <ul class="nav" role="menu">
        <li role="none"><a role="menuitem" href="dashboard.php">Overview</a></li>
        <li role="none"><a role="menuitem" href="responses.php" >Responses List</a></li>
        <li role="none"><a role="menuitem" href="unanswered.php" class="active">Unanswered List</a></li>
        <li role="none"><a role="menuitem" href="settings.php">Settings</a></li>
        <li role="none"><a role="menuitem" href="logout.php">Logout</a></li>
      </ul>
    </aside>

    <main class="content">
      <div class="panel">
        <h1>Unanswered Queries</h1>
        <p class="note">Convert a query to a response (creates a draft response and opens its edit page)</p>

        <table aria-describedby="unansweredTable">
          <thead>
            <tr><th>Query</th><th style="width:100px">Count</th><th style="width:180px">When</th><th style="width:200px">Actions</th></tr>
          </thead>
          <tbody>
            <?php if (empty($unanswered)): ?>
            <tr>
                  <td colspan="4" style="padding:18px;text-align:center;color:var(--muted)">
                  No unanswered queries
                </td>
                </tr>
            <?php else: foreach ($unanswered as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['query']); ?></td>
                <td><?php echo intval($row['cnt']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td>
                  <a class="btn" href="unanswered.php?handle=<?php echo $row['id']; ?>">Convert</a>
                  <a class="link-delete" href="unanswered.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Remove?')">Delete</a>
                </td>
              </tr>
              <?php endforeach; endif; ?>          </tbody>
        </table>

      </div>
    </main>
  </div>
  <?php include 'footer.php'; ?>

</body>
</html>
