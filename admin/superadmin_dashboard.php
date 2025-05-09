<?php
session_start();
if (!isset($_SESSION["login"]) || $_SESSION["status"] !== "superuser") {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Superuser</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-lg border-0">
                <div class="card-body text-center">
                    <h2 class="card-title mb-3 text-primary">Selamat Datang, Superuser!</h2>
                    <p class="card-text">Anda memiliki akses penuh sebagai Superuser.</p>

                    <div class="d-grid gap-2 col-8 mx-auto mt-4">
                        <a href="index.php" class="btn btn-success">Masuk ke Dashboard Admin</a>
                        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
