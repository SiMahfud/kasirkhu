<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-history"></i> Riwayat Transaksi</h2>
        <a href="<?= site_url('transactions/new') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Transaksi Baru</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Kode Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Total Akhir</th>
                    <th>Metode Pembayaran</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= esc($transaction->transaction_code) ?></td>
                            <td><?= esc($transaction->customer_name ?: '-') ?></td>
                            <td>Rp <?= number_format($transaction->final_amount, 0, ',', '.') ?></td>
                            <td><?= esc(ucfirst($transaction->payment_method ?: '-')) ?></td>
                            <td><?= esc(strftime('%d %b %Y %H:%M', strtotime($transaction->created_at))) ?></td>
                            <td>
                                <?php
                                // Assuming user_id in transaction links to users table, and we can fetch user name.
                                // This requires TransactionModel to have a method to fetch user or join,
                                // or fetching user names separately in controller. For simplicity, showing user_id for now.
                                echo esc($transaction->cashier_name ?? $transaction->user_id);
                                ?>
                            </td>
                            <td>
                                <a href="<?= site_url('transactions/' . $transaction->id) ?>" class="btn btn-sm btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                <!-- Delete button with confirmation -->
                                <?= form_open('transactions/delete/' . $transaction->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');"]) ?>
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                <?= form_close() ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Belum ada transaksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager): ?>
        <?= $pager->links() ?>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
