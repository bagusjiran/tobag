<?php
session_start();
include 'admin/koneksi.php';

$response = ['success' => false];

if (isset($_POST['id_pesanan']) && isset($_POST['qty'])) {
    $id_pesanan = $_POST['id_pesanan'];
    $qty = (int)$_POST['qty'];
    $id_user = $_SESSION['id_user'];

    // Update quantity in the database
    $query_update = mysqli_query($koneksi, "UPDATE tb_pesanan SET qty = '$qty' WHERE id_pesanan = '$id_pesanan' AND id_user = '$id_user'");

    if ($query_update) {
        // Recalculate totals
        $query_pesanan = mysqli_query($koneksi, "
            SELECT p.*, pr.harga 
            FROM tb_pesanan p
            JOIN tb_produk pr ON p.id_produk = pr.id_produk
            WHERE p.id_user = '$id_user'
        ");

        $subtotal = 0;
        while ($row = mysqli_fetch_assoc($query_pesanan)) {
            $subtotal += $row['qty'] * $row['harga'];
        }

        // Calculate discount
        $diskon = 0;
        if ($subtotal > 3000000) {
            $diskon = 0.07 * $subtotal;
        } elseif ($subtotal > 1500000) {
            $diskon = 0.05 * $subtotal;
        }
        $total_bayar = $subtotal - $diskon;

        $response = [
            'success' => true,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total_bayar' => $total_bayar
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>