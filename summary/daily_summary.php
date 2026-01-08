<?php
include("../db/config.php");
include("../includes/header.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$today = date("Y-m-d");


function formatDuration($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $h, $m, $s);
}

$query = "
SELECT s.subject_name,
       COUNT(ss.session_id) AS session_count,
       SUM(ss.duration_seconds) AS total_seconds
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND DATE(ss.start_time) = '$today'
GROUP BY s.subject_name
";

$result = $conn->query($query);


$labels = [];
$seconds_data = [];
$grand_total_seconds = 0;


$query_not_studied = "
SELECT subject_name
FROM subjects
WHERE student_id = $student_id
AND subject_id NOT IN (
    SELECT DISTINCT subject_id
    FROM study_sessions
    WHERE DATE(start_time) = '$today'
)
";

$not_studied = $conn->query($query_not_studied);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daily Summary</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light ">

<div class="container py-5">

<h2>📅 Today's Study Summary</h2>

<
<table class="table table-bordered mt-4">
    <tr>
        <th>Subject</th>
        <th>Sessions</th>
        <th>Time Studied (HH:MM:SS)</th>
    </tr>

<?php if ($result->num_rows > 0) { ?>
<?php while ($row = $result->fetch_assoc()) {

    $labels[] = $row['subject_name'];
    $seconds_data[] = (int)$row['total_seconds'];
    $grand_total_seconds += $row['total_seconds'];
?>
<tr>
    <td><?php echo $row['subject_name']; ?></td>
    <td><?php echo $row['session_count']; ?></td>
    <td><?php echo formatDuration($row['total_seconds']); ?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr>
    <td colspan="3" class="text-center">No study sessions recorded today</td>
</tr>
<?php } ?>
</table>

<h4 class="mt-3">
    Total Study Time Today:
    <b><?php echo formatDuration($grand_total_seconds); ?></b>
</h4>

<hr>

<h5 class="mt-4">Subjects Yet to Be Studied Today</h5>

<?php if ($not_studied->num_rows > 0) { ?>
<ul class="list-group mt-3">
<?php while ($row = $not_studied->fetch_assoc()) { ?>
    <li class="list-group-item">📘 <?php echo $row['subject_name']; ?></li>
<?php } ?>
</ul>
<?php } else { ?>
<div class="alert alert-success mt-3">
    🎉 Great job! You studied all subjects today.
</div>
<?php } ?>


<?php if (!empty($labels)) { ?>
<div class="row mt-5">

    <div class="col-md-6">
        <h5>📊 Subject-wise Study Time</h5>
        <canvas id="barChart"></canvas>
    </div>

    <div class="col-md-6">
        <h5>🍩 Study Time Distribution</h5>
        <div class="d-flex justify-content-center">
            <div style="width:300px;height:300px;">
                <canvas id="donutChart"></canvas>
            </div>
        </div>
    </div>

</div>
<?php } ?>

<a href="../dashboard.php" class="btn btn-dark mt-4">Back to Dashboard</a>


<script>
<?php if (!empty($labels)) { ?>

const formatTime = (sec) => {
    let h = Math.floor(sec / 3600);
    let m = Math.floor((sec % 3600) / 60);
    let s = sec % 60;
    return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
};


new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Time Studied',
            data: <?php echo json_encode($seconds_data); ?>
        }]
    },
    options: {
        plugins: {
            tooltip: {
                callbacks: {
                    label: ctx => formatTime(ctx.raw)
                }
            }
        }
    }
});


new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            data: <?php echo json_encode($seconds_data); ?>
        }]
    },
    options: {
        plugins: {
            tooltip: {
                callbacks: {
                    label: ctx => formatTime(ctx.raw)
                }
            },
            legend: { position: 'bottom' }
        }
    }
});

<?php } ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
