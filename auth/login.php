<?php
include("../db/config.php");

if (isset($_SESSION['student_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM students WHERE email='$email'");
    $user = $res->fetch_assoc();//convert to array

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['name'] = $user['name'];
        header("Location: ../dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center mb-3">🔐 Login</h3>

        <?php if (isset($error)) { ?> 
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button name="login" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3">
            New user?
            <a href="register.php">Create account</a>
        </p>
    </div>
</div>

</body>
</html>
