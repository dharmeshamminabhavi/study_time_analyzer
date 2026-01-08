<?php
include("../db/config.php");
include("../includes/header.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];


function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}

/* Fetch all past sessions  */
$query = "
SELECT s.subject_name,
       ss.start_time,
       ss.end_time,
       ss.duration_seconds,
       ss.notes,
       ss.session_id
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
ORDER BY ss.start_time DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Study History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light ">
<div class="container py-5">

<h2>📚 Study History</h2>

<table class="table table-bordered table-striped mt-4">
    <tr>
        <th>Subject</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Duration (HH:MM:SS)</th>
        <th>Notes</th>
    </tr>

<?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $row['subject_name']; ?></td>
        <td><?php echo $row['start_time']; ?></td>
        <td><?php echo $row['end_time']; ?></td>
        <td><?php echo formatDuration($row['duration_seconds']); ?></td>

        <td>
            <form action="save_notes.php" method="POST">
                <input type="hidden" name="session_id"
                       value="<?php echo $row['session_id']; ?>">
                <textarea name="notes"
                          class="form-control"
                          rows="2"
                          placeholder="write notes"><?php echo $row['notes']; ?></textarea>
                <button class="btn btn-sm btn-primary mt-1">Save</button>
            </form>
        </td>
    </tr>
<?php } ?>

</table>

<a href="../dashboard.php" class="btn btn-dark mt-3">Back to Dashboard</a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
