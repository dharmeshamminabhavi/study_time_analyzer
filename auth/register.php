<?php
include("../db/config.php");

if (isset($_POST['register'])) { // button clicked
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->query("INSERT INTO students (name, email, password)
                  VALUES ('$name', '$email', '$password')");

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow p-4" style="width: 420px;">
        <h3 class="text-center mb-3">📝 Register</h3>

        <form method="POST">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button name="register" class="btn btn-success w-100">Create Account</button>
        </form>

        <p class="text-center mt-3">
            Already registered?
            <a href="login.php">Login</a>
        </p>
    </div>
</div>

</body>
</html>
