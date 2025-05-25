<?php
include 'admin/koneksi.php';
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $query = mysqli_query($koneksi, "SELECT p.*, k.nm_kategori FROM tb_produk p LEFT JOIN tb_kategori k ON p.id_kategori = k.id_kategori WHERE p.id_produk='$id'");
    $data = mysqli_fetch_assoc($query);
    echo json_encode($data);
}
?>