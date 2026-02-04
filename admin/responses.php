<?php
// admin/responses.php
session_start();

include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';
include_once __DIR__ . '/../includes/functions.php';

require_admin();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// handle delete (GET) - simple flow as before
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delid = intval($_GET['delete']);
    $stmt = $mysqli->prepare("DELETE FROM responses WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $delid);
        $stmt->execute();
        $stmt->close();
        header('Location: responses.php');
        exit;
    }
}

// fetch responses with variant counts
$responses = array();
$q = "SELECT r.id, r.title, r.reply, COUNT(q.id) AS variants
      FROM responses r
      LEFT JOIN questions q ON q.response_id = r.id
      GROUP BY r.id
      ORDER BY r.id DESC";
$r = $mysqli->query($q);
if ($r) {
    while ($row = $r->fetch_assoc()) $responses[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Responses — Admin — Talk To Campus</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
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

    /* table */
    .table-wrap{overflow:auto;padding:18px}
    table { width:100%; border-collapse:collapse; min-width:900px; }
    thead th { text-align:left; padding:12px 10px; border-bottom:2px solid #eef2f7; color:#333; font-weight:700; }
    tbody td { padding:12px 10px; border-bottom:1px solid #f6f7fa; vertical-align:middle; color:#333; }
    .reply-preview { max-width:720px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#444 }

    /* actions */
    .action-wrap { position:relative; display:inline-block }
    .action-toggle { background:#0ea5a4; color:#fff; padding:7px 10px; border-radius:6px; cursor:pointer; font-size:13px; text-decoration:none }
    .action-menu { position:absolute; right:0; top:calc(100% + 6px); background:#fff; border-radius:6px; box-shadow:0 8px 20px rgba(0,0,0,0.12); min-width:140px; display:none; z-index:20; }
    .action-menu a { display:block; padding:10px 12px; color:#333; text-decoration:none; border-bottom:1px solid #f1f3f7 }
    .action-menu a:last-child { border-bottom:0 }
    .action-menu a:hover { background:#f8f9fb; }

    .note { color:var(--muted); padding:12px 18px; font-size:14px }

    @media (max-width:900px){
      .sidebar{display:none}
      .container{padding:18px}
      table{min-width:700px}
      .reply-preview { max-width:320px }
    }
  </style>
</head>
<body>

<div class="topbar" role="banner">
    <div class="brand" aria-label="Site title">
      <div class="title">Talk To Campus</div>
      <div class="subtitle">Smart Campus Assistant</div>
    </div>
    
    <div class="right">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | <a style="color:#fff;text-decoration:underline" href="chat.php">Go to Chatbot</a></div>
  </div>

  <div class="layout">
    <aside class="sidebar" role="navigation" aria-label="Admin menu">
      <h3>Admin Dashboard</h3>
      <ul class="nav" role="menu">
        <li role="none"><a role="menuitem" href="dashboard.php">Overview</a></li>
        <li role="none"><a role="menuitem" href="responses.php" class="active">Responses List</a></li>
        <li role="none"><a role="menuitem" href="unanswered.php">Unanswered List</a></li>
        <li role="none"><a role="menuitem" href="settings.php">Settings</a></li>
        <li role="none"><a role="menuitem" href="logout.php">Logout</a></li>
      </ul>
    </aside>

    <main class="container" role="main">
      <div class="panel" role="region" aria-labelledby="responsesTitle">
        <div class="panel-head">
          <div>
            <h2 id="responsesTitle">Responses</h2>
            <div class="sub">List of Questions & Responses</div>
          </div>
          <div>
            <a class="create" href="manage_response.php">+ Create New</a>
          </div>
        </div>

        <div class="controls" role="toolbar" aria-label="Table controls">
          <div class="left">
            <label for="showCount">Show</label>
            <select id="showCount" aria-controls="responsesTable">
              <option value="5">5</option>
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
            <span style="color:var(--muted)">entries</span>
          </div>

          <div class="right">
            <label for="searchBox" style="margin-right:8px;color:var(--muted)">Search:</label>
            <input id="searchBox" type="search" placeholder="Search responses..." aria-label="Search responses">
          </div>
        </div>

        <div class="table-wrap" id="tableWrap">
          <table id="responsesTable" aria-describedby="responsesTitle">
            <thead>
              <tr>
                <th style="width:70px">#</th>
                <th>Question / Title</th>
                <th style="width:120px">Variants</th>
                <th>Response</th>
                <th style="width:160px">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($responses) === 0): ?>
                <tr><td colspan="5" style="padding:22px;text-align:center;color:var(--muted)">No responses yet. Add a response to begin.</td></tr>
              <?php else: $i=1; foreach ($responses as $row): 
                  $id = intval($row['id']);
                  $title = htmlspecialchars($row['title']);
                  $variants = intval($row['variants']);
                  $reply = strip_tags($row['reply']);
                  $preview = htmlspecialchars(mb_substr($reply, 0, 150, 'UTF-8'));
              ?>
                <tr>
                  <td><?php echo $id; ?></td>
                  <td><?php echo $title; ?></td>
                  <td><?php echo $variants; ?></td>
                  <td class="reply-preview" title="<?php echo htmlspecialchars($reply); ?>"><?php echo $preview; ?><?php echo (strlen($reply) > 150) ? '...' : ''; ?></td>
                  <td>
                    <div class="action-wrap">
                      <span class="action-toggle" tabindex="0" aria-haspopup="true">Action ▾</span>
                      <div class="action-menu" role="menu" aria-hidden="true">
                        <a role="menuitem" href="manage_response.php?id=<?php echo $id; ?>">Edit</a>
                        <a role="menuitem" href="responses.php?delete=<?php echo $id; ?>" onclick="return confirm('Delete this response?');">Delete</a>
                        <a role="menuitem" href="manage_response.php?create_variant_from=<?php echo $id; ?>">Add Variant</a>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <div class="note">Tip: Use <strong>Create New</strong> to add a response / answer, then add question variants (keywords) via Manage Response.</div>
      </div>

    </main>
  </div>

<script>
// small client-side features: search + show count + action dropdown toggles

(function(){
  var searchBox = document.getElementById('searchBox');
  var showCount = document.getElementById('showCount');
  var table = document.getElementById('responsesTable');
  var tbody = table.querySelector('tbody');
  var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

  function filterAndPaginate(){
    var q = (searchBox.value || '').toLowerCase().trim();
    var max = parseInt(showCount.value, 10) || 10;
    var visible = 0;
    rows.forEach(function(r){
      // skip placeholder "no rows" row
      if (!r.querySelectorAll('td').length) return;
      var text = r.textContent.toLowerCase();
      var match = q === '' || text.indexOf(q) !== -1;
      if (match && visible < max){
        r.style.display = '';
        visible++;
      } else {
        r.style.display = match ? 'none' : 'none';
      }
    });
    // if no rows visible, show the no-data row if exists
    var noRow = tbody.querySelector('.no-data-row');
    if (!visible) {
      // check if an explicit "no responses" row exists; if not, create it
      if (!tbody.querySelector('.no-data-row')) {
        var tr = document.createElement('tr');
        tr.className = 'no-data-row';
        tr.innerHTML = '<td colspan="5" style="padding:18px;text-align:center;color:var(--muted)">No matching responses</td>';
        tbody.appendChild(tr);
      }
    } else {
      var existing = tbody.querySelector('.no-data-row');
      if (existing) existing.parentNode.removeChild(existing);
    }
  }

  // attach
  searchBox.addEventListener('input', filterAndPaginate);
  showCount.addEventListener('change', filterAndPaginate);

  // initial run
  filterAndPaginate();

  // action dropdown behaviour
  document.addEventListener('click', function(e){
    var allMenus = document.querySelectorAll('.action-menu');
    var clickedToggle = e.target.closest('.action-toggle');
    if (clickedToggle) {
      var wrap = clickedToggle.parentNode;
      var menu = wrap.querySelector('.action-menu');
      var isShown = menu.style.display === 'block';
      // hide all
      Array.prototype.forEach.call(allMenus, function(m){ m.style.display = 'none'; m.setAttribute('aria-hidden','true'); });
      // toggle
      if (!isShown) { menu.style.display = 'block'; menu.setAttribute('aria-hidden','false'); }
      return;
    }
    if (!e.target.closest('.action-wrap')) {
      Array.prototype.forEach.call(allMenus, function(m){ m.style.display = 'none'; m.setAttribute('aria-hidden','true'); });
    }
  });

  // keyboard: toggle Open with Enter/Space when focused
  Array.prototype.forEach.call(document.querySelectorAll('.action-toggle'), function(el){
    el.addEventListener('keydown', function(ev){
      if (ev.keyCode === 13 || ev.keyCode === 32) {
        ev.preventDefault();
        el.click();
      }
    });
  });

})();
</script>

</body>
</html>
