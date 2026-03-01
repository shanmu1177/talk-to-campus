<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_id() == '') {
    session_start();
}

function json_exit($arr) {
    echo json_encode($arr);
    exit;
}

$q = isset($_POST['q']) ? trim($_POST['q']) : '';
$is_quick = (isset($_POST['is_quick']) && intval($_POST['is_quick']) == 1) ? 1 : 0;

$default_intro = "Hello! I am Talk To Campus Bot. How can I assist you today?";
$default_noresult = "I could not find an answer for that. The admin will check and reply soon.";

$base = __DIR__ . '/../includes';

if (file_exists($base . '/config.php')) include_once $base . '/config.php';
if (file_exists($base . '/db.php')) include_once $base . '/db.php';
if (file_exists($base . '/functions.php')) include_once $base . '/functions.php';

$sys = array(
    'intro_msg' => $default_intro,
    'no_result_msg' => $default_noresult
);

if (function_exists('get_system_info')) {
    $sys = get_system_info();
}

/* Empty input */
if ($q == '' && $is_quick == 0) {
    json_exit(array('success' => false, 'reply' => $sys['no_result_msg']));
}

/* QUICK BUTTON */
if ($is_quick == 1) {

    if (function_exists('find_response_for_query')) {
        $reply = find_response_for_query($q, $q);
        json_exit(array('success' => true, 'reply' => $reply));
    }

    json_exit(array('success' => true, 'reply' => $sys['intro_msg']));
}


/* NORMAL TEXT */
if (function_exists('find_response_for_query')) {
    $reply = find_response_for_query($q, null);
    json_exit(array('success' => true, 'reply' => $reply));
}


/* FALLBACK DB LOGIC (if functions missing) */
if (isset($mysqli)) {

    $q_low = strtolower($q);

    /* EXACT MATCH */
    $stmt = mysqli_prepare($mysqli,
        "SELECT r.reply
         FROM questions q
         JOIN responses r ON r.id = q.response_id
         WHERE LOWER(q.question) = ?
         LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $q_low);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $reply_exact);

        if (mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            json_exit(array('success' => true, 'reply' => $reply_exact));
        }

        mysqli_stmt_close($stmt);
    }

    /* KEYWORD SEARCH */
    $words = preg_split('/\s+/', $q_low);

    foreach ($words as $w) {

        if ($w == '' || strlen($w) < 2) continue;

        $like = "%" . $w . "%";

        $stmt = mysqli_prepare($mysqli,
            "SELECT r.reply
             FROM questions q
             JOIN responses r ON r.id = q.response_id
             WHERE q.question LIKE ?
             LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $like);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $reply_like);

            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                json_exit(array('success' => true, 'reply' => $reply_like));
            }

            mysqli_stmt_close($stmt);
        }
    }

    /* INSERT INTO UNANSWERED */
    $stmt = mysqli_prepare($mysqli,
        "SELECT id, cnt FROM unanswered WHERE query = ? LIMIT 1"
    );

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "s", $q);
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
                mysqli_stmt_bind_param($ins, "s", $q);
                mysqli_stmt_execute($ins);
                mysqli_stmt_close($ins);
            }
        }
    }

    json_exit(array('success' => true, 'reply' => $sys['no_result_msg']));
}

/* TOTAL FAIL SAFE */
json_exit(array('success' => false, 'reply' => $default_noresult));
?>