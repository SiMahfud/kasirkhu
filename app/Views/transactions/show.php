<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-file-invoice-dollar"></i> Detail Transaksi #<?= esc($transaction->transaction_code) ?></h2>
        <div>
            <a href="<?= site_url('transactions/new') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Buat Baru</a>
            <a href="<?= site_url('transactions') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-list"></i> Riwayat Transaksi</a>
            <!-- TODO: Add Print Struk Button here later -->
        </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <strong>Kode Transaksi:</strong> <?= esc($transaction->transaction_code) ?><br>
                    <strong>Tanggal:</strong> <?= esc(strftime('%d %B %Y %H:%M:%S', strtotime($transaction->created_at))) ?><br>
                    <strong>Kasir:</strong>
                    <?php
                        // Assuming user_id is present and we can fetch user name
                        // For now, just user_id. In a real app, join or load user model.
                        $userModel = new \App\Models\UserModel(); // Quick way for now
                        $kasir = $userModel->find($transaction->user_id);
                        echo esc($kasir->name ?? $transaction->user_id);
                    ?><br>
                </div>
                <div class="col-md-6 text-md-end">
                    <strong>Nama Pelanggan:</strong> <?= esc($transaction->customer_name ?: '-') ?><br>
                    <strong>Metode Pembayaran:</strong> <?= esc(ucfirst($transaction->payment_method ?: '-')) ?><br>
                </div>
            </div>
        </div>
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-shopping-cart"></i> Item Transaksi</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Kode Produk</th>
                            <th>Nama Produk/Layanan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-center">Kuantitas</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($details)): ?>
                            <?php $no = 1; foreach ($details as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($item->product_code) ?></td>
                                    <td><?= esc($item->product_name) ?></td>
                                    <td class="text-end">Rp <?= number_format($item->price_per_unit, 0, ',', '.') ?></td>
                                    <td class="text-center"><?= esc($item->quantity) ?></td>
                                    <td class="text-end">Rp <?= number_format($item->subtotal, 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada item detail untuk transaksi ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="4"></td>
                            <td class="text-end">Total Harga</td>
                            <td class="text-end">Rp <?= number_format($transaction->total_amount, 0, ',', '.') ?></td>
                        </tr>
                        <?php if ($transaction->discount > 0): ?>
                        <tr>
                            <td colspan="4"></td>
                            <td class="text-end">Diskon</td>
                            <td class="text-end">Rp <?= number_format($transaction->discount, 0, ',', '.') ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="4"></td>
                            <td class="text-end fs-5">Grand Total</td>
                            <td class="text-end fs-5">Rp <?= number_format($transaction->final_amount, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicatat pada: <?= esc(strftime('%d %B %Y, %H:%M:%S', strtotime($transaction->created_at))) ?>
            <?php if ($transaction->updated_at && $transaction->updated_at != $transaction->created_at): ?>
                | Terakhir diubah: <?= esc(strftime('%d %B %Y, %H:%M:%S', strtotime($transaction->updated_at))) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <!-- Delete button with confirmation -->
        <?= form_open('transactions/delete/' . $transaction->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Ini akan menjadi soft delete.');"]) ?>
            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> Hapus Transaksi</button>
        <?= form_close() ?>
    </div>

</div>
<?= $this->endSection() ?>
