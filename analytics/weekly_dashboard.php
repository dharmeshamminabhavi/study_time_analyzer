<?php 
include("../db/config.php");
include("../includes/header.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];


function formatDuration($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $h, $m, $s);
}


$subject_labels = [];
$subject_seconds = [];

$q1 = "
SELECT s.subject_name,
       SUM(ss.duration_seconds) AS total_seconds
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND ss.start_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY s.subject_name
";

$res1 = $conn->query($q1);
while ($row = $res1->fetch_assoc()) {
    $subject_labels[]  = $row['subject_name'];
    $subject_seconds[] = (int)$row['total_seconds'];
}


$day_labels = [];
$day_seconds = [];

$q2 = "
SELECT DAYNAME(ss.start_time) AS day,
       SUM(ss.duration_seconds) AS total_seconds
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND ss.start_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DAYNAME(ss.start_time)
ORDER BY FIELD(
    DAYNAME(ss.start_time),
    'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'
)
";

$res2 = $conn->query($q2);
while ($row = $res2->fetch_assoc()) {
    $day_labels[]  = $row['day'];
    $day_seconds[] = (int)$row['total_seconds'];
}


$weekly_total_seconds = array_sum($subject_seconds);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Weekly Analytics</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light ">
<div class="container py-5">

<h2>📊 Weekly Study Analytics (Last 7 Days)</h2>


<table class="table table-bordered mt-4">
    <tr>
        <th>Subject</th>
        <th>Total Time (HH:MM:SS)</th>
    </tr>

    <?php for ($i = 0; $i < count($subject_labels); $i++) { ?>
        <tr>
            <td><?php echo $subject_labels[$i]; ?></td>
            <td><?php echo formatDuration($subject_seconds[$i]); ?></td>
        </tr>
    <?php } ?>
</table>

<h5 class="mt-3">
    Total Study Time This Week:
    <b><?php echo formatDuration($weekly_total_seconds); ?></b>
</h5>


<div class="row mt-5">

   
    <div class="col-md-6 text-center">
        <h5>🍩 Subject-wise Study Time</h5>
        <div style="width:260px; height:260px; margin:auto;">
            <canvas id="subjectChart"></canvas>
        </div>
    </div>

    
    <div class="col-md-6">
        <h5>📊 Day-wise Study Time</h5>
        <canvas id="barChart"></canvas>
    </div>

    
    <div class="col-md-12 mt-5">
        <h5>📈 Study Trend Over Last 7 Days</h5>
        <canvas id="lineChart" height="70"></canvas>
    </div>

</div>

<a href="../dashboard.php" class="btn btn-dark mt-4">Back to Dashboard</a>


<script>
function formatTime(sec) {
    let h = Math.floor(sec / 3600);
    let m = Math.floor((sec % 3600) / 60);
    let s = sec % 60;
    return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}


new Chart(document.getElementById('subjectChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($subject_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($subject_seconds); ?>
        }]
    },
    options: {
        plugins: {
            tooltip: {
                callbacks: {
                    label: (ctx) => formatTime(ctx.raw)
                }
            },
            legend: { position: 'bottom' }
        }
    }
});


new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($day_labels); ?>,
        datasets: [{
            label: 'Study Time',
            data: <?php echo json_encode($day_seconds); ?>
        }]
    },
    options: {
        plugins: {
            tooltip: {
                callbacks: {
                    label: (ctx) => formatTime(ctx.raw)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (v) => formatTime(v)
                }
            }
        }
    }
});


new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($day_labels); ?>,
        datasets: [{
            label: 'Study Time',
            data: <?php echo json_encode($day_seconds); ?>,
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        plugins: {
            tooltip: {
                callbacks: {
                    label: (ctx) => formatTime(ctx.raw)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (v) => formatTime(v)
                }
            }
        }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
