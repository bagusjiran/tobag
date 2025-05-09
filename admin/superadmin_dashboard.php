<?php
session_start();
if (!isset($_SESSION["login"]) || $_SESSION["status"] !== "superuser") {
    header("Location: login.php");
    exit;
}
?>

<h1>Selamat datang, Superuser!</h1>
<a href="logout.php">Logout</a>
