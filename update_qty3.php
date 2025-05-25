<?php
session_start();
include 'admin/koneksi.php';

$response = ['success' => false, 'message' => 'Terjadi kesalahan'];

if (!isset($_SESSION['id_user'])) {
    $response['message'] = 'Anda harus login untuk memperbarui pesanan';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_POST['id_pesanan']) && isset($_POST['qty'])) {
    $id_pesanan = mysqli_real_escape_string($koneksi, $_POST['id_pesanan']);
    $qty = (int)$_POST['qty'];
    $id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']);

    if ($qty < 1) {
        $qty = 1;
    }

    $query_update = mysqli_query($koneksi, "UPDATE tb_pesanan SET qty = '$qty' WHERE id_pesanan = '$id_pesanan' AND id_user = '$id_user'");

    if ($query_update && mysqli_affected_rows($koneksi) > 0) {
        $query_pesanan = mysqli_query($koneksi, "
            SELECT p.*, pr.harga 
            FROM tb_pesanan p
            JOIN tb_produk pr ON p.id_produk = pr.id_produk
            WHERE p.id_user = '$id_user'
        ");

        $subtotal = 0;
        $item_count = 0;
        while ($row = mysqli_fetch_assoc($query_pesanan)) {
            $subtotal += $row['qty'] * $row['harga'];
            $item_count++;
        }

        $diskon = 0;
        if ($subtotal > 3000000) {
            $diskon = 0.07 * $subtotal;
        } elseif ($subtotal > 1500000) {
            $diskon = 0.05 * $subtotal;
        }
        $total_bayar = $subtotal - $diskon;

        $response = [
            'success' => true,
            'message' => 'Jumlah pesanan berhasil diperbarui',
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total_bayar' => $total_bayar,
            'item_count' => $item_count
        ];
    } else {
        $response['message'] = 'Item tidak ditemukan atau gagal diperbarui';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>