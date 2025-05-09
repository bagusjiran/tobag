<?php
session_start();
include "koneksi.php";

// cek apakah sudah login
if (!isset($_SESSION)) {
  header ("location: login.php");
  exit;
}

// cek apakah status tersedia dan pastikan user adalah admin
if (!isset($_SESSION["status"]) || $_SESSION["status"] !== "admin") {
  echo "<script>
    alert('Akses ditolak! Halaman ini hanya untuk Admin.';
    windows.location.href='login.php';
    </script>";
    exit;
   
}

password_hash('admin#123', PASSWORD_DEFAULT)
?>

<?php password_hash('admin#123', PASSWORD_DEFAULT) ?>


<h6><?php  echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest '; ?> </h6>