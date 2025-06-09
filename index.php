<?php
session_start();
include 'admin/koneksi.php';

// Tangani pengambilan data keranjang hanya jika pengguna sudah login
if (isset($_SESSION['id_user'])) {
    $id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']);

    // Ambil data keranjang
    $query_pesanan = mysqli_query($koneksi, "
        SELECT p.*, pr.nm_produk, pr.gambar, pr.harga, pr.stok 
        FROM tb_pesanan p
        JOIN tb_produk pr ON p.id_produk = pr.id_produk
        WHERE p.id_user = '$id_user'
    ");

    // Hitung total untuk mini-cart dan tabel
    $subtotal = 0;
    $cart_items = [];
    $cart_count = 0;
    while ($row = mysqli_fetch_assoc($query_pesanan)) {
        $cart_items[] = $row;
        $subtotal += $row['qty'] * $row['harga'];
        $cart_count++;
    }
    $diskon = 0;
    if ($subtotal > 3000000) {
        $diskon = 0.07 * $subtotal;
    } elseif ($subtotal > 1500000) {
        $diskon = 0.05 * $subtotal;
    }
    $total_bayar = $subtotal - $diskon;
} else {
    // Tetapkan nilai default jika pengguna belum login
    $subtotal = 0;
    $cart_items = [];
    $cart_count = 0;
    $diskon = 0;
    $total_bayar = 0;
}

// Proses checkout (tambahkan pemeriksaan login)
if (isset($_POST['checkout'])) {
    if (!isset($_SESSION['id_user'])) {
        echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
        exit;
    }
    if (empty($cart_items)) {
        echo "<script>alert('Keranjang kosong!'); window.location='belanja.php';</script>";
        exit;
    }

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Periksa stok untuk semua item
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
        echo "<script>alert('Checkout berhasil!'); window.location='cart.php';</script>";
        exit;
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        mysqli_rollback($koneksi);
        echo "<script>alert('{$e->getMessage()}'); window.location='cart.php';</script>";
        exit;
    }
}

