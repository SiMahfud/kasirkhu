<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-award"></i> <?= esc($title) ?></h2>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('message')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="get" action="<?= site_url('reports/sales/top-products') ?>" class="mb-4 p-3 border rounded bg-light">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="from_date" class="form-label">Dari Tanggal:</label>
                <input type="date" name="from_date" id="from_date" class="form-control" value="<?= esc($fromDate ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label for="to_date" class="form-label">Sampai Tanggal:</label>
                <input type="date" name="to_date" id="to_date" class="form-control" value="<?= esc($toDate ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <label for="limit" class="form-label">Jumlah Top:</label>
                <input type="number" name="limit" id="limit" class="form-control" value="<?= esc($limit ?? 10) ?>" min="1" max="100">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </div>
    </form>

    <?php if (!empty($topProducts)): ?>
        <p class="text-muted">Menampilkan <?= count($topProducts) ?> produk terlaris dari periode <?= esc(date('d M Y', strtotime($fromDate))) ?> s/d <?= esc(date('d M Y', strtotime($toDate))) ?>.</p>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Total Kuantitas Terjual</th>
                        <th class="text-end">Total Pendapatan dari Produk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($topProducts as $product): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($product->product_code ?: '-') ?></td>
                            <td><?= esc($product->product_name) ?></td>
                            <td class="text-center fw-bold"><?= esc($product->total_quantity_sold) ?></td>
                            <td class="text-end">Rp <?= number_format($product->total_revenue, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Tidak ada data produk terjual untuk periode atau filter yang dipilih.</div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
