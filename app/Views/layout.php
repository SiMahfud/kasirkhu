<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Toko Khumaira') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Custom CSS bisa ditambahkan di sini -->
    <style>
        body { padding-top: 56px; /* Adjusted for fixed navbar */ }
        .container { margin-top: 20px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= site_url('/') ?>">Toko Khumaira</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('categories') ?>">Kategori</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('products') ?>">Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('products/stock') ?>">Laporan Stok</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('transactions') ?>">Transaksi</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReports" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Laporan
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownReports">
                        <li><a class="dropdown-item" href="<?= site_url('reports/sales/daily') ?>">Penjualan Harian</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('reports/sales/top-products') ?>">Produk Terlaris</a></li>
                        <!-- <li><hr class="dropdown-divider"></li> -->
                        <!-- <li><a class="dropdown-item" href="#">Laporan Lain</a></li> -->
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (session()->get('isLoggedIn')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('logout') ?>">Logout (<?= session()->get('username') ?>)</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('login') ?>">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): // Untuk error validasi ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <p><strong>Terjadi kesalahan:</strong></p>
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</div>

<footer class="py-4 bg-light mt-auto">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Hak Cipta &copy; Toko Khumaira <?= date('Y') ?></div>
            <div>
                Rendered in {elapsed_time} seconds. Environment: <?= ENVIRONMENT ?>.
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<!-- Custom JS bisa ditambahkan di sini -->
</body>
</html>
