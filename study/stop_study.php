<?php
include("../db/config.php");


if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$session = $conn->query("
    SELECT ss.session_id, ss.start_time
    FROM study_sessions ss
    JOIN subjects s ON ss.subject_id = s.subject_id
    WHERE s.student_id = $student_id
    AND ss.end_time IS NULL
    ORDER BY ss.session_id DESC
    LIMIT 1
");

/* no active session  */
if ($session->num_rows === 0) {
    header("Location: study.php");
    exit;
}

$row = $session->fetch_assoc();

$end_time = date("Y-m-d H:i:s");

$duration_seconds = max(
    1,
    strtotime($end_time) - strtotime($row['start_time'])
);


$duration_minutes = floor($duration_seconds / 60);


$conn->query("
    UPDATE study_sessions
    SET end_time = '$end_time',
        duration_minutes = $duration_minutes,
        duration_seconds = $duration_seconds
    WHERE session_id = {$row['session_id']}
");


header("Location: study.php");
exit;
