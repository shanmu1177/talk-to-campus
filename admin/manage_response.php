<?php
session_start();

include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';

require_admin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mode = $id ? 'edit' : 'add';

$title = '';
$reply = '';
$variants = array();

/* SAVE FORM */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title = trim($_POST['title']);
    $reply = trim($_POST['reply']);
    $variants_raw = trim($_POST['variants']);

    if ($mode == 'edit') {

        $stmt = mysqli_prepare($mysqli,
            "UPDATE responses SET title = ?, reply = ? WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "ssi", $title, $reply, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    } else {

        $stmt = mysqli_prepare($mysqli,
            "INSERT INTO responses (title, reply, created_at)
             VALUES (?, ?, NOW())"
        );

        mysqli_stmt_bind_param($stmt, "ss", $title, $reply);
        mysqli_stmt_execute($stmt);

        $id = mysqli_insert_id($mysqli);

        mysqli_stmt_close($stmt);
        $mode = 'edit';
    }

    /* DELETE OLD VARIANTS */
    $del = mysqli_prepare($mysqli,
        "DELETE FROM questions WHERE response_id = ?"
    );
    mysqli_stmt_bind_param($del, "i", $id);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);

    /* INSERT NEW VARIANTS */
    if ($variants_raw != '') {

        $lines = preg_split("/\r\n|\n|\r/", $variants_raw);

        $stmt = mysqli_prepare($mysqli,
            "INSERT INTO questions (response_id, question)
             VALUES (?, ?)"
        );

        foreach ($lines as $ln) {

            $q = trim($ln);
            if ($q == '') continue;

            mysqli_stmt_bind_param($stmt, "is", $id, $q);
            mysqli_stmt_execute($stmt);
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: responses.php");
    exit;
}


/* LOAD EXISTING DATA */
if ($id > 0) {

    $stmt = mysqli_prepare($mysqli,
        "SELECT title, reply FROM responses WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $title, $reply);

    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        die("Response not found.");
    }

    mysqli_stmt_close($stmt);

    /* Load variants */
    $stmt2 = mysqli_prepare($mysqli,
        "SELECT question FROM questions WHERE response_id = ?"
    );

    mysqli_stmt_bind_param($stmt2, "i", $id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_bind_result($stmt2, $question_val);

    while (mysqli_stmt_fetch($stmt2)) {
        $variants[] = $question_val;
    }

    mysqli_stmt_close($stmt2);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><title><?php echo $mode === 'edit' ? 'Edit' : 'Add'; ?> Response</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --purple1:#7b4df4;
      --purple2:#d44fd8;
      --bg:#f6f8fb;
      --card:#ffffff;
      --muted:#6b6f7a;
      --teal:#0ea5a4;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family: "Segoe UI", Roboto, Arial, sans-serif;background:var(--bg);color:#222}
    /* Top bar */
  .topbar{background:linear-gradient(90deg,var(--purple1),var(--purple2));color:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 6px 18px rgba(20,10,40,0.06)}
  .brand { display:flex; flex-direction:column; }
  .brand .title { font-weight:700; font-size:18px; line-height:1; }
  .brand .subtitle { font-size:12px; opacity:0.95; margin-top:4px; }
  .topbar .right { font-size:14px; }

  .topbar a{color:#fff;text-decoration:underline;margin-left:14px;font-weight:600}
  
    .layout{display:flex;min-height:calc(100vh - 56px)}
    .sidebar{width:220px;background:var(--teal);color:#fff;padding:22px 12px}
    .sidebar h3{color:#fff;margin:6px 12px 18px;font-size:18px}
    .nav{list-style:none;padding:0;margin:12px 0}
    .nav a{display:block;color:#fff;text-decoration:none;padding:10px 12px;border-radius:8px}
    .nav a.active, .nav a:hover{background:rgba(255,255,255,0.08)}
    .content{flex:1;padding:28px;max-width:1000px;margin:0 auto}

    .panel { background:var(--card); padding:18px; border-radius:12px; box-shadow:0 8px 30px rgba(10,10,30,0.04) }
    h2 { margin:0 0 12px }
    label{display:block;margin:12px 0 6px;font-weight:600}
    input[type="text"], textarea { width:100%; padding:12px; border-radius:8px; border:1px solid #eef2f7; font-size:14px; background:#fff; }
    textarea { min-height:160px; resize:vertical }
    .actions { margin-top:16px }
    .btn { display:inline-block; padding:10px 14px; border-radius:8px; text-decoration:none; font-weight:600; cursor:pointer }
    .btn-primary { background:linear-gradient(90deg,var(--purple1),var(--purple2)); color:#fff; border:0 }
    .btn-ghost { background:transparent; color:var(--purple1); border:0; margin-left:12px; text-decoration:underline }
    .note { color:var(--muted); font-size:13px; margin-top:10px }
    .form-row { margin-bottom:8px }
  </style>
</head>
<body>

  <div class="topbar" role="banner">
    <div class="brand" aria-label="Site title">
      <div class="title">Talk To Campus</div>
      <div class="subtitle">Smart Campus Assistant</div>
    </div>
    <div>
      Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?> |
      <a href="dashboard.php">Dashboard</a> |
      <a href="responses.php">Responses</a> |
      <a style="color:#fff;text-decoration:underline" href="/TALK-TO-CAMPUS">Go to Chatbot</a></div>
    </div>
  </div>

  <div class="layout">
    <aside class="sidebar" role="navigation">
      <h3><u>Admin Dashboard</u></h3>
      <ul class="nav">
        <li><a href="dashboard.php">Overview</a></li>
        <li><a href="responses.php">Responses List</a></li>
        <li><a href="unanswered.php">Unanswered List</a></li>
        <li><a href="settings.php">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </aside>

    <main class="content" role="main">
      <h2><?php echo $mode === 'edit' ? 'Edit' : 'Add'; ?> Response</h2>

      

      <div class="panel" role="region" aria-labelledby="responseForm">
        <form method="post" action="">
          <div class="form-row">
            <label for="title">Title </label>
            <input id="title" name="title" type="text" value="<?php echo htmlspecialchars($title); ?>" placeholder="Short title, e.g. FAQs - Admission & Fees" />
          </div>

          <div class="form-row">
            <label for="reply">Reply (HTML allowed)</label>
            <textarea id="reply" name="reply"><?php echo htmlspecialchars($reply); ?></textarea>
          </div>

          <div class="form-row">
            <label for="variants">Question variants (one per line)</label>
            <textarea id="variants" name="variants" placeholder="e.g. campus location&#10;where is the campus"><?php echo htmlspecialchars(implode("\n", $variants)); ?></textarea>
            <div class="note">Variants are alternate user phrasings that map to this reply. If none, admin can later map unanswered queries.</div>
          </div>

          <div class="actions">
            <button class="btn btn-primary" type="submit"><?php echo $mode === 'edit' ? 'Save Changes' : 'Create Response'; ?></button>
            <a class="btn btn-ghost" href="responses.php">Cancel</a>
          </div>
        </form>
      </div>

    </main>
  </div>
<?php include 'footer.php'; ?>

</body>
</html>
