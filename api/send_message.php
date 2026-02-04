<?php
// api/send_message.php
// Expects POST: q (string), is_quick (0|1)
// Returns JSON: { "success": true|false, "reply": "<html string>", "error": "..." }

header('Content-Type: application/json; charset=utf-8');

// start session so chat.php or other pages can use it (if you want)
if (session_status() === PHP_SESSION_NONE) session_start();

// helper to always return JSON and exit
function json_exit($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// Read POST params
$q = isset($_POST['q']) ? trim($_POST['q']) : '';
$is_quick = isset($_POST['is_quick']) && intval($_POST['is_quick']) === 1 ? 1 : 0;

// Default fallback messages
$default_intro = "Hello! I'm Talk To Campus Bot. How can I assist you today?";
$default_noresult = "I couldn't find an answer for that. The admin will check and reply soon.";

// Try to include functions and DB. Use @ to suppress raw warnings in UI; we'll handle errors.
$has_db = false;
$has_funcs = false;
$mysqli = null;
$sys = array('intro_msg' => $default_intro, 'no_result_msg' => $default_noresult);

// attempt to include config + db + functions
$base_includes = __DIR__ . '/../includes';
if (file_exists($base_includes . '/config.php')) {
    @include_once $base_includes . '/config.php';
}
if (file_exists($base_includes . '/db.php')) {
    @include_once $base_includes . '/db.php'; // should create $mysqli
    if (isset($mysqli) && $mysqli && ($mysqli instanceof mysqli)) {
        $has_db = true;
    }
}
if (file_exists($base_includes . '/functions.php')) {
    @include_once $base_includes . '/functions.php';
    // functions.php defines get_system_info() and find_response_for_query()
    if (function_exists('get_system_info')) $has_funcs = true;
}

// If functions available, load system info
if ($has_funcs) {
    $sys = get_system_info();
} else {
    // If no functions but DB exists try to fetch system_info manually
    if ($has_db) {
        $res = $mysqli->query("SELECT site_title,intro_msg,no_result_msg FROM system_info LIMIT 1");
        if ($res && ($row = $res->fetch_assoc())) {
            if (!empty($row['intro_msg'])) $sys['intro_msg'] = $row['intro_msg'];
            if (!empty($row['no_result_msg'])) $sys['no_result_msg'] = $row['no_result_msg'];
        }
    }
}

// If empty query and quick action missing -> return error
if ($q === '' && $is_quick === 0) {
    json_exit(array('success' => false, 'reply' => $sys['no_result_msg'], 'error' => 'Empty query'));
}

// If quick action (payload) — treat q as payload or use mapping
if ($is_quick) {
    // allow either 'q' as payload or a separate quick param
    $payload = $q;
    if ($payload === '' && isset($_POST['quick'])) $payload = trim($_POST['quick']);

    // If functions exist, use them to get reply for quick
    if ($has_funcs) {
        $reply = find_response_for_query($payload, $payload);
        json_exit(array('success' => true, 'reply' => $reply));
    }

    // fallback: map some known quick keys to a simple reply or to DB titles
    $map = array(
        'campus_info' => 'Campus Info',
        'academics'   => 'Academics',
        'events'      => 'Events',
        'faqs'        => 'FAQs'
    );
    if ($has_db && isset($map[$payload])) {
        $title_like = '%' . $mysqli->real_escape_string($map[$payload]) . '%';
        $stmt = $mysqli->prepare("SELECT reply FROM responses WHERE title LIKE ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $title_like);
            $stmt->execute();
            $r = $stmt->get_result();
            if ($row = $r->fetch_assoc()) {
                $stmt->close();
                json_exit(array('success' => true, 'reply' => $row['reply']));
            }
            $stmt->close();
        }
    }

    // fallback reply when nothing else
    json_exit(array('success' => true, 'reply' => $sys['intro_msg']));
}

// Normal text message — use functions if present
if ($has_funcs) {
    $reply = find_response_for_query($q, null);
    json_exit(array('success' => true, 'reply' => $reply));
}

// If no functions but DB exists: do simple matching
if ($has_db) {
    $q_low = mb_strtolower($q, 'UTF-8');

    // exact match first
    $stmt = $mysqli->prepare("SELECT r.reply FROM questions q JOIN responses r ON r.id = q.response_id WHERE LOWER(q.question)
     = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $q_low);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $stmt->close();
            json_exit(array('success' => true, 'reply' => $row['reply']));
        }
        $stmt->close();
    }

    // keyword search
    $words = preg_split('/\s+/', $q_low);
    foreach ($words as $w) {
        $w = trim($w);
        if ($w === '' || strlen($w) < 2) continue;
        $like = '%' . $w . '%';
        $stmt = $mysqli->prepare("SELECT r.reply FROM questions q JOIN responses r ON r.id = q.response_id WHERE
         q.question LIKE ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $like);
            $stmt->execute();
            $res2 = $stmt->get_result();
            if ($row2 = $res2->fetch_assoc()) {
                $stmt->close();
                json_exit(array('success' => true, 'reply' => $row2['reply']));
            }
            $stmt->close();
        }
    }

    // No match: insert or increment unanswered
    $stmt = $mysqli->prepare("SELECT id,cnt FROM unanswered WHERE query = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $q);
        $stmt->execute();
        $res3 = $stmt->get_result();
        if ($u = $res3->fetch_assoc()) {
            $stmt->close();
            $newcnt = (int)$u['cnt'] + 1;
            $upd = $mysqli->prepare("UPDATE unanswered SET cnt = ? WHERE id = ?");
            if ($upd) { $upd->bind_param('ii', $newcnt, $u['id']); $upd->execute(); $upd->close(); }
        } else {
            $stmt->close();
            $ins = $mysqli->prepare("INSERT INTO unanswered (query,cnt,created_at) VALUES (?,1,NOW())");
            if ($ins) { $ins->bind_param('s', $q); $ins->execute(); $ins->close(); }
        }
    }

    json_exit(array('success' => true, 'reply' => $sys['no_result_msg']));
}

// If we reach here, no DB and no functions — return safe reply
json_exit(array('success' => false, 'reply' => $default_noresult, 'error' => 'No DB or functions available'));
