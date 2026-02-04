<?php
// includes/functions.php
// Core rule-based chatbot logic and helpers for PHP 5.6

// Make sure db is available
if (!isset($mysqli)) {
    // try to include db automatically if not present
    if (file_exists(__DIR__ . '/db.php')) {
        include_once __DIR__ . '/db.php';
    } else {
        trigger_error("Missing mysqli connection. Include includes/db.php before includes/functions.php", E_USER_WARNING);
    }
}

/**
 * get_system_info
 * returns associative array with keys: site_title, intro_msg, no_result_msg
 */
function get_system_info() {
    global $mysqli;
    $default = array(
        'site_title' => defined('SITE_TITLE') ? SITE_TITLE : 'Talk To Campus',
        'intro_msg' => "Hello! I'm Talk To Campus Bot. How can I assist you today?",
        'no_result_msg' => "I couldn't find an answer for that. We'll ask admin to check."
    );

    if (!isset($mysqli)) return $default;

    $res = $mysqli->query("SELECT site_title, intro_msg, no_result_msg FROM system_info LIMIT 1");
    if ($res && ($row = $res->fetch_assoc())) {
        return array_merge($default, $row);
    }
    return $default;
}

/**
 * find_response_for_query
 * Input: $query string, $quick string|null (e.g. 'campus_info', 'academics', 'events', 'faqs')
 * Returns: reply string (HTML). If none found, inserts to 'unanswered' and returns no_result_msg.
 */
function find_response_for_query($query, $quick = null) {
    global $mysqli;

    $query_trim = trim($query);
    $query_low = mb_strtolower($query_trim, 'UTF-8');

    // 1) If quick action provided, map to some known keywords or look up response by title
    if ($quick) {
        // map quick keys to response title keywords (adjust to your DB entries)
        $map = array(
            'campus_info' => 'Campus Info',
            'academics'   => 'Academics',
            'events'      => 'Events',
            'faqs'        => 'FAQs'
        );
        if (isset($map[$quick])) {
            $title_like = '%' . $map[$quick] . '%';
            $stmt = $mysqli->prepare("SELECT reply FROM responses WHERE title LIKE ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $title_like);
                $stmt->execute();
                $r = $stmt->get_result();
                if ($row = $r->fetch_assoc()) {
                    $stmt->close();
                    return $row['reply'];
                }
                $stmt->close();
            }
        }
    }

    // 2) Exact match (questions table)
    $stmt = $mysqli->prepare("SELECT r.reply
                              FROM questions q
                              JOIN responses r ON r.id = q.response_id
                              WHERE LOWER(q.question) = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $query_low);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && ($row = $res->fetch_assoc())) {
            $stmt->close();
            return $row['reply'];
        }
        $stmt->close();
    }

    // 3) Keyword search (split by spaces, search questions LIKE)
    $words = preg_split('/\s+/', $query_low);
    foreach ($words as $w) {
        $w = trim($w);
        if ($w === '' || strlen($w) < 2) continue; // skip tiny words
        $like = '%' . $w . '%';
        $stmt = $mysqli->prepare("SELECT r.reply
                                  FROM questions q
                                  JOIN responses r ON r.id = q.response_id
                                  WHERE q.question LIKE ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $like);
            $stmt->execute();
            $res2 = $stmt->get_result();
            if ($res2 && ($row2 = $res2->fetch_assoc())) {
                $stmt->close();
                return $row2['reply'];
            }
            $stmt->close();
        }
    }

    // 4) No match — insert or increment unanswered table
    if (isset($mysqli)) {
        // try to find existing
        $stmt = $mysqli->prepare("SELECT id, cnt FROM unanswered WHERE query = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $query_trim);
            $stmt->execute();
            $res3 = $stmt->get_result();
            if ($u = $res3->fetch_assoc()) {
                $stmt->close();
                $newcnt = (int)$u['cnt'] + 1;
                $upd = $mysqli->prepare("UPDATE unanswered SET cnt = ? WHERE id = ?");
                if ($upd) {
                    $upd->bind_param('ii', $newcnt, $u['id']);
                    $upd->execute();
                    $upd->close();
                }
            } else {
                $stmt->close();
                $ins = $mysqli->prepare("INSERT INTO unanswered (query, cnt, created_at) VALUES (?, 1, NOW())");
                if ($ins) {
                    $ins->bind_param('s', $query_trim);
                    $ins->execute();
                    $ins->close();
                }
            }
        }
    }

    // Return system no-result message
    $sys = get_system_info();
    return isset($sys['no_result_msg']) ? $sys['no_result_msg'] : "I don't know this yet. Admin will check.";
}

/**
 * sanitize_output
 * Minimal helper to safely output reply text (if you expect HTML allowed, skip htmlentities)
 * Here we assume replies may include safe HTML (admin enters), so we return raw.
 * Use this if you want to escape: return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
 */
function sanitize_output($s) {
    return $s;
}
