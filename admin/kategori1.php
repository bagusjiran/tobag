<?php
include "koneksi.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Kategori Produk - Admin of ToBag</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Kategori Produk</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
          <li class="breadcrumb-item active">Kategori Produk</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <div class="row">
      <div class="col-lg-12">
        <div class="card-body">
          <a href="t.kategori.php" class="btn btn-primary mt-3">
            <i class="bi bi-plus-lg"></i> Tambah Data
          </a>
        </div>
      </div>
    </div>

    <section class="section dashboard">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Daftar Kategori</h5>

              <!-- Tampilkan Pesan Status -->
              <?php
              if (isset($_GET['status'])) {
                if ($_GET['status'] == 'sukses') {
                  echo "<p style='color:green;'>Data berhasil diproses!</p>";
                } elseif ($_GET['status'] == 'gagal') {
                  echo "<p style='color:red;'>Data gagal diproses! (ID mungkin tidak ditemukan)</p>";
                } elseif ($_GET['status'] == 'error') {
                  echo "<p style='color:red;'>Terjadi kesalahan. Silakan coba lagi.</p>";
                }
              }
              ?>

              <!-- Table with stripped rows -->
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">NO</th>
                    <th scope="col">Nama Kategori</th>
                    <th scope="col">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $no = 1;

                  // Query untuk mengambil data kategori
                  $sql_query = "SELECT id_kategori, nm_kategori FROM tb_kategori";
                  $sql = mysqli_query($koneksi, $sql_query);

                  if (mysqli_num_rows($sql) > 0) {
                    while ($hasil = mysqli_fetch_array($sql)) {
                  ?>
                      <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $hasil['nm_kategori']; ?></td>
                        <td>
                          <!-- Tombol Edit -->
                          <a href="e.kategori.php?id=<?php echo $hasil['id_kategori']; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> Edit
                          </a>
                          <!-- Tombol Hapus -->
                          <a href="h.kategori.php?id=<?php echo $hasil['id_kategori']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                            <i class="bi bi-trash"></i> Hapus
                          </a>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                  ?>
                    <tr>
                      <td colspan="3" class="text-center">Belum Ada Data</td>
                    </tr>
                  <?php
                  }
                  ?>
                </tbody>
              </table>
              <!-- End Table with stripped rows -->
            </div>
          </div>
        </div>
      </div>
    </section>
  </main><!-- End #main -->

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>