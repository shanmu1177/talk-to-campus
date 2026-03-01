<?php
// includes/functions.php
// Fully compatible with PHP 5.6 (No get_result, No mbstring dependency)

// Ensure DB connection exists
if (!isset($mysqli)) {
    if (file_exists(__DIR__ . '/db.php')) {
        include_once __DIR__ . '/db.php';
    } else {
        trigger_error("Missing mysqli connection.", E_USER_WARNING);
    }
}

/**
 * Get system info
 */
function get_system_info() {
    global $mysqli;

    $default = array(
        'site_title' => defined('SITE_TITLE') ? SITE_TITLE : 'Talk To Campus',
        'intro_msg' => "Hello! I am Talk To Campus Bot. How can I assist you today?",
        'no_result_msg' => "I could not find an answer for that. Admin will check."
    );

    if (!isset($mysqli)) return $default;

    $sql = "SELECT site_title, intro_msg, no_result_msg FROM system_info LIMIT 1";
    $result = mysqli_query($mysqli, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return array_merge($default, $row);
    }

    return $default;
}

/**
 * Find response for query
 */
function find_response_for_query($query, $quick = null) {
    global $mysqli;

    $query_trim = trim($query);
    $query_low  = strtolower($query_trim);

    // 1) Quick buttons
    if ($quick) {
        $map = array(
            'campus_info' => 'Campus Info',
            'academics'   => 'Academics',
            'events'      => 'Events',
            'faqs'        => 'FAQs'
        );

        if (isset($map[$quick])) {
            $title_like = '%' . $map[$quick] . '%';

            $stmt = mysqli_prepare($mysqli,
                "SELECT reply FROM responses WHERE title LIKE ? LIMIT 1"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $title_like);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $reply);

                if (mysqli_stmt_fetch($stmt)) {
                    mysqli_stmt_close($stmt);
                    return $reply;
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    // 2) Exact match
    $stmt = mysqli_prepare($mysqli,
        "SELECT r.reply
         FROM questions q
         JOIN responses r ON r.id = q.response_id
         WHERE LOWER(q.question) = ? LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $query_low);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $reply_exact);

        if (mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            return $reply_exact;
        }

        mysqli_stmt_close($stmt);
    }

    // 3) Keyword search
    $words = preg_split('/\s+/', $query_low);

    foreach ($words as $w) {
        $w = trim($w);
        if ($w == '' || strlen($w) < 2) continue;

        $like = '%' . $w . '%';

        $stmt = mysqli_prepare($mysqli,
            "SELECT r.reply
             FROM questions q
             JOIN responses r ON r.id = q.response_id
             WHERE q.question LIKE ? LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $like);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $reply_like);

            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                return $reply_like;
            }

            mysqli_stmt_close($stmt);
        }
    }

    // 4) Insert / Update unanswered
    if (isset($mysqli)) {

        $stmt = mysqli_prepare($mysqli,
            "SELECT id, cnt FROM unanswered WHERE query = ? LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $query_trim);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $uid, $ucnt);

            if (mysqli_stmt_fetch($stmt)) {

                mysqli_stmt_close($stmt);

                $newcnt = $ucnt + 1;

                $upd = mysqli_prepare($mysqli,
                    "UPDATE unanswered SET cnt = ? WHERE id = ?"
                );

                if ($upd) {
                    mysqli_stmt_bind_param($upd, "ii", $newcnt, $uid);
                    mysqli_stmt_execute($upd);
                    mysqli_stmt_close($upd);
                }

            } else {

                mysqli_stmt_close($stmt);

                $ins = mysqli_prepare($mysqli,
                    "INSERT INTO unanswered (query, cnt, created_at)
                     VALUES (?, 1, NOW())"
                );

                if ($ins) {
                    mysqli_stmt_bind_param($ins, "s", $query_trim);
                    mysqli_stmt_execute($ins);
                    mysqli_stmt_close($ins);
                }
            }
        }
    }

    $sys = get_system_info();
    return isset($sys['no_result_msg'])
        ? $sys['no_result_msg']
        : "I do not know this yet.";
}

/**
 * Output sanitizer
 */
function sanitize_output($s) {
    return $s;
}
?>