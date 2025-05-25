<?php
session_start();
include 'admin/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']);

// Ambil data keranjang
$query_pesanan = mysqli_query($koneksi, "
    SELECT p.*, pr.nm_produk, pr.gambar, pr.harga 
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

// Proses checkout
if (isset($_POST['checkout'])) {
    if (empty($cart_items)) {
        echo "<script>alert('Keranjang kosong!'); window.location='cart.php';</script>";
        exit;
    }

    $result = mysqli_query($koneksi, "SELECT MAX(RIGHT(id_jual, 3)) AS max_id FROM tb_jual");
    $row = mysqli_fetch_assoc($result);
    $last_id = $row['max_id'];
    $next_id = 'T' . str_pad((int)$last_id + 1, 3, '0', STR_PAD_LEFT);

    $tgl = date('Y-m-d H:i:s');
    $query_insert_jual = mysqli_query($koneksi, "INSERT INTO tb_jual (id_jual, id_user, tgl_jual, total, diskon) 
        VALUES ('$next_id', '$id_user', '$tgl', '$total_bayar', '$diskon')");

    if (!$query_insert_jual) {
        echo "<script>alert('Gagal menyimpan data penjualan!'); window.location='cart.php';</script>";
        exit;
    }

    foreach ($cart_items as $item) {
        $total = $item['qty'] * $item['harga'];
        $query_dtl = mysqli_query($koneksi, "INSERT INTO tb_jualdtl (id_jual, id_produk, qty, harga) 
            VALUES ('$next_id', '{$item['id_produk']}', '{$item['qty']}', '$total')");

        if (!$query_dtl) {
            echo "<script>alert('Gagal menyimpan detail penjualan!'); window.location='cart.php';</script>";
            exit;
        }
    }

    $hapus = mysqli_query($koneksi, "DELETE FROM tb_pesanan WHERE id_user = '$id_user'");
    if (!$hapus) {
        echo "<script>alert('Gagal menghapus keranjang!'); window.location='cart.php';</script>";
        exit;
    }

    echo "<script>alert('Checkout berhasil!'); window.location='cart.php';</script>";
    exit;
}

// Proses update cart
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $id_pesanan => $new_qty) {
        $id_pesanan = mysqli_real_escape_string($koneksi, $id_pesanan);
        $new_qty = (int)$new_qty;
        if ($new_qty < 1) $new_qty = 1;

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
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Belanja - ToBag</title>
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
    <style>
        .nice-select .list { max-height: 300px !important; overflow-y: auto !important; }
        .cart-plus-minus-box { width: 60px; text-align: center; }
        .loading { display: none; color: #ff0000; font-size: 12px; }
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
                                <a href="index.php"><h1>ToBag</h1></a>
                            </div>
                        </div>
                        <div class="col-lg-9 pl-0 ml-sm-15 ml-xs-15">
                            <div class="header-middle-right">
                                <ul class="hm-menu">
                                    <?php if (!isset($_SESSION['id_user'])) { ?>
                                        <li class="hm-wishlist">
                                            <a href="login.php" title="Login"><i class="fa fa-user-circle-o"></i></a>
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
                                                            <a href="single-product.php?id=<?= $item['id_produk'] ?>" class="minicart-product-image">
                                                                <img src="admin/produk_img/<?= $item['gambar'] ?>" alt="<?= htmlspecialchars($item['nm_produk']) ?>">
                                                            </a>
                                                            <div class="minicart-product-details">
                                                                <h6><a href="single-product.php?id=<?= $item['id_produk'] ?>"><?= htmlspecialchars($item['nm_produk']) ?></a></h6>
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
                                                    <a href="checkout.php" class="li-button li-button-fullwidth li-button-sm">
                                                        <span>Checkout</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                    <?php } ?>
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
                        <li class="active">Keranjang</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="Shopping-cart-area pt-60 pb-60">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <form method="post" action="">
                            <div class="table-content table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="li-product-remove">Hapus</th>
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
                                            echo "<tr><td colspan='6'>Keranjang kosong.</td></tr>";
                                        } else {
                                            foreach ($cart_items as $row) {
                                                $subtotal_item = $row['qty'] * $row['harga'];
                                                ?>
                                                <tr>
                                                    <td class="li-product-remove">
                                                        <button class="delete-item" data-id="<?= $row['id_pesanan'] ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                    <td class="li-product-thumbnail">
                                                        <a href="single-product.php?id=<?= $row['id_produk'] ?>">
                                                            <img src="admin/produk_img/<?= $row['gambar'] ?>" alt="<?= htmlspecialchars($row['nm_produk']) ?>" width="70">
                                                        </a>
                                                    </td>
                                                    <td class="li-product-name">
                                                        <a href="single-product.php?id=<?= $row['id_produk'] ?>"><?= htmlspecialchars($row['nm_produk']) ?></a>
                                                    </td>
                                                    <td class="li-product-price">
                                                        <span class="amount">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                                                    </td>
                                                    <td class="quantity">
                                                        <label>Quantity</label>
                                                        <div class="cart-plus-minus">
                                                            <input class="cart-plus-minus-box qty-input" name="qty[<?= $row['id_pesanan'] ?>]" value="<?= $row['qty'] ?>" type="number" min="1" data-id="<?= $row['id_pesanan'] ?>">
                                                        </div>
                                                        <span class="loading">Memproses...</span>
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
                                <div class="col-12">
                                    <div class="coupon-all">
                                        <div class="coupon2">
                                            <input class="button" name="update_cart" value="Update cart" type="submit">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5 ml-auto">
                                    <div class="cart-page-total">
                                        <h2>Total Pesanan</h2>
                                        <ul>
                                            <li>Subtotal <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span></li>
                                            <li>Diskon <span>Rp <?= number_format($diskon, 0, ',', '.') ?></span></li>
                                            <li>Total <span>Rp <?= number_format($total_bayar, 0, ',', '.') ?></span></li>
                                        </ul>
                                        <button type="submit" name="checkout" class="btn btn-dark mt-3">Checkout</button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
                                            <a href="https://twitter.com/" data-toggle="tooltip" target="_blank" title="Twitter">
                                                <i class="fa fa-twitter"></i>
                                            </a>
                                        </li>
                                        <li class="facebook">
                                            <a href="https://www.facebook.com/" data-toggle="tooltip" target="_blank" title="Facebook">
                                                <i class="fa fa-facebook"></i>
                                            </a>
                                        </li>
                                        <li class="youtube">
                                            <a href="https://www.youtube.com/" data-toggle="tooltip" target="_blank" title="Youtube">
                                                <i class="fa fa-youtube"></i>
                                            </a>
                                        </li>
                                        <li class="instagram">
                                            <a href="https://www.instagram.com/" data-toggle="tooltip" target="_blank" title="Instagram">
                                                <i class="fa fa-instagram"></i>
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
</html>