// Proses update cart (tambahkan pemeriksaan login)
if (isset($_POST['update_cart'])) {
    if (!isset($_SESSION['id_user'])) {
        echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
        exit;
    }
    foreach ($_POST['qty'] as $id_pesanan => $new_qty) {
        $id_pesanan = mysqli_real_escape_string($koneksi, $id_pesanan);
        $new_qty = (int)$new_qty;
        if ($new_qty < 1) $new_qty = 1;

        // Periksa stok produk
        $query_check = mysqli_query($koneksi, "
            SELECT pr.stok 
            FROM tb_pesanan p
            JOIN tb_produk pr ON p.id_produk = pr.id_produk
            WHERE p.id_pesanan = '$id_pesanan' AND p.id_user = '$id_user'
        ");
        $row = mysqli_fetch_assoc($query_check);
        if ($new_qty > $row['stok']) {
            echo "<script>alert('Stok tidak mencukupi untuk pesanan ini!'); window.location='cart.php';</script>";
            exit;
        }

        $query_update = mysqli_query($koneksi, "UPDATE tb_pesanan SET qty = '$new_qty' WHERE id_pesanan = '$id_pesanan' AND id_user = '$id_user'");
        if (!$query_update) {
            echo "<script>alert('Gagal memperbarui jumlah pesanan!'); window.location='cart.php';</script>";
            exit;
        }
    }
    echo "<script>alert('Keranjang berhasil diperbarui!'); window.location='cart.php';</script>";
    exit;
}
?>

<!doctype html>
<html class="no-js" lang="zxx">

<!-- shop-list31:48-->

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Belanja - ToBag</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
    <!-- Material Design Iconic Font-V2.2.0 -->
    <link rel="stylesheet" href="css/material-design-iconic-font.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <!-- Font Awesome Stars-->
    <link rel="stylesheet" href="css/fontawesome-stars.css">
    <!-- Meanmenu CSS -->
    <link rel="stylesheet" href="css/meanmenu.css">
    <!-- owl carousel CSS -->
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <!-- Slick Carousel CSS -->
    <link rel="stylesheet" href="css/slick.css">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="css/animate.css">
    <!-- Jquery-ui CSS -->
    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <!-- Venobox CSS -->
    <link rel="stylesheet" href="css/venobox.css">
    <!-- Nice Select CSS -->
    <link rel="stylesheet" href="css/nice-select.css">
    <!-- Magnific Popup CSS -->
    <link rel="stylesheet" href="css/magnific-popup.css">
    <!-- Bootstrap V4.1.3 Fremwork CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Helper CSS -->
    <link rel="stylesheet" href="css/helper.css">
    <!-- Main Style CSS -->
    <link rel="stylesheet" href="style.css">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="css/responsive.css">
    <!-- Modernizr js -->
    <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .nice-select .list {
            max-height: none !important;
            /* biar tidak terbatas tingginya */
            overflow: visible !important;
        }

        .nice-select .list {
            max-height: 300px !important;
            overflow-y: auto !important;
        }
    </style>
</head>

<body>
    <!--[if lt IE 8]>
		<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->
    <!-- Begin Body Wrapper -->
    <div class="body-wrapper">
        <!-- Begin Header Area -->
        <header class="header-top">
            <!-- Begin Header Top Area -->
            <!-- Header Top Area End Here -->
            <!-- Begin Header Middle Area -->
            <div class="header-middle pl-sm-0 pr-sm-0 pl-xs-0 pr-xs-0">
                <div class="container">
                    <div class="row">
                        <!-- Begin Header Logo Area -->
                        <div class="col-lg-3">
                            <div class="logo pb-sm-30 pb-xs-30">
                                <a href="index.php">
                                    <h1>ToBag</h1>
                                </a>
                            </div>
                        </div>
                        <!-- Header Logo Area End Here -->
                        <!-- Begin Header Middle Right Area -->
                        <div class="col-lg-9 pl-0 ml-sm-15 ml-xs-15">
                            <!-- Begin Header Middle Searchbox Area -->
                            <form action="" method="GET" class="hm-searchbox">
                                <select name="kategori" class="nice-select select-search-category">
                                    <option value="">All</option>
                                    <?php
                                    include 'admin/koneksi.php';
                                    $kategoriQuery = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY nm_kategori ASC");
                                    while ($kategori = mysqli_fetch_assoc($kategoriQuery)) {
                                        $selected = (isset($_GET['kategori']) && $_GET['kategori'] == $kategori['id_kategori']) ? 'selected' : '';
                                        echo "<option value='{$kategori['id_kategori']}' $selected>{$kategori['nm_kategori']}</option>";
                                    }
                                    ?>
                                </select>
                                <input type="text" name="keyword" placeholder="Enter your search key ..." value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
                                <button class="li-btn" type="submit"><i class="fa fa-search"></i></button>
                            </form>
                            <div class="header-middle-right">
                                <ul class="hm-menu">
                                    <?php if (!isset($_SESSION['id_user'])) { ?>
                                        <li class="hm-wishlist">
                                            <a href="login.php" title="Login">
                                                <i class="fa fa-user-circle-o"></i>
                                            </a>
                                        </li>
                                    <?php } else { ?>
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
                                        <!-- Mini Cart -->
                                        <li class="hm-minicart">
                                            <div class="hm-minicart-trigger">
                                                <span class="item-icon"></span>
                                                <span class="item-text">Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                                    <span class="cart-item-count"><?= $cart_count ?></span>
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
                                                            <button class="close delete-item" data-id="<?= $item['id_pesanan'] ?>">
                                                                <i class="fa fa-close"></i>
                                                            </button>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                                <p class="minicart-total">SUBTOTAL: <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span></p>
                                                <div class="minicart-button">
                                                    <a href="cart.php" class="li-button li-button-dark li-button-fullwidth li-button-sm">
                                                        <span>View Full Cart</span>
                                                    </a>
                                                    <form method="POST" action="cart.php">
                                                        <input type="hidden" name="checkout" value="1">
                                                        <button type="submit" name="checkout" class="li-button li-button-fullwidth li-button-sm" style="border: none !important;">
                                                            <span>Chechout</span> 
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <!-- Header Middle Right Area End Here -->
                        </div>
                        <!-- Header Middle Right Area End Here -->
                    </div>
                </div>
            </div>
            <!-- Header Middle Area End Here -->
            <!-- Begin Header Bottom Area -->
            <div class="header-bottom header-sticky stick d-none d-lg-block d-xl-block">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Begin Header Bottom Menu Area -->
                            <div class="hb-menu">
                                <nav>
                                    <ul>
                                        <li><a href="index.php">Beranda</a></li>
                                        <li><a href="belanja.php">Belanja</a></li>
                                        <li><a href="contact.php">Hubungi Kami</a></li>
                                    </ul>
                                </nav>
                            </div>
                            <!-- Header Bottom Menu Area End Here -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Header Bottom Area End Here -->
            <!-- Begin Mobile Menu Area -->
            <div class="mobile-menu-area d-lg-none d-xl-none col-12">
                <div class="container">
                    <div class="row">
                        <div class="mobile-menu">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mobile Menu Area End Here -->
        </header>
            <!-- Header Area End Here -->
            <!-- Begin Slider With Banner Area -->
            <div class="slider-with-banner">
                <div class="container">
                    <div class="row">
                        <!-- Begin Slider Area -->
                        <div class="col-lg-8 col-md-8">
                            <div class="slider-area">
                                <div class="slider-active owl-carousel">
                                    <!-- Begin Single Slide Area -->
                                    <div class="single-slide align-center-left  animation-style-01 bg-1">
                                        <div class="slider-progress"></div>
                                        <div class="slider-content">
                                            <h5>Sale Offer <span>-20% Off</span> This Week</h5>
                                            <h2>Chamcham Galaxy S9 | S9+</h2>
                                            <h3>Starting at <span>$1209.00</span></h3>
                                            <div class="default-btn slide-btn">
                                                <a class="links" href="shop-left-sidebar.html">Shopping Now</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Slide Area End Here -->
                                    <!-- Begin Single Slide Area -->
                                    <div class="single-slide align-center-left animation-style-02 bg-2">
                                        <div class="slider-progress"></div>
                                        <div class="slider-content">
                                            <h5>Sale Offer <span>Black Friday</span> This Week</h5>
                                            <h2>Work Desk Surface Studio 2018</h2>
                                            <h3>Starting at <span>$824.00</span></h3>
                                            <div class="default-btn slide-btn">
                                                <a class="links" href="shop-left-sidebar.html">Shopping Now</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Slide Area End Here -->
                                    <!-- Begin Single Slide Area -->
                                    <div class="single-slide align-center-left animation-style-01 bg-3">
                                        <div class="slider-progress"></div>
                                        <div class="slider-content">
                                            <h5>Sale Offer <span>-10% Off</span> This Week</h5>
                                            <h2>Phantom 4 Pro+ Obsidian</h2>
                                            <h3>Starting at <span>$1849.00</span></h3>
                                            <div class="default-btn slide-btn">
                                                <a class="links" href="shop-left-sidebar.html">Shopping Now</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Slide Area End Here -->
                                </div>
                            </div>
                        </div>
                        <!-- Slider Area End Here -->
                    <!-- Begin Li Banner Area -->
                    <div class="col-lg-4 col-md-4 text-center pt-sm-30 pt-xs-30">
                        <div class="li-banner">
                            <a href="#">
                                <img src="images/banner/1_1.jpg" alt="">
                            </a>
                        </div>
                        <div class="li-banner mt-15 mt-md-30 mt-xs-25 mb-xs-5">
                            <a href="#">
                                <img src="images/banner/1_2.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <!-- Li Banner Area End Here -->
                </div>
            </div>
        </div>
        <!-- Slider With Banner Area End Here -->
        <!-- Begin Static Top Area -->
        <div class="static-top-wrap">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="static-top-content mt-sm-30">
                            Promo Spesial Akhir Pekan dari <strong>Technozone</strong> – Gunakan Kode Kupon:
                            <span>TECHNO15</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Static Top Area End Here -->
        <section class="product-area li-laptop-product pt-60 pb-45 pt-sm-50 pt-xs-60">
            <div class="container">
                <div class="row">
                    <!-- Begin Li's Section Area -->
                    <div class="col-lg-12">
                        <div class="li-section-title">
                            <h2>
                                <span>Produk Kami</span>
                            </h2>
                        </div>
                        <div class="row">
                            <div class="product-active owl-carousel">
                                <?php
                                include "admin/koneksi.php"; // Pastikan file koneksi sudah sesuai

                                $query = mysqli_query($koneksi, "
    SELECT p.*, k.nm_kategori 
    FROM tb_produk p 
    JOIN tb_kategori k ON p.id_kategori = k.id_kategori 
    ORDER BY RAND() LIMIT 5
");

                                while ($data = mysqli_fetch_assoc($query)) {
                                ?>
                                    <div class="col-lg-12">
                                        <!-- single-product-wrap start -->
                                        <div class="single-product-wrap">
                                            <div class="product-image">
                                                <a href="detail_produk.php?id=<?php echo $data['id_produk']; ?>">
                                                    <img src="admin/produk_img/<?php echo $data['gambar']; ?>" alt="<?php echo $data['nm_produk']; ?>" width="300" height="300">
                                                </a>
                                            </div>
                                            <div class="product_desc">
                                                <div class="product_desc_info">
                                                    <div class="product-review">
                                                        <h5 class="manufacturer">
                                                            <a href="#"><?php echo strtoupper($data['nm_kategori']); ?>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <h4><a class="product_name" href="detail_produk.php?id=<?php echo $data['id_produk']; ?>">
                                                            <?php echo $data['nm_produk']; ?>
                                                        </a></h4>
                                                    <div class="price-box">
                                                        <span class="new-price">Rp<?php echo number_format($data['harga'], 0, ',', '.'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="add-actions">
                                                    <ul class="add-actions-link">
                                                        <li class="add-cart active"><a href="detail_produk.php?id=<?php echo $data['id_produk']; ?>">Beli Sekarang</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- single-product-wrap end -->
                                    </div>
                                <?php } ?>

                            </div>
                        </div>
                    </div>
                    <!-- Li's Section Area End Here -->
                </div>
            </div>
        </section>            <!-- Li's Trendding Products Area End Here -->
            <!-- Begin Footer Area -->
            <div class="footer">
            <!-- Begin Footer Static Top Area -->
            <div class="footer-static-top">
                <div class="container">
                    <!-- Begin Footer Shipping Area -->
                    <div class="footer-shipping pt-60 pb-55 pb-xs-25">
                        <div class="row">
                            <!-- Mulai Area Kotak Pengiriman Li -->
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
                            <!-- Akhir Area Kotak Pengiriman Li -->

                            <!-- Mulai Area Kotak Pengiriman Li -->
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
                            <!-- Akhir Area Kotak Pengiriman Li -->

                            <!-- Mulai Area Kotak Pengiriman Li -->
                            <div class="col-lg-3 col-md-6 col-sm-6 pb-xs-30">
                                <div class="li-shipping-inner-box">
                                    <div class="shipping-icon">
                                        <img src="images/shipping-icon/3.png" alt="Ikon Pengiriman">
                                    </div>
                                    <div class="shipping-text">
                                        <h2>Belanja dengan aman</h2>
                                        <p Per>Perlindungan terhadap pembelian setiap teransaksi yang ada.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Akhir Area Kotak Pengiriman Li -->

                            <!-- Mulai Area Kotak Pengiriman Li -->
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
                            <!-- Akhir Area Kotak Pengiriman Li -->
                        </div>
                    </div>

                    <!-- Footer Shipping Area End Here -->
                </div>
            </div>
            <!-- Footer Static Top Area End Here -->
            <!-- Begin Footer Static Middle Area -->
            <div class="footer-static-middle">
                <div class="container">
                    <div class="footer-logo-wrap pt-50 pb-35">
                        <div class="row">
                            <!-- Begin Footer Logo Area -->
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

                            <!-- Footer Logo Area End Here -->
                            <!-- Begin Footer Block Area -->
                            <div class="col-lg-2 col-md-3 col-sm-6">

                            </div>
                            <!-- Footer Block Area End Here -->
                            <!-- Begin Footer Block Area -->
                            <div class="col-lg-2 col-md-3 col-sm-6">

                            </div>
                            <!-- Footer Block Area End Here -->
                            <!-- Begin Footer Block Area -->
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
                                <!-- Begin Footer Newsletter Area -->
                                <!-- Footer Newsletter Area End Here -->
                            </div>
                            <!-- Footer Block Area End Here -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer Static Middle Area End Here -->
            <!-- Begin Footer Static Bottom Area -->
            <div class="footer-static-bottom pt-55 pb-55">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Begin Footer Payment Area -->
                            <div class="copyright text-center">
                                <a href="#">
                                    <img src="images/payment/1.png" alt="">
                                </a>
                            </div>
                            <!-- Footer Payment Area End Here -->
                            <!-- Begin Copyright Area -->
                            <div class="copyright text-center pt-25">
                                <span><a target="_blank" href="https://wa.me/6282322238082">Disusun oleh : Bagus Jiran</a></span>
                            </div>
                            <!-- Copyright Area End Here -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer Static Bottom Area End Here -->
        </div>
        <!-- Footer Area End Here -->
        <!-- Begin Quick View | Modal Area -->
        <div class="modal fade modal-wrapper" id="exampleModalCenter">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="modal-inner-area row">
                            <div class="col-lg-5 col-md-6 col-sm-6">
                                <!-- Product Details Left -->
                                <div class="product-details-left">
                                    <div class="product-details-images slider-navigation-1">
                                        <div class="lg-image">
                                            <img src="" alt="product image" id="modal-gambar">
                                        </div>
                                    </div>
                                </div>
                                <!--// Product Details Left -->
                            </div>

                            <div class="col-lg-7 col-md-6 col-sm-6">
                                <div class="product-details-view-content pt-60">
                                    <div class="product-info">
                                        <h2 id="modal-nama-produk"></h2>
                                        <span class="product-details-ref" id="modal-kategori">Kategori</span>
                                        <div class="price-box pt-20">
                                            <span class="new-price new-price-2" id="modal-harga">Rp0</span>
                                        </div>
                                        <div class="product-desc">
                                            <p id="modal-desk"></p>
                                            <p><strong>Stok tersedia:</strong> <span id="modal-stok">0</span> unit</p> <!--  Tambahan -->
                                        </div>

                                        <div class="single-add-to-cart">
                                            <form action="tambah_ke_keranjang.php" method="POST" class="cart-quantity">
                                                <input type="hidden" name="id_produk" id="input-id-produk">
                                                <input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?>">
                                                <input type="hidden" name="harga" id="input-harga">
                                                <input type="hidden" name="redirect_url" value="belanja.php">

                                                <div class="quantity">
                                                    <label>Jumlah</label>
                                                    <div class="cart-plus-minus">
                                                        <input class="cart-plus-minus-box" name="jumlah" id="input-jumlah" value="1" type="text">
                                                        <div class="dec qtybutton"><i class="fa fa-angle-down"></i></div>
                                                        <div class="inc qtybutton"><i class="fa fa-angle-up"></i></div>
                                                    </div>
                                                </div>

                                                <button class="add-to-cart" type="submit">Beli Sekarang</button>
                                            </form>

                                        </div>
                                        <div class="product-additional-info pt-25">
                                            <div class="product-social-sharing pt-25">
                                                <ul>
                                                    <li class="facebook"><a href="#"><i class="fa fa-facebook"></i>Facebook</a></li>
                                                    <li class="twitter"><a href="#"><i class="fa fa-twitter"></i>Twitter</a></li>
                                                    <li class="instagram"><a href="#"><i class="fa fa-instagram"></i>Instagram</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('.quick-view').click(function() {
                    var id = $(this).data('id');

                    $.ajax({
                        url: 'get-produk.php',
                        type: 'GET',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(data) {
                            $('#modal-nama-produk').text(data.nm_produk);
                            $('#modal-kategori').text(data.nm_kategori);
                            $('#modal-harga').text('Rp' + parseInt(data.harga).toLocaleString('id-ID'));
                            $('#modal-desk').text(data.desk);
                            $('#modal-gambar').attr('src', 'admin/produk_img/' + data.gambar);
                            $('#modal-stok').text(data.stok);

                            // Set hidden form fields
                            $('#input-id-produk').val(data.id_produk);
                            $('#input-harga').val(data.harga);

                            // Reset jumlah
                            $('#input-jumlah').val(1);

                            // Tampilkan modal
                            $('#exampleModalCenter').modal('show');
                        },
                        error: function() {
                            alert('Gagal mengambil data produk.');
                        }
                    });
                });
            });
        </script>


        <!-- Quick View | Modal Area End Here -->
    </div>
    <!-- Body Wrapper End Here -->
    <!-- jQuery-V1.12.4 -->
    <script src="js/vendor/jquery-1.12.4.min.js"></script>
    <!-- Popper js -->
    <script src="js/vendor/popper.min.js"></script>
    <!-- Bootstrap V4.1.3 Fremwork js -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Ajax Mail js -->
    <script src="js/ajax-mail.js"></script>
    <!-- Meanmenu js -->
    <script src="js/jquery.meanmenu.min.js"></script>
    <!-- Wow.min js -->
    <script src="js/wow.min.js"></script>
    <!-- Slick Carousel js -->
    <script src="js/slick.min.js"></script>
    <!-- Owl Carousel-2 js -->
    <script src="js/owl.carousel.min.js"></script>
    <!-- Magnific popup js -->
    <script src="js/jquery.magnific-popup.min.js"></script>
    <!-- Isotope js -->
    <script src="js/isotope.pkgd.min.js"></script>
    <!-- Imagesloaded js -->
    <script src="js/imagesloaded.pkgd.min.js"></script>
    <!-- Mixitup js -->
    <script src="js/jquery.mixitup.min.js"></script>
    <!-- Countdown -->
    <script src="js/jquery.countdown.min.js"></script>
    <!-- Counterup -->
    <script src="js/jquery.counterup.min.js"></script>
    <!-- Waypoints -->
    <script src="js/waypoints.min.js"></script>
    <!-- Barrating -->
    <script src="js/jquery.barrating.min.js"></script>
    <!-- Jquery-ui -->
    <script src="js/jquery-ui.min.js"></script>
    <!-- Venobox -->
    <script src="js/venobox.min.js"></script>
    <!-- Nice Select js -->
    <script src="js/jquery.nice-select.min.js"></script>
    <!-- ScrollUp js -->
    <script src="js/scrollUp.min.js"></script>
    <!-- Main/Activator js -->
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Real-time quantity update
            function updateQty(element) {
                var $input = $(element);
                var idPesanan = $input.data('id');
                var newQty = parseInt($input.val());
                var $row = $input.closest('tr');
                var $loading = $row.find('.loading');

                if (newQty < 1) {
                    newQty = 1;
                    $input.val(newQty);
                }

                $loading.show();
                $.ajax({
                    url: 'update_qty.php',
                    method: 'POST',
                    data: { id_pesanan: idPesanan, qty: newQty },
                    dataType: 'json',
                    success: function(response) {
                        $loading.hide();
                        if (response.success) {
                            var price = parseFloat($row.find('.li-product-price .amount').text().replace('Rp', '').replace(/\./g, '').replace(',', '.'));
                            var newSubtotal = price * newQty;
                            $row.find('.product-subtotal .amount').text('Rp' + newSubtotal.toLocaleString('id-ID'));

                            $('.cart-page-total ul').html(`
                                <li>Subtotal <span>Rp ${response.subtotal.toLocaleString('id-ID')}</span></li>
                                <li>Diskon <span>Rp ${response.diskon.toLocaleString('id-ID')}</span></li>
                                <li>Total <span>Rp ${response.total_bayar.toLocaleString('id-ID')}</span></li>
                            `);

                            $('.hm-minicart-trigger .item-text').html(`
                                Rp ${response.subtotal.toLocaleString('id-ID')}
                                <span class="cart-item-count">${response.item_count}</span>
                            `);

                            $('.minicart-product-list').find(`[data-id="${idPesanan}"]`).closest('li').find('span').text(
                                'Rp ' + newSubtotal.toLocaleString('id-ID') + ' x ' + newQty
                            );
                        } else {
                            alert(response.message || 'Gagal memperbarui jumlah pesanan!');
                            $input.val($input.data('original-value'));
                        }
                    },
                    error: function() {
                        $loading.hide();
                        alert('Terjadi kesalahan saat memperbarui jumlah pesanan.');
                        $input.val($input.data('original-value'));
                    }
                });
            }

            $('.qty-input').on('change', function() {
                $(this).data('original-value', $(this).val());
                updateQty(this);
            });

            // Delete item
            $('.delete-item').on('click', function(e) {
                e.preventDefault();
                if (!confirm('Yakin hapus item ini?')) return;

                var idPesanan = $(this).data('id');
                var $row = $(this).closest('tr');
                var $loading = $row.find('.loading');

                $loading.show();
                $.ajax({
                    url: 'hapus_produk.php',
                    method: 'POST',
                    data: { id: idPesanan },
                    dataType: 'json',
                    success: function(response) {
                        $loading.hide();
                        if (response.success) {
                            $row.remove();
                            $('.cart-page-total ul').html(`
                                <li>Subtotal <span>Rp ${response.subtotal.toLocaleString('id-ID')}</span></li>
                                <li>Diskon <span>Rp ${response.diskon.toLocaleString('id-ID')}</span></li>
                                <li>Total <span>Rp ${response.total_bayar.toLocaleString('id-ID')}</span></li>
                            `);
                            $('.hm-minicart-trigger .item-text').html(`
                                Rp ${response.subtotal.toLocaleString('id-ID')}
                                <span class="cart-item-count">${response.item_count || 0}</span>
                            `);
                            $('.minicart-product-list').find(`[data-id="${idPesanan}"]`).closest('li').remove();
                            if ($('tbody tr').length === 0) {
                                $('tbody').html('<tr><td colspan="6">Keranjang kosong.</td></tr>');
                            }
                        } else {
                            alert(response.message || 'Gagal menghapus item!');
                        }
                    },
                    error: function() {
                        $loading.hide();
                        alert('Terjadi kesalahan saat menghapus item.');
                    }
                });
            });
        });
    </script>
</body>

<!-- shop-list31:48-->

</html>