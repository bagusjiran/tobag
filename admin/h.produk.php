<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id_produk = $_GET['id'];
    $query = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id_produk='$id_produk'");
    $data = mysqli_fetch_array($query);

    if ($data) {
        $gambar = $data['gambar'];
        if ($gambar && file_exists($dir . $gambar)) {
            unlink($dir . $gambar); // Hapus file gambar dari server
        }

        $hapus = mysqli_query($koneksi, "DELETE FROM tb_produk WHERE id_produk='$id_produk'");
        if ($hapus) {
            echo "<script>alert('Data Berhasil Dihapus')</script>";
            header("refresh:0; produk.php");
        } else {
            echo "<script>alert('Gagal menghapus produk')</script>";
            header("refresh:0; produk.php");
        }
    } else {
        echo "<script>alert('Produk tidak ditemukan')</script>";
        header("refresh:0; produk.php");
    }
} else {
    echo "<script>alert('ID produk tidak ditemukan')</script>";
    header("refresh:0; produk.php");
}
?>