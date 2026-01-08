<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4">

        
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/study_analyzer/dashboard.php">
            ⏱ <span>Study Time Analyzer</span>
        </a>

        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto gap-3">

                <li class="nav-item">
                    <a class="nav-link" href="/study_analyzer/dashboard.php">
                        🏠 Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/study_analyzer/subjects/add_subject.php">
                        📘 Subjects
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/study_analyzer/study/study.php">
                        ⏱ Study
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/study_analyzer/summary/daily_summary.php">
                        📅 Daily
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link "
                       
href="/study_analyzer/analytics/weekly_dashboard.php">
                        📊 Weekly
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/study_analyzer/insights/insights.php">
                        📈 Insights
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/study_analyzer/history/history.php">
                        🕒 History
                    </a>
                </li>
            </ul>

            
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted fw-semibold">
                    <?php echo $_SESSION['name'] ?? 'User'; ?>
                </span>

                <a href="/study_analyzer/auth/logout.php"
                   class="btn btn-outline-danger btn-sm">
                    🚪 Logout
                </a>
            </div>
        </div>
    </div>
</nav>


