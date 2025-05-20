<?php
session_start();
include "./admin/koneksi.php";

// cek apakah sudah login
if (!isset($_SESSION["login"])) {
  header("location: login.php");
  exit;
}

// cek apakah status tersedia dan pastikan user adalah admin
if (!isset($_SESSION["status"]) || $_SESSION["status"] !== "admin" && $_SESSION["status"] !== "superuser") {
  echo "<script>
    alert('Akses ditolak! Halaman ini hanya untuk Admin.';
    windows.location.href='login.php';
    </script>";
    exit;
   
}
?>