<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
include_once __DIR__ . "/../includes/db.php";
// ACTION: delete unanswered
if (isset($_GET['delete_unanswered'])) {
    $id = intval($_GET['delete_unanswered']);
    $mysqli->query("DELETE FROM unanswered WHERE id=$id LIMIT 1");
    header("Location: ../admin/unanswered.php");
    exit;
}
// ACTION: convert unanswered → response
if (isset($_GET['convert'])) {
    $id = intval($_GET['convert']);
   $res = $mysqli->query("SELECT query FROM unanswered WHERE id=$id LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        $query = $row['query'];

        // Create a new response
        $stmt = $mysqli->prepare("INSERT INTO responses(title,reply,created_at) VALUES(?, ?, NOW())");
        $title = "Auto response";
        $defaultReply = "This is an auto-generated reply. Please edit.";
        $stmt->bind_param("ss", $title, $defaultReply);
        $stmt->execute();
        $newId = $stmt->insert_id;

        // Add question variant
        $stmt2 = $mysqli->prepare("INSERT INTO questions(response_id,question) VALUES(?,?)");
        $stmt2->bind_param("is", $newId, $query);
        $stmt2->execute();

        // Remove unanswered
        $mysqli->query("DELETE FROM unanswered WHERE id=$id LIMIT 1");

        header("Location: ../admin/manage_response.php?id=" . $newId);
        exit;
    }
}
header("Location: ../admin/dashboard.php");
exit;
