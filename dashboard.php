<?php
include("db/config.php");
include("includes/header.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: auth/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$today = date("Y-m-d");

//study date//
$result = $conn->query("
SELECT DISTINCT DATE(ss.start_time) AS study_date
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
ORDER BY study_date ASC
");

$study_dates = [];
while ($row = $result->fetch_assoc()) {
    $study_dates[] = $row['study_date'];
}


$current_streak = 0;


if (in_array($today, $study_dates)) {
    $check_date = $today;

    while (in_array($check_date, $study_dates)) {
        $current_streak++;
        $check_date = date("Y-m-d", strtotime("-1 day", strtotime($check_date)));
    }
} else {
    $current_streak = 0;
}


$max_streak = 0;
$temp_streak = 0;
$prev_date = null;

foreach ($study_dates as $date) {
    if (
        $prev_date === null ||
        strtotime($date) === strtotime("+1 day", strtotime($prev_date))
    ) {
        $temp_streak++;
    } else {
        $temp_streak = 1;
    }

    $max_streak = max($max_streak, $temp_streak);
    $prev_date = $date;
}



$today = date("Y-m-d");

$result_subjects_today = $conn->query("
SELECT COUNT(DISTINCT ss.subject_id) AS total
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND DATE(ss.start_time) = '$today'
");

$subjects_studied_today = $result_subjects_today->fetch_assoc()['total'];




$today = date("Y-m-d");

$result_sessions_today = $conn->query("
SELECT COUNT(*) AS total
FROM study_sessions ss
JOIN subjects s ON ss.subject_id = s.subject_id
WHERE s.student_id = $student_id
AND DATE(ss.start_time) = '$today'
");

$sessions_today = $result_sessions_today->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            👋 Welcome <span class="text-primary"><?php echo $_SESSION['name']; ?></span>
        </h3>
        
    </div>

    <div class="row mt-4 mb-4">

    
    <div class="col-md-3">
        <div class="card shadow text-center p-4 h-100">
            <h5>🔥 Current Streak</h5>
            <h2 class="text-success">
                <?php echo $current_streak; ?>
            </h2>
            <small class="text-muted">days</small>
        </div>
    </div>

    
    <div class="col-md-3">
        <div class="card shadow text-center p-4 h-100">
            <h5>🏆 Max Streak</h5>
            <h2 class="text-primary">
                <?php echo $max_streak; ?>
            </h2>
            <small class="text-muted">days</small>
        </div>
    </div>

   
    <div class="col-md-3">
        <div class="card shadow text-center p-4 h-100">
            <h5>📚 Subjects Studied Today</h5>
            <h2 class="text-warning">
                <?php echo $subjects_studied_today; ?>
            </h2>
            <small class="text-muted">unique</small>
        </div>
    </div>

    
    <div class="col-md-3">
        <div class="card shadow text-center p-4 h-100">
            <h5>⏱ Sessions Today</h5>
            <h2 class="text-danger">
                <?php echo $sessions_today; ?>
            </h2>
            <small class="text-muted">sessions</small>
        </div>
    </div>

</div>




   
    <div class="row g-4">

        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-book fs-1 text-success"></i>
                    <h5 class="mt-3">Manage Subjects</h5>
                    <p class="text-muted">Add or view your study subjects</p>
                    <a href="subjects/add_subject.php" class="btn btn-success w-100">Open</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-clock-history fs-1 text-primary"></i>
                    <h5 class="mt-3">Start Study Session</h5>
                    <p class="text-muted">Track your study time live</p>
                    <a href="study/study.php" class="btn btn-primary w-100">Start</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-calendar-check fs-1 text-warning"></i>
                    <h5 class="mt-3">Daily Summary</h5>
                    <p class="text-muted">View today’s study report</p>
                    <a href="summary/daily_summary.php" class="btn btn-warning w-100">View</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-bar-chart-line fs-1 text-info"></i>
                    <h5 class="mt-3">Weekly Analytics</h5>
                    <p class="text-muted">Analyze weekly study trends</p>
                    <a href="analytics/weekly_dashboard.php" class="btn btn-info w-100">Analyze</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-lightbulb fs-1 text-danger"></i>
                    <h5 class="mt-3">Insights & Alerts</h5>
                    <p class="text-muted">Understand your study habits</p>
                    <a href="insights/insights.php" class="btn btn-danger w-100">View</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-clock fs-1 text-secondary"></i>
                    <h5 class="mt-3">Study History</h5>
                    <p class="text-muted">Review past study sessions</p>
                    <a href="history/history.php" class="btn btn-secondary w-100">Open</a>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
