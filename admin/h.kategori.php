<?php
include "koneksi.php";

// Ambil ID dari parameter URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id !== null) {
    // Gunakan prepared statement untuk mencegah SQL injection
    $stmt = $koneksi->prepare("DELETE FROM tb_kategori WHERE id_kategori = ?");
    if ($stmt) {
        $stmt->bind_param("s", $id); // "s" karena id_kategori diasumsikan string
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Redirect ke halaman kategori dengan parameter untuk menampilkan pesan sukses
            header("Location: kategori.php?status=sukses");
            exit;
        } else {
            // Redirect ke halaman kategori dengan parameter untuk menampilkan pesan error (mungkin ID tidak ditemukan)
            header("Location: kategori.php?status=gagal");
            exit;
        }
        $stmt->close();
    } else {
        // Tangani error saat menyiapkan statement
        die("Error preparing statement: " . $koneksi->error);
    }
} else {
    // Tangani kasus di mana ID tidak diberikan
    header("Location: kategori.php?status=error");
    exit;
}
?>