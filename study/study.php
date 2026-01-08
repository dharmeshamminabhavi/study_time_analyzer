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


$subjects = $conn->query("
    SELECT subject_id, subject_name
    FROM subjects
    WHERE student_id = $student_id
");


$running_session = $conn->query("
    SELECT ss.start_time, s.subject_name
    FROM study_sessions ss
    JOIN subjects s ON ss.subject_id = s.subject_id
    WHERE s.student_id = $student_id
    AND ss.end_time IS NULL
    ORDER BY ss.session_id DESC
    LIMIT 1
")->fetch_assoc();


$session_count = $conn->query("
    SELECT COUNT(*) AS total
    FROM study_sessions ss
    JOIN subjects s ON ss.subject_id = s.subject_id
    WHERE s.student_id = $student_id
    AND DATE(ss.start_time) = '$today'
")->fetch_assoc()['total'];


$total_seconds_today = $conn->query("
    SELECT IFNULL(SUM(duration_seconds),0) AS total
    FROM study_sessions ss
    JOIN subjects s ON ss.subject_id = s.subject_id
    WHERE s.student_id = $student_id
    AND DATE(ss.start_time) = '$today'
    AND ss.end_time IS NOT NULL
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Study Session</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .btn-lg {
            border-radius: 12px;
            font-weight: 600;
        }
        .timer-box h1 {
            font-size: 3rem;
        }
    </style>
</head>

<body class="bg-light">

<div class="container d-flex justify-content-center">
    <div class="col-lg-5 col-md-7 col-sm-12 mt-5">
    <div class="card shadow-lg p-4">

    <h2 class="text-center mb-2">⏱ Study Session</h2>

    <?php if ($running_session) { ?>
        <p class="text-center text-muted">
            Studying: <b><?php echo $running_session['subject_name']; ?></b>
        </p>
    <?php } ?>

    
    <div class="row text-center mt-4 timer-box">
        <div class="col">
            <h1 id="hours">0</h1>
            <p>Hours</p>
        </div>
        <div class="col">
            <h1 id="minutes">0</h1>
            <p>Minutes</p>
        </div>
        <div class="col">
            <h1 id="seconds">0</h1>
            <p>Seconds</p>
        </div>
    </div>

    
    <div class="text-center mt-3">
        <p>📘 Sessions Today: <b><?php echo $session_count; ?></b></p>
        <p>
            ⏳ Total Study Time Today:
            <b><span id="totalToday"><?php echo formatDuration($total_seconds_today); ?></span></b>
        </p>
    </div>

    <hr>

    
    <?php if (!$running_session) { ?>
    <form action="start_study.php" method="POST" class="mt-4">
        <label class="form-label fw-semibold">Select Subject</label>

        <select name="subject_id" class="form-select mb-3" required>
            <?php while ($row = $subjects->fetch_assoc()) { ?>
                <option value="<?php echo $row['subject_id']; ?>">
                    <?php echo $row['subject_name']; ?>
                </option>
            <?php } ?>
        </select>

        <button class="btn btn-success btn-lg w-100">
            <i class="bi bi-play-fill"></i> Start Study Session
        </button>
    </form>
    <?php } ?>

    
    <div class="row mt-4 g-3">
        <div class="col-md-6">
            <button class="btn btn-warning btn-lg w-100" id="pauseBtn">
                <i class="bi bi-pause-fill"></i> Pause
            </button>
        </div>
        <div class="col-md-6">
            <button class="btn btn-primary btn-lg w-100" id="resumeBtn">
                <i class="bi bi-play-fill"></i> Resume
            </button>
        </div>
    </div>

    
    <form action="stop_study.php" method="POST" class="mt-4">
        <button class="btn btn-danger btn-lg w-100">
            <i class="bi bi-stop-fill"></i> Stop Session
        </button>
    </form>

    <a href="../dashboard.php" class="btn btn-outline-dark w-100 mt-4">
        ⬅ Back to Dashboard
    </a>

</div>


<script>
let paused = false;
let pauseStart = null;
let pausedMs = 0;

let startTimestamp = <?php
    echo $running_session
        ? strtotime($running_session['start_time']) * 1000
        : 'null';
?>;

let baseSecondsToday = <?php echo $total_seconds_today; ?>;

function formatTime(sec) {
    let h = Math.floor(sec / 3600);
    let m = Math.floor((sec % 3600) / 60);
    let s = sec % 60;
    return String(h).padStart(2,'0') + ":" +
           String(m).padStart(2,'0') + ":" +
           String(s).padStart(2,'0');
}

function startTimer() {
    if (!startTimestamp) return;

    setInterval(() => {
        if (paused) return;

        const now = Date.now();
        let diff = Math.floor((now - startTimestamp - pausedMs) / 1000);

        document.getElementById("hours").innerText = Math.floor(diff / 3600);
        document.getElementById("minutes").innerText = Math.floor((diff % 3600) / 60);
        document.getElementById("seconds").innerText = diff % 60;

        document.getElementById("totalToday").innerText =
            formatTime(baseSecondsToday + diff);
    }, 1000);
}


document.getElementById("pauseBtn").onclick = () => {
    if (!paused) {
        paused = true;
        pauseStart = Date.now();
    }
};


document.getElementById("resumeBtn").onclick = () => {
    if (paused) {
        paused = false;
        pausedMs += Date.now() - pauseStart;
    }
};

startTimer();
</script>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

