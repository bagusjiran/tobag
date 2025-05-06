<?php
include "koneksi.php";

// Ambil ID dari parameter URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk mengambil data kategori berdasarkan ID
    $sql = mysqli_query($koneksi, "SELECT * FROM tb_kategori WHERE id_kategori = '$id'");
    $data = mysqli_fetch_array($sql);

    // Jika data tidak ditemukan, tampilkan pesan error
    if (!$data) {
        echo "<script>alert('Data tidak ditemukan!'); window.location='kategori.php';</script>";
        exit;
    }
} else {
    // Jika ID tidak ada di URL, redirect ke halaman kategori
    header("Location: kategori.php");
    exit;
}

// Proses update data kategori
if (isset($_POST['simpan'])) {
    $nm_kategori = $_POST['nm_kategori'];

    // Query untuk mengupdate data kategori
    $query = mysqli_query($koneksi, "UPDATE tb_kategori SET nm_kategori = '$nm_kategori' WHERE id_kategori = '$id'");

    if ($query) {
        echo "<script>alert('Data berhasil diubah!'); window.location='kategori.php';</script>";
    } else {
        echo "<script>alert('Data gagal diubah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Edit Kategori - Admin of ToBag</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Edit Kategori</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
          <li class="breadcrumb-item"><a href="kategori.php">Kategori</a></li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Form Edit Kategori</h5>

              <!-- Form Edit Kategori -->
              <form method="post">
                <div class="mb-3">
                  <label for="nm_kategori" class="form-label">Nama Kategori</label>
                  <input type="text" class="form-control" id="nm_kategori" name="nm_kategori" value="<?php echo $data['nm_kategori']; ?>" required>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary" name="simpan">Simpan</button>
                  <a href="kategori.php" class="btn btn-secondary">Batal</a>
                </div>
              </form><!-- End Form Edit Kategori -->

            </div>
          </div>
        </div>
      </div>
    </section>
  </main><!-- End #main -->
</body>

</html>