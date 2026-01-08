<?php
include("../db/config.php");
include("../includes/header.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$result = $conn->query("
    SELECT subject_name
    FROM subjects
    WHERE student_id = $student_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5">

<h2>📘 My Subjects</h2>

<table class="table table-bordered mt-4">
    <tr>
        <th>Subject Name</th>
    </tr>

    <?php if ($result->num_rows > 0) { ?>
        <?php while ($row = $result->fetch_assoc()) { ?> 
            <tr>
                <td><?php echo $row['subject_name']; ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td class="text-center">No subjects added yet</td>
        </tr>
    <?php } ?>
</table>

<a href="add_subject.php" class="btn btn-success">Add Subject</a>
<a href="../dashboard.php" class="btn btn-dark">Back to Dashboard</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
