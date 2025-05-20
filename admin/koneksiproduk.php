<?php
session_start();
include "koneksi.php";

// cek apakah sudah login
if (!isset($_SESSION)) {
  header ("location: login.php");
  exit;
}

// cek apakah status tersedia dan pastikan user adalah admin
if (!isset($_SESSION["status"]) || $_SESSION["status"] !== "admin" && $_SESSION["status"] !== "superuser") {
    // Jika bukan admin, tampilkan pesan akses ditolak
    // dan redirect ke halaman login
  echo "<script>
    alert('Akses ditolak! Halaman ini hanya untuk Admin.';
    windows.location.href='login.php';
    </script>";
    exit;
}

if (isset($_POST['simpan'])) {
    $auto = mysqli_query($koneksi, "SELECT max(id_produk) as max_code FROM tb_produk");
    $hasil = mysqli_fetch_array($auto);
    $code = $hasil['max_code'];

    if ($code) {
        $urutan = (int) substr($code, 1, 3);
    } else {
        $urutan = 0;
    }
    $urutan++;
    $huruf = "P";
    $id_produk = $huruf . sprintf("%03s", $urutan);

    $nm_produk = $_POST['nm_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $desk = $_POST['desk'];
    $id_kategori = $_POST['id_kategori'];

    $imgfile = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $extension = strtolower(pathinfo($imgfile, PATHINFO_EXTENSION));

    $dir = "produk_img/";
    $allowed_extension = array('jpg', 'jpeg', 'png', 'gif', 'webp');

    if (!in_array($extension, $allowed_extension)) {
        echo "<script>alert('Format Gambar Tidak valid. Hanya jpg, jpeg, png, gif, dan webp yang diperbolehkan.')</script>";
    } else {
        $newfilename = $id_produk . '.' . $extension;
        move_uploaded_file($tmp, $dir . $newfilename);

        $query = mysqli_query($koneksi, "INSERT INTO tb_produk(id_produk, nm_produk, harga, stok, desk, id_kategori, gambar) 
        VALUES ('$id_produk', '$nm_produk', '$harga', '$stok', '$desk', '$id_kategori', '$newfilename')");

        if ($query) {
            echo "<script>alert('Data Berhasil Disimpan')</script>";
            header("refresh:0; url=produk.php");
        } else {
            echo "<script>alert('Data Gagal Disimpan')</script>";
            header("refresh:0; url=produk.php");
        }
    }
}
?>