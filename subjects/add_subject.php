<?php
include("../db/config.php");
include("../includes/header.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$error = "";
$success = "";

if (isset($_POST['add_subject'])) {

    $subject_name = trim($_POST['subject_name']);

    if (!empty($subject_name)) {

        
        $check = $conn->prepare("
            SELECT subject_id 
            FROM subjects 
            WHERE student_id = ? 
            AND LOWER(subject_name) = LOWER(?)
        ");
        $check->bind_param("is", $student_id, $subject_name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "⚠️ Subject already exists.";
        } else {

            
            $insert = $conn->prepare("
                INSERT INTO subjects (student_id, subject_name)
                VALUES (?, ?)
            ");
            $insert->bind_param("is", $student_id, $subject_name);

            if ($insert->execute()) {
                header("Location: add_subject.php?success=1");
                exit;
            } else {
                $error = "❌ Failed to add subject.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Subject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5" style="max-width: 500px;">

    <h3 class="mb-4 text-center">➕ Add Subject</h3>

    
    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ <strong>Subject added successfully!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    
    <?php if (!empty($error)) { ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <form method="POST" class="card shadow p-4">
        <div class="mb-3">
            <label>Subject Name</label>
            <input type="text" name="subject_name" class="form-control" required>
        </div>

        <button name="add_subject" class="btn btn-success w-100">
            Add Subject
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="list_subjects.php">View Subjects</a> |
        <a href="../dashboard.php">Back to Dashboard</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
