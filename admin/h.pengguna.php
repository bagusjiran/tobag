<?php
include 'koneksi.php';
if (isset($_GET['id'])) {
    $id_user = $_GET['id'];

    $hapus = mysqli_query($koneksi, "DELETE FROM tb_user WHERE id_user = '$id_user'");

    if ($hapus) {
        echo "<script>alert('Data Berhasil Dihapus')</script>";
        header("refresh:0; pengguna.php");
    } else {
        echo "<script>alert('Data Gagal Dihapus')</script>";
        header("refresh:0; pengguna.php");
    }
} else {
    echo "<script>alert('ID tidak ditemukan')</script>";
    header("refresh:0; pengguna.php");
}
?>