<?php
include 'koneksi.php';
$id = $_GET['id'];

$hapus = mysqli_query($koneksi, "DELETE FROM tb_produk WHERE id_produk  ='$id'");

if ($hapus) {
    echo "<script>alert('Data Berhasil Dihapus')</script>";
    header("refresh:0; kategori.php");
} else {
    echo "<script>alert('Data Gagal Dihapus')</script>";
    header("refresh:0; kategori.php");
}
?>