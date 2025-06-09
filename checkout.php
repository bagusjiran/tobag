<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script><?php
session_start();
include 'admin/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']);

// Ambil data keranjang
$query_pesanan = mysqli_query($koneksi, "
    SELECT p.*, pr.nm_produk, pr.gambar, pr.harga, pr.stok 
    FROM tb_pesanan p
    JOIN tb_produk pr ON p.id_produk = pr.id_produk
    WHERE p.id_user = '$id_user'
");

// Hitung total
$subtotal = 0;
$cart_items = [];
while ($row = mysqli_fetch_assoc($query_pesanan)) {
    $cart_items[] = $row;
    $subtotal += $row['qty'] * $row['harga'];
}
$diskon = 0;
if ($subtotal > 3000000) {
    $diskon = 0.07 * $subtotal;
} elseif ($subtotal > 1500000) {
    $diskon = 0.05 * $subtotal;
}
$total_bayar = $subtotal - $diskon;

// Proses konfirmasi pesanan
if (isset($_POST['confirm_order'])) {
    if (empty($cart_items)) {
        echo "<script>alert('Keranjang kosong!'); window.location='cart.php';</script>";
        exit;
    }

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Periksa stok
        foreach ($cart_items as $item) {
            if ($item['qty'] > $item['stok']) {
                throw new Exception("Stok tidak mencukupi untuk produk: {$item['nm_produk']} (Tersedia: {$item['stok']}, Diminta: {$item['qty']})");
            }
        }

        // Generate ID penjualan
        $result = mysqli_query($koneksi, "SELECT MAX(RIGHT(id_jual, 3)) AS max_id FROM tb_jual");
        $row = mysqli_fetch_assoc($result);
        $last_id = $row['max_id'];
        $next_id = 'T' . str_pad((int)$last_id + 1, 3, '0', STR_PAD_LEFT);

        $tgl = date('Y-m-d H:i:s');
        $query_insert_jual = mysqli_query($koneksi, "INSERT INTO tb_jual (id_jual, id_user, tgl_jual, total, diskon) 
            VALUES ('$next_id', '$id_user', '$tgl', '$total_bayar', '$diskon')");

        if (!$query_insert_jual) {
            throw new Exception('Gagal menyimpan data penjualan!');
        }

        // Simpan detail penjualan dan kurangi stok
        foreach ($cart_items as $item) {
            $total = $item['qty'] * $item['harga'];
            $query_dtl = mysqli_query($koneksi, "INSERT INTO tb_jualdtl (id_jual, id_produk, qty, harga) 
                VALUES ('$next_id', '{$item['id_produk']}', '{$item['qty']}', '$total')");

            if (!$query_dtl) {
                throw new Exception('Gagal menyimpan detail penjualan!');
            }

            // Kurangi stok produk
            $query_update_stok = mysqli_query($koneksi, "UPDATE tb_produk SET stok = stok - {$item['qty']} WHERE id_produk = '{$item['id_produk']}'");
            if (!$query_update_stok) {
                throw new Exception('Gagal memperbarui stok produk!');
            }
        }

        // Hapus pesanan dari keranjang
        $hapus = mysqli_query($koneksi, "DELETE FROM tb_pesanan WHERE id_user = '$id_user'");
        if (!$hapus) {
            throw new Exception('Gagal menghapus keranjang!');
        }

        // Commit transaksi
        mysqli_commit($koneksi);
        echo "<script>alert('Pesanan berhasil dikonfirmasi!'); window.location='index.php';</script>";
        exit;
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        mysqli_rollback($koneksi);
        echo "<script>alert('{$e->getMessage()}'); window.location='checkout.php';</script>";
        exit;
    }
}
?>
<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Checkout - ToBag</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
    <link rel="stylesheet" href="css/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/fontawesome-stars.css">
    <link rel="stylesheet" href="css/meanmenu.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/slick.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <link rel="stylesheet" href="css/venobox.css">
    <link rel="stylesheet" href="css/nice-select.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/helper.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="body-wrapper">
        <header class="header-top">
            <div class="header-middle pl-sm-0 pr-sm-0 pl-xs-0 pr-xs-0">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="logo pb-sm-30 pb-xs-30">
                                <a href="index.php"><h1>ToBag</h1></a>
                            </div>
                        </div>
                        <div class="col-lg-9 pl-0 ml-sm-15 ml-xs-15">
                            <div class="header-middle-right">
                                <ul class="hm-menu">
                                    <li class="hm-wishlist dropdown">
                                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-user-circle-o"></i>
                                        </a>
                                        <ul class="dropdown-menu" style="padding: 10px; min-width: 150px; text-align: center;">
                                            <li style="padding: 5px 10px; font-weight: bold;">
                                                <?= htmlspecialchars($_SESSION['username']) ?>
                                            </li>
                                            <li><hr style="margin: 5px 0;"></li>
                                            <li>
                                                <a href="logout.php" style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                                    <i class="fa fa-sign-out"></i> Logout
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="hm-minicart">
                                        <div class="hm-minicart-trigger">
                                            <span class="item-icon"></span>
                                            <span class="item-text">Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                                <span class="cart-item-count"><?= count($cart_items) ?></span>
                                            </span>
                                        </div>
                                        <span></span>
                                        <div class="minicart">
                                            <ul class="minicart-product-list">
                                                <?php foreach ($cart_items as $item) { ?>
                                                    <li>
                                                        <a href="detail_produk.php?id=<?= $item['id_produk'] ?>" class="minicart-product-image">
                                                            <img src="admin/produk_img/<?= $item['gambar'] ?>" alt="<?= htmlspecialchars($item['nm_produk']) ?>">
                                                        </a>
                                                        <div class="minicart-product-details">
                                                            <h6><a href="detail_produk.php?id=<?= $item['id_produk'] ?>"><?= htmlspecialchars($item['nm_produk']) ?></a></h6>
                                                            <span>Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?> x <?= $item['qty'] ?></span>
                                                        </div>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                            <p class="minicart-total">SUBTOTAL: <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span></p>
                                            <div class="minicart-button">
                                                <a href="cart.php" class="li-button li-button-dark li-button-fullwidth li-button-sm">
                                                    <span>View Full Cart</span>
                                                </a>
                                                <a href="checkout.php" class="li-button li-button-fullwidth li-button-sm">
                                                    <span>Checkout</span>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-bottom header-sticky stick d-none d-lg-block d-xl-block">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="hb-menu">
                                <nav>
                                    <ul>
                                        <li><a href="index.php">Beranda</a></li>
                                        <li><a href="belanja.php">Belanja</a></li>
                                        <li><a href="contact.php">Hubungi Kami</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mobile-menu-area d-lg-none d-xl-none col-12">
                <div class="container">
                    <div class="row">
                        <div class="mobile-menu"></div>
                    </div>
                </div>
            </div>
        </header>
        <div class="breadcrumb-area">
            <div class="container">
                <div class="breadcrumb-content">
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li class="active">Checkout</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="Shopping-cart-area pt-60 pb-60">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2>Konfirmasi Pesanan</h2>
                        <div class="table-content table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="li-product-thumbnail">Gambar</th>
                                        <th class="cart-product-name">Produk</th>
                                        <th class="li-product-price">Harga</th>
                                        <th class="li-product-quantity">Jumlah</th>
                                        <th class="li-product-subtotal">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($cart_items)) {
                                        echo "<tr><td colspan='5'>Keranjang kosong.</td></tr>";
                                    } else {
                                        foreach ($cart_items as $row) {
                                            $subtotal_item = $row['qty'] * $row['harga'];
                                            ?>
                                            <tr>
                                                <td class="li-product-thumbnail">
                                                    <a href="detail_produk.php?id=<?= $row['id_produk'] ?>">
                                                        <img src="admin/produk_img/<?= $row['gambar'] ?>" alt="<?= htmlspecialchars($row['nm_produk']) ?>" width="70">
                                                    </a>
                                                </td>
                                                <td class="li-product-name">
                                                    <a href="detail_produk.php?id=<?= $row['id_produk'] ?>"><?= htmlspecialchars($row['nm_produk']) ?></a>
                                                </td>
                                                <td class="li-product-price">
                                                    <span class="amount">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                                                </td>
                                                <td class="quantity">
                                                    <span><?= $row['qty'] ?></span>
                                                </td>
                                                <td class="product-subtotal">
                                                    <span class="amount">Rp <?= number_format($subtotal_item, 0, ',', '.') ?></span>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-5 ml-auto">
                                <div class="cart-page-total">
                                    <h2>Total Pesanan</h2>
                                    <ul>
                                        <li>Subtotal <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span></li>
                                        <li>Diskon <speedan>Rp <?= number_format($diskon, 0, ',', '.') ?></span></li>
                                        <li>Total <span>Rp <?= number_format($total_bayar, 0, ',', '.') ?></span></li>
                                    </ul>
                                    <?php if (!empty($cart_items)) { ?>
                                        <form method="post" action="">
                                            <button type="submit" name="confirm_order" class="btn btn-dark mt-3">Konfirmasi Pesanan</button>
                                        </form>
                                    <?php } else { ?>
                                        <a href="belanja.php" class="btn btn-dark mt-3">Belanja Sekarang</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="footer-static-top">
                <div class="container">
                    <div class="footer-shipping pt-60 pb-55 pb-xs-25">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 col-sm-6 pb-sm-55 pb-xs-55">
                                <div class="li-shipping-inner-box">
                                    <div class="shipping-icon">
                                        <img src="images/shipping-icon/1.png" alt="Ikon Pengiriman">
                                    </div>
                                    <div class="shipping-text">
                                        <h2>Pengiriman Gratis</h2>
                                        <p>Dan pengembalian gratis sepuasnya. Lihat di halaman pengiriman.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6 pb-sm-55 pb-xs-55">
                                <div class="li-shipping-inner-box">
                                    <div class="shipping-icon">
                                        <img src="images/shipping-icon/2.png" alt="Ikon Pengiriman">
                                    </div>
                                    <div class="shipping-text">
                                        <h2>Pembayaran Terpercaya</h2>
                                        <p>Bayar dengan metode pembayaran yang aman dan terpercaya di seluruh indonesia.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6 pb-xs-30">
                                <div class="li-shipping-inner-box">
                                    <div class="shipping-icon">
                                        <img src="images/shipping-icon/3.png" alt="Ikon Pengiriman">
                                    </div>
                                    <div class="shipping-text">
                                        <h2>Belanja dengan aman</h2>
                                        <p>Perlindungan terhadap pembelian setiap teransaksi yang ada.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6 pb-xs-30">
                                <div class="li-shipping-inner-box">
                                    <div class="shipping-icon">
                                        <img src="images/shipping-icon/4.png" alt="Ikon Pengiriman">
                                    </div>
                                    <div class="shipping-text">
                                        <h2>Pusat Bantuan</h2>
                                        <p>melayani sepenuh hati.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-static-middle">
                <div class="container">
                    <div class="footer-logo-wrap pt-50 pb-35">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="footer-logo">
                                    <h1>ToBag</h1>
                                    <p class="info">
                                        ToBag kepercayaan anda menyediakan peralatan elektronik masa kini.
                                    </p>
                                </div>
                                <ul class="des">
                                    <li>
                                        <span>Alamat: </span>
                                        Jln. Pemuda KM.05 Cepu-Blora, Jawa Tengah, Indonesia
                                    </li>
                                    <li>
                                        <span>Telepon: </span>
                                        <a href="https://wa.me/6282322238082">(+62) 823 2223 8082</a>
                                    </li>
                                    <li>
                                        <span>Email: </span>
                                        <a href="mailto:info@ToBag.co.id">info@ToBag.co.id</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6"></div>
                            <div class="col-lg-2 col-md-3 col-sm-6"></div>
                            <div class="col-lg-4">
                                <div class="footer-block">
                                    <h3 class="footer-block-title">Ikuti Kami</h3>
                                    <ul class="social-link">
                                            <li class="twitter">
                                                <a href="https://x.com/BagusJiran694" data-toggle="tooltip" target="_blank" title="Twitter">
                                                    <i class="fa fa-twitter"></i>
                                                </a>
                                            </li>
                                            <li class="youtube">
                                                <a href="https://www.youtube.com/@bagusjiranriskohar" data-toggle="tooltip" target="_blank" title="Youtube">
                                                    <i class="fa fa-youtube"></i>
                                                </a>
                                            </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-static-bottom pt-55 pb-55">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="copyright text-center">
                                <a href="#"><img src="images/payment/1.png" alt=""></a>
                            </div>
                            <div class="copyright text-center pt-25">
                                <span><a target="_blank" href="https://wa.me/6282322238082">Disusun oleh: Bagus Jiran</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/vendor/jquery-1.12.4.min.js"></script>
    <script src="js/vendor/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/ajax-mail.js"></script>
    <script src="js/jquery.meanmenu.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/isotope.pkgd.min.js"></script>
    <script src="js/imagesloaded.pkgd.min.js"></script>
    <script src="js/jquery.mixitup.min.js"></script>
    <script src="js/jquery.countdown.min.js"></script>
    <script src="js/jquery.counterup.min.js"></script>
    <script src="js/waypoints.min.js"></script>
    <script src="js/jquery.barrating.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/venobox.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/scrollUp.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>