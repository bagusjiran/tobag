<?php
include 'admin/koneksi.php'; // koneksi ke database

// Validasi apakah parameter 'id' ada dalam URL
if (!isset($_GET['id'])) {
    echo "<script>alert('Produk tidak ditemukan'); window.location.href = 'belanja.php';</script>";
    exit;
}

$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT p.*, k.nm_kategori FROM tb_produk p LEFT JOIN tb_kategori k ON p.id_kategori = k.id_kategori WHERE p.id_produk='$id'");
$data = mysqli_fetch_assoc($query);

// Validasi apakah data produk ditemukan
if (!$data) {
    echo "<script>alert('Data produk tidak ditemukan'); window.location.href = 'belanja.php';</script>";
    exit;
}
?>

<div class="content-wraper">
    <div class="container">
        <div class="row single-product-area">
            <div class="col-lg-5 col-md-6">
                <!-- Product Details Left -->
                <div class="product-details-left">
                    <div class="product-details-images slider-navigation-1">
                        <div class="lg-image">
                            <a class="popup-img venobox vbox-item" href="admin/produk_img/<?= $data['gambar'] ?>" data-gall="myGallery">
                                <img src="admin/produk_img/<?= $data['gambar'] ?>" alt="<?= $data['nm_produk'] ?>" width="300" height="300">
                            </a>
                        </div>
                    </div>
                </div>
                <!--// Product Details Left -->
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
                            <p>
                                <span><?= nl2br($data['desk']) ?></span>
                            </p>
                            <p><strong>Stok tersedia:</strong> <?= $data['stok'] ?> unit</p>
                        </div>

                        <div class="single-add-to-cart">
                            <form action="tambah_ke_keranjang.php" method="POST" class="cart-quantity">
                                <input type="hidden" name="id_produk" value="<?= $data['id_produk'] ?>">
                                <input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?? '' ?>">
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
<!-- Produk lainnya -->
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
                        $query_produk_lain = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id_produk != '$id' ORDER BY RAND() LIMIT 6");
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
                                                    <a href="#">Kategori: <?= $p['id_kategori'] ?></a>
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
                                                <li><a href="detail_produk.php?id=<?= $p['id_produk'] ?>" title="Quick View" class="quick-view-btn"><i class="fa fa-eye"></i></a></li>
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
