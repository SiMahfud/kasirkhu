<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-chart-line"></i> <?= esc($title) ?></h2>
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

    <!-- Date Filter Form -->
    <form method="get" action="<?= site_url('reports/sales/daily') ?>" class="mb-4 p-3 border rounded bg-light">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="from_date" class="form-label">Dari Tanggal:</label>
                <input type="date" name="from_date" id="from_date" class="form-control" value="<?= esc($fromDate ?? '') ?>" required>
            </div>
            <div class="col-md-5">
                <label for="to_date" class="form-label">Sampai Tanggal:</label>
                <input type="date" name="to_date" id="to_date" class="form-control" value="<?= esc($toDate ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </div>
    </form>

    <?php if (!empty($reportData)): ?>
        <div class="card mb-4">
            <div class="card-header fw-bold">
                Ringkasan Periode <?= esc(date('d M Y', strtotime($fromDate))) ?> s/d <?= esc(date('d M Y', strtotime($toDate))) ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="card-text">Total Transaksi: <strong class="fs-5"><?= esc($summary['total_transactions']) ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <p class="card-text">Total Penjualan: <strong class="fs-5">Rp <?= number_format($summary['total_sales'], 0, ',', '.') ?></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-center">Jumlah Transaksi</th>
                        <th class="text-end">Total Omset</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td><?= esc(date('d M Y', strtotime($row->transaction_date))) ?></td>
                            <td class="text-center"><?= esc($row->total_transactions) ?></td>
                            <td class="text-end">Rp <?= number_format($row->total_sales, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="fw-bold table-group-divider">
                     <tr>
                        <td class="text-end" colspan="2">Total Keseluruhan:</td>
                        <td class="text-end">Rp <?= number_format($summary['total_sales'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td class="text-end" colspan="2">Total Transaksi Keseluruhan:</td>
                        <td class="text-center"><?= esc($summary['total_transactions']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Tidak ada data penjualan untuk periode yang dipilih.</div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
