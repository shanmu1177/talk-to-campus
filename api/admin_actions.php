<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin/login.php");
    exit;
}

include_once __DIR__ . "/../includes/db.php";


/* DELETE UNANSWERED */
if (isset($_GET['delete_unanswered'])) {

    $id = intval($_GET['delete_unanswered']);

    $stmt = mysqli_prepare($mysqli, 
        "DELETE FROM unanswered WHERE id = ? LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: ../admin/unanswered.php");
    exit;
}


/* CONVERT UNANSWERED → RESPONSE */
if (isset($_GET['convert'])) {

    $id = intval($_GET['convert']);

    $stmt = mysqli_prepare($mysqli, 
        "SELECT query FROM unanswered WHERE id = ? LIMIT 1"
    );

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $query);

        if (mysqli_stmt_fetch($stmt)) {

            mysqli_stmt_close($stmt);

            /* Create new response */
            $title = "Auto response";
            $defaultReply = "This is an auto-generated reply. Please edit.";

            $stmt2 = mysqli_prepare($mysqli,
                "INSERT INTO responses (title, reply, created_at) VALUES (?, ?, NOW())"
            );

            if ($stmt2) {
                mysqli_stmt_bind_param($stmt2, "ss", $title, $defaultReply);
                mysqli_stmt_execute($stmt2);

                $newId = mysqli_insert_id($mysqli);

                mysqli_stmt_close($stmt2);

                /* Insert into questions */
                $stmt3 = mysqli_prepare($mysqli,
                    "INSERT INTO questions (response_id, question) VALUES (?, ?)"
                );

                if ($stmt3) {
                    mysqli_stmt_bind_param($stmt3, "is", $newId, $query);
                    mysqli_stmt_execute($stmt3);
                    mysqli_stmt_close($stmt3);
                }

                /* Delete from unanswered */
                $stmt4 = mysqli_prepare($mysqli,
                    "DELETE FROM unanswered WHERE id = ? LIMIT 1"
                );

                if ($stmt4) {
                    mysqli_stmt_bind_param($stmt4, "i", $id);
                    mysqli_stmt_execute($stmt4);
                    mysqli_stmt_close($stmt4);
                }

                header("Location: ../admin/manage_response.php?id=" . $newId);
                exit;
            }

        } else {
            mysqli_stmt_close($stmt);
        }
    }
}

header("Location: ../admin/dashboard.php");
exit;
?>