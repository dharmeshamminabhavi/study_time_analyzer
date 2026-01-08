<?php
include("../db/config.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$session_id = $_POST['session_id'];//study session
$notes = $conn->real_escape_string($_POST['notes']);//clean

$conn->query("
    UPDATE study_sessions
    SET notes = '$notes'
    WHERE session_id = $session_id
");

header("Location: history.php");
exit;
