<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu untuk melihat detail produk atau menambahkan ke keranjang.'); window.location.href = 'login.php';</script>";
    exit;
}
?>
<!doctype html>
<html class="no-js" lang="zxx">

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
            overflow: visible !important;
        }

        .nice-select .list {
            max-height: 300px !important;
            overflow-y: auto !important;
        }
    </style>
</head>

<body>
    <div class="body-wrapper">
        <header class="header-top">
            <div class="header-middle pl-sm-0 pr-sm-0 pl-xs-0 pr-xs-0">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="logo pb-sm-30 pb-xs-30">
                                <a href="index.php">
                                    <h1>ToBag</h1>
                                </a>
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
                                            <span class="item-text">£80.00
                                                <span class="cart-item Nigel
                                                <span class="cart-item-count">2</span>
                                            </span>
                                        </div>
                                        <span></span>
                                        <div class="minicart">
                                            <ul class="minicart-product-list">
                                                <li>
                                                    <a href="single-product.html" class="minicart-product-image">
                                                        <img src="images/product/small-size/1.jpg" alt="cart products">
                                                    </a>
                                                    <div class="minicart-product-details">
                                                        <h6><a href="single-product.html">Aenean eu tristique</a></h6>
                                                        <span>£40 x 1</span>
                                                    </div>
                                                    <button class="close">
                                                        <i class="fa fa-close"></i>
                                                    </button>
                                                </li>
                                                <li>
                                                    <a href="single-product.html" class="minicart-product-image">
                                                        <img src="images/product/small-size/2.jpg" alt="cart products">
                                                    </a>
                                                    <div class="minicart-product-details">
                                                        <h6><a href="single-product.html">Aenean eu tristique</a></h6>
                                                        <span>£40 x 1</span>
                                                    </div>
                                                    <button class="close">
                                                        <i class="fa fa-close"></i>
                                                    </button>
                                                </li>
                                            </ul>
                                            <p class="minicart-total">SUBTOTAL: <span>£80.00</span></p>
                                            <div class="minicart-button">
                                                <a href="cart.php" class="li-button li-button-dark li-button-fullwidth li-button-sm">
                                                    <span>View Full Cart</span>
                                                </a>
                                                <a href="checkout.html" class="li-button li-button-fullwidth li-button-sm">
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
                        <li><a href="Belanja.php">Belanja</a></li>
                        <li class="active">Detail Produk</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        include 'admin/koneksi.php';

        if (!isset($_GET['id'])) {
            echo "<script>alert('Produk tidak ditemukan'); window.location.href = 'belanja.php';</script>";
            exit;
        }

        $id = $_GET['id'];
        $query = mysqli_query($koneksi, "SELECT p.*, k.nm_kategori FROM tb_produk p LEFT JOIN tb_kategori k ON p.id_kategori = k.id_kategori WHERE p.id_produk='$id'");
        $data = mysqli_fetch_assoc($query);

        if (!$data) {
            echo "<script>alert('Data produk tidak ditemukan'); window.location.href = 'belanja.php';</script>";
            exit;
        }
        ?>
        <div class="content-wraper">
            <div class="container">
                <div class="row single-product-area">
                    <div class="col-lg-5 col-md-6">
                        <div class="product-details-left">
                            <div class="product-details-images slider-navigation-1">
                                <div class="lg-image">
                                    <a class="popup-img venobox vbox-item" href="admin/produk_img/<?= $data['gambar'] ?>" data-gall="myGallery">
                                        <img src="admin/produk_img/<?= $data['gambar'] ?>" alt="<?= $data['nm_produk'] ?>" width="300" height="300">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($data['stok'] == 0) : ?>
                        <script>
                            alert('Stok produk ini sudah habis.');
                            window.location.href = 'belanja.php';
                        </script>
                        <?php exit; ?>
                    <?php endif; ?>
                    <div class="col-lg-7 col-md-6">
                        <div class="product-details-view-content p-2">
                            <div class="product-info">
                                <h2><?= $data['nm_produk'] ?></h2>
                                <span class="product-details-ref">Kategori: <?= $data['nm_kategori'] ?></span>
                                <div class="price-box pt-20">
                                    <span class="new-price new-price-2">Rp<?= number_format($data['harga'], 0, ',', '.') ?></span>
                                </div>
                                <div class="product-desc">
                                    <p><strong>Stok tersedia:</strong> <?= $data['stok'] ?> unit</p>
                                </div>
                                <div class="single-add-to-cart">
                                    <form action="tambah_ke_keranjang.php" method="POST" class="cart-quantity">
                                        <input type="hidden" name="id_produk" value="<?= $data['id_produk'] ?>">
                                        <input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?>">
                                        <input type="hidden" name="harga" value="<?= $data['harga'] ?>">
                                        <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI'] ?>">
                                        <div class="quantity">
                                            <label>Jumlah</label>
                                            <div class="cart-plus-minus">
                                                <input type="number" class="cart-plus-minus-box" name="jumlah" value="1" min="1" max="<?= $data['stok'] ?>">
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
                <!-- Begin Product Area -->
        <div class="product-area pt-35">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="li-product-tab">
                            <ul class="nav li-product-menu">
                                <li><a class="active" data-toggle="tab" href="#description"><span>Deskripsi</span></a></li>
                            </ul>
                        </div>
                        <!-- Begin Li's Tab Menu Content Area -->
                    </div>
                </div>
                <div class="tab-content">
                    <div id="description" class="tab-pane active show" role="tabpanel">
                        <div class="product-description">
                            <span><?= nl2br($data['desk']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- Product Area End Here -->
        <section class="product-area li-laptop-product pt-30 pb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="li-section-title">
                            <h2><span>Produk Lainnya</span></h2>
                        </div>
                        <div class="row">
                            <div class="product-active owl-carousel">
                                <?php
                                $query_produk_lain = mysqli_query($koneksi, "SELECT p.*, k.nm_kategori FROM tb_produk p LEFT JOIN tb_kategori k ON p.id_kategori = k.id_kategori WHERE p.id_produk != '$id' ORDER BY RAND() LIMIT 6");
                                while ($p = mysqli_fetch_array($query_produk_lain)) {
                                ?>
                                    <div class="col-lg-12">
                                        <div class="single-product-wrap">
                                            <div class="product-image">
                                                <a href="detail_produk.php?id=<?= $p['id_produk'] ?>">
                                                    <img src="admin/produk_img/<?= $p['gambar'] ?>" alt="<?= $p['nm_produk'] ?>" width="300" height="300">
                                                </a>
                                            </div>
                                            <div class="product_desc">
                                                <div class="product_desc_info">
                                                    <div class="product-review">
                                                        <h5 class="manufacturer">
                                                            <a href="#">Kategori: <?= $p['nm_kategori'] ?></a>
                                                        </h5>
                                                    </div>
                                                    <h4><a class="product_name" href="detail_produk.php?id=<?= $p['id_produk'] ?>"><?= $p['nm_produk'] ?></a></h4>
                                                    <div class="price-box">
                                                        <span class="new-price">Rp<?= number_format($p['harga'], 0, ',', '.') ?></span>
                                                    </div>
                                                </div>
                                                <div class="add-actions">
                                                    <ul class="add-actions-link">
                                                        <li class="add-cart active"><a href="detail_produk.php?id=<?= $p['id_produk'] ?>">Beli Sekarang</a></li>
                                                        <li><a href="detail_produk.php?id=<?= $p['id_produk'] ?>" class="quick-view" data-toggle="modal" data-target="#exampleModalCenter" data-id="<?= $p['id_produk']; ?>"><i class="fa fa-eye"></i></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
                                <a href="#">
                                    <img src="images/payment/1.png" alt="">
                                </a>
                            </div>
                            <div class="copyright text-center pt-25">
                                <span><a target="_blank" href="https://wa.me/6282322238082">Disusun oleh : Bagus Jiran</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade modal-wrapper" id="exampleModalCenter">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <div class="modal-inner-area row">
                            <div class="col-lg-5 col-md-6 col-sm-6">
                                <div class="product-details-left">
                                    <div class="product-details-images slider-navigation-1">
                                        <div class="lg-image">
                                            <img src="" alt="product image" id="modal-gambar">
                                        </div>
                                    </div>
                                </div>
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
                                            <p><strong>Stok tersedia:</strong> <span id="modal-stok">0</span> unit</p>
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
                        data: { id: id },
                        dataType: 'json',
                        success: function(data) {
                            $('#modal-nama-produk').text(data.nm_produk);
                            $('#modal-kategori').text(data.nm_kategori);
                            $('#modal-harga').text('Rp' + parseInt(data.harga).toLocaleString('id-ID'));
                            $('#modal-desk').text(data.desk);
                            $('#modal-gambar').attr('src', 'admin/produk_img/' + data.gambar);
                            $('#modal-stok').text(data.stok);
                            $('#input-id-produk').val(data.id_produk);
                            $('#input-harga').val(data.harga);
                            $('#input-jumlah').val(1);
                            $('#exampleModalCenter').modal('show');
                        },
                        error: function() {
                            alert('Gagal mengambil data produk.');
                        }
                    });
                });
            });
        </script>
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