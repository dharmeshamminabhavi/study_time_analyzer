<?php
include("../db/config.php");
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logged Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow p-4 text-center" style="width: 350px;">
        <h4 class="mb-3">👋 You are logged out</h4>
        <p class="text-muted">See you again!</p>

        <a href="login.php" class="btn btn-primary w-100">Login Again</a>
    </div>
</div>

</body>
</html>
