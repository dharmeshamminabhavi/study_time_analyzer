<?php
include("../db/config.php");
include("../includes/header.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$today = date("Y-m-d");

$insights = [];

function formatDuration($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $h, $m, $s);
}

//study days
$first_study_row = $conn->query("
    SELECT MIN(DATE(start_time)) AS first_day
    FROM study_sessions ss
    JOIN subjects s ON ss.subject_id = s.subject_id
    WHERE s.student_id = $student_id
")->fetch_assoc();

$first_study_date = $first_study_row['first_day'];

if (!$first_study_date) {

    $studied_days_week = 0;
    $possible_days_week = 0;
    $weekly_consistency_percent = 0;

    $insights[] = "ℹ️ Start your first study session to track weekly consistency.";
}
else {

    $seven_days_ago = date("Y-m-d", strtotime("-6 days"));

    
    $week_start_date = ($first_study_date > $seven_days_ago)
        ? $first_study_date
        : $seven_days_ago;
    $studied_days_week = $conn->query("
        SELECT COUNT(DISTINCT DATE(ss.start_time)) AS total
        FROM study_sessions ss
        JOIN subjects s ON ss.subject_id = s.subject_id
        WHERE s.student_id = $student_id
        AND DATE(ss.start_time) >= '$week_start_date'
    ")->fetch_assoc()['total'];
    $possible_days_week =
        floor((strtotime($today) - strtotime($week_start_date)) / 86400) + 1;
    $weekly_consistency_percent =
        round(($studied_days_week / $possible_days_week) * 100);
    if ($weekly_consistency_percent < 70) {
        $insights[] =
            "⚠️ Weekly consistency dropped: studied on <b>$studied_days_week</b> out of <b>$possible_days_week</b> days.";
    } else {
        $insights[] =
            "✅ Good weekly consistency: studied on <b>$studied_days_week</b> out of <b>$possible_days_week</b> days.";
    }
}


//not studied
$subjects = $conn->query("
SELECT subject_id, subject_name
FROM subjects
WHERE student_id = $student_id
");

while ($sub = $subjects->fetch_assoc()) {
    $sid = $sub['subject_id'];

    $last = $conn->query("
        SELECT MAX(start_time) AS last_study
        FROM study_sessions
        WHERE subject_id = $sid
    ")->fetch_assoc();

    if ($last['last_study']) {
        $days_gap = floor((strtotime($today) - strtotime($last['last_study'])) / 86400);
        if ($days_gap >= 5) {
            $insights[] = "📌 You haven’t studied <b>{$sub['subject_name']}</b> for <b>$days_gap days</b>.";
        }
    } else {
        $insights[] = "📌 You have never studied <b>{$sub['subject_name']}</b> yet.";
    }
}

//most studied

$most_subject = $conn->query("
SELECT s.subject_name,
       SUM(ss.duration_seconds) AS total_seconds
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND ss.start_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY s.subject_name
ORDER BY total_seconds DESC
LIMIT 1
");

if ($most_subject->num_rows > 0) {
    $row = $most_subject->fetch_assoc();

    if ($row['total_seconds'] > 0) {
        $hours = floor($row['total_seconds'] / 3600);
        $minutes = floor(($row['total_seconds'] % 3600) / 60);

        $hours   = floor($row['total_seconds'] / 3600);
$minutes = floor(($row['total_seconds'] % 3600) / 60);
$seconds = $row['total_seconds'] % 60;

$time_text = "{$hours}h {$minutes}m {$seconds}s";

        $insights[] = "⭐ Your most studied subject this month is <b>{$row['subject_name']}</b> ($time_text).";
    }
}

//least studied

$least_subject = $conn->query("
SELECT s.subject_name,
       SUM(ss.duration_seconds) AS total_seconds
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND ss.start_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY s.subject_id
HAVING total_seconds > 0
ORDER BY total_seconds ASC
LIMIT 1
");

if ($least_subject->num_rows > 0) {
    $row = $least_subject->fetch_assoc();

    $hours = floor($row['total_seconds'] / 3600);
    $minutes = floor(($row['total_seconds'] % 3600) / 60);

    $hours   = floor($row['total_seconds'] / 3600);
$minutes = floor(($row['total_seconds'] % 3600) / 60);
$seconds = $row['total_seconds'] % 60;

$time_text = "{$hours}h {$minutes}m {$seconds}s";

    $insights[] =
        "⚠️ Your least studied subject this month is <b>{$row['subject_name']}</b> ($time_text).";
} else {
    $insights[] =
        "ℹ️ Not enough study data to determine your least studied subject.";
}


// average

$avg_query = $conn->query("
SELECT 
    SUM(ss.duration_seconds) AS total_seconds,
    COUNT(DISTINCT DATE(ss.start_time)) AS studied_days
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND ss.start_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
");

$avg_row = $avg_query->fetch_assoc();

$avg_daily_seconds = 0;

if ($avg_row['studied_days'] > 0) {
    $avg_daily_seconds = floor($avg_row['total_seconds'] / $avg_row['studied_days']);
}


$avg_hours = floor($avg_daily_seconds / 3600);
$avg_minutes = floor(($avg_daily_seconds % 3600) / 60);
$avg_seconds = $avg_daily_seconds % 60;

$avg_time_text = sprintf("%02d:%02d:%02d", $avg_hours, $avg_minutes, $avg_seconds);


$query_trend = "
SELECT DATE(ss.start_time) AS day,
       SUM(ss.duration_seconds) AS total_seconds
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND ss.start_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(ss.start_time)
ORDER BY day
";

$result_trend = $conn->query($query_trend);

$trendMap = [];
while ($row = $result_trend->fetch_assoc()) {
    $trendMap[$row['day']] = round($row['total_seconds'] / 60);
}

$trend_labels = [];
$trend_values = [];

for ($i = 29; $i >= 0; $i--) {
    $date = date("Y-m-d", strtotime("-$i days"));
    $trend_labels[] = date("M j", strtotime($date));
    $trend_values[] = $trendMap[$date] ?? 0;
}

//consistency
$query_month = "
SELECT COUNT(DISTINCT DATE(ss.start_time)) AS studied_days
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND ss.start_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
";

$studied_days_30 = (int)$conn->query($query_month)->fetch_assoc()['studied_days'];
$total_days_30 = 30;
$skipped_days_30 = max(0, $total_days_30 - $studied_days_30);

$consistency_percent = round(($studied_days_30 / $total_days_30) * 100);

//first study date
$first_study_row = $conn->query("
    SELECT MIN(DATE(start_time)) AS first_day
    FROM study_sessions ss
    JOIN subjects s ON ss.subject_id = s.subject_id
    WHERE s.student_id = $student_id
")->fetch_assoc();

$first_study_date = $first_study_row['first_day'];

if (!$first_study_date) {
    // No study yet skip consistency graph
    $studied_days = 0;
    $skipped_days = 0;
    $consistency_percent = 0;
} else {
    // Total days 
$total_days = (strtotime($today) - strtotime($first_study_date)) / 86400 + 1;


$studied_days = $conn->query("
    SELECT COUNT(DISTINCT DATE(start_time)) AS total
    FROM study_sessions ss
    JOIN subjects s ON ss.subject_id = s.subject_id
    WHERE s.student_id = $student_id
    AND DATE(start_time) BETWEEN '$first_study_date' AND '$today'
")->fetch_assoc()['total'];

// Skipped days 
$skipped_days = max(0, $total_days - $studied_days);


$consistency_percent = round(($studied_days / $total_days) * 100);
}





$query_hourly = "
SELECT HOUR(ss.start_time) AS hour_slot,
       SUM(ss.duration_seconds) AS total_seconds
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND ss.start_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY HOUR(ss.start_time)
ORDER BY hour_slot
";

$result_hourly = $conn->query($query_hourly);


$hour_labels = [];
$hour_values = array_fill(0, 24, 0);

for ($h = 0; $h < 24; $h++) {
    $hour_labels[] = sprintf("%02d:00", $h);
}


while ($row = $result_hourly->fetch_assoc()) {
    $hour = (int)$row['hour_slot'];
    $hour_values[$hour] = round($row['total_seconds'] / 60);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Study Insights</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">🧠 Study Insights & Alerts</h2>

    
    <ul class="list-group mb-4">
        <?php foreach ($insights as $msg) { ?>
            <li class="list-group-item"><?php echo $msg; ?></li>
        <?php } ?>
    </ul>

    
    <div class="row mb-4">
        <div class="col-md-6">
    <div class="card shadow text-center p-4">
        <h5>⏱ Average Daily Study Time</h5>
        <h2 class="text-info">
            <?php echo $avg_time_text; ?>
        </h2>
        
    </div>
</div>

        <div class="col-md-6">
            <div class="card shadow text-center p-4">
                <h5>📊 Monthly Consistency</h5>
                <h2 class="text-primary"><?php echo $consistency_percent; ?>%</h2>
            </div>
        </div>
    </div>
    


    
    <div class="row mb-4">

        
        <div class="col-md-5">
            <div class="card shadow p-4 h-100">
                <h5 class="mb-3 text-center">🍩 Study Consistency (Last 30 Days)</h5>
                <div class="d-flex justify-content-center">
                    <canvas id="consistencyChart" width="220" height="220"></canvas>
                </div>
            </div>
        </div>

       
        <div class="col-md-7">
            <div class="card shadow p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">📈 30-Day Study Trend</h5>
                    <span class="badge bg-secondary">Last 30 days</span>
                </div>
                <canvas id="trendChart" height="140"></canvas>
            </div>
        </div>

    </div>



<div class="card shadow p-4 mb-4">
    <h5 class="mb-3">🕒 Hourly Productivity Heatmap (Last 30 Days)</h5>
    <div style="height:300px;">
        <canvas id="hourlyChart"></canvas>
    </div>
</div>






    <a href="../dashboard.php" class="btn btn-dark">Back to Dashboard</a>

</div>




<script>

new Chart(document.getElementById('consistencyChart'), {
    type: 'doughnut',
    data: {
        labels: ['Studied Days', 'Skipped Days'],
        datasets: [{
data: [<?php echo $studied_days; ?>, <?php echo $skipped_days; ?>]
        }]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});


new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($trend_labels); ?>,
        datasets: [{
            label: 'Minutes Studied',
            data: <?php echo json_encode($trend_values); ?>,
            tension: 0.4,
            fill: false,
            pointRadius: 3
        }]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false,
        scales: {
            y: {
                min: 0,
                max: 10,
                ticks: { stepSize: 0.5 }
            }
        }
    }
});

new Chart(document.getElementById('hourlyChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($hour_labels); ?>,
        datasets: [{
            label: 'Minutes Studied',
            data: <?php echo json_encode($hour_values); ?>,
            backgroundColor: <?php
                echo json_encode(array_map(function ($v) {
                    if ($v == 0) return '#e9ecef';     
                    if ($v < 20) return '#b6d4fe';    
                    if ($v < 40) return '#6ea8fe';    
                    return '#0d6efd';                 
                }, $hour_values));
            ?>
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Hour of Day'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Minutes Studied'
                }
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
