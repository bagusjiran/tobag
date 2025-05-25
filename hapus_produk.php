<?php
session_start();
include 'admin/koneksi.php';

$response = ['success' => false, 'message' => 'Terjadi kesalahan'];

if (!isset($_SESSION['id_user'])) {
    $response['message'] = 'Anda harus login untuk menghapus produk dari keranjang';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_POST['id'])) {
    $id_pesanan = mysqli_real_escape_string($koneksi, $_POST['id']);
    $id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']);

    // Delete the item from the cart
    $query = mysqli_query($koneksi, "DELETE FROM tb_pesanan WHERE id_pesanan = '$id_pesanan' AND id_user = '$id_user'");

    if ($query && mysqli_affected_rows($koneksi) > 0) {
        // Recalculate totals
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
            'message' => 'Produk berhasil dihapus dari keranjang',
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total_bayar' => $total_bayar,
            'item_count' => $item_count
        ];
    } else {
        $response['message'] = 'Produk tidak ditemukan atau sudah dihapus';
    }
} else {
    $response['message'] = 'ID pesanan tidak diberikan';
}

header('Content-Type: application/json');
echo json_encode($response);
?>