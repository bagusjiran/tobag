<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

include 'admin/koneksi.php';

// Get data from POST
$id_produk = isset($_POST['id_produk']) ? $_POST['id_produk'] : '';
$jumlah    = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;
$harga     = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;
$redirect  = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'belanja.php';

// Use id_user from session, not POST
$id_user = $_SESSION['id_user'];

// Validate input
if (empty($id_produk) || $jumlah <= 0 || $harga <= 0) {
    echo "<script>alert('Data tidak valid.'); window.location.href='$redirect';</script>";
    exit;
}

// Check stock availability (but don't reduce stock yet)
$cek = mysqli_query($koneksi, "SELECT stok FROM tb_produk WHERE id_produk = '$id_produk'");
$data = mysqli_fetch_assoc($cek);

if ($data && $data['stok'] >= $jumlah) {
    $total = $jumlah * $harga;

    // Generate new id_pesanan
    $getLast = mysqli_query($koneksi, "SELECT id_pesanan FROM tb_pesanan ORDER BY id_pesanan DESC LIMIT 1");
    $lastData = mysqli_fetch_assoc($getLast);

    if ($lastData) {
        $lastId = (int) substr($lastData['id_pesanan'], 1); // Extract number after 'M'
        $newId = $lastId + 1;
    } else {
        $newId = 1; // If no previous data, start with 1
    }

    $id_pesanan = 'M' . str_pad($newId, 3, '0', STR_PAD_LEFT); // Format as M001, M002, etc.

    // Insert into tb_pesanan without reducing stock
    $insert = mysqli_query($koneksi, "INSERT INTO tb_pesanan (id_pesanan, id_produk, qty, total, id_user) VALUES (
        '$id_pesanan', '$id_produk', $jumlah, $total, '$id_user')");

    if ($insert) {
        echo "<script>alert('Produk berhasil ditambahkan ke pesanan.'); window.location.href='cart.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan pesanan: " . mysqli_error($koneksi) . "'); window.location.href='$redirect';</script>";
    }
} else {
    echo "<script>alert('Stok tidak mencukupi.'); window.location.href='$redirect';</script>";
}
?>