<?php
include("../db/config.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
}

$subject_id = $_POST['subject_id'];
$start_time = date("Y-m-d H:i:s");

$conn->query("INSERT INTO study_sessions (subject_id, start_time)
              VALUES ($subject_id, '$start_time')");

header("Location: study.php");
?>
