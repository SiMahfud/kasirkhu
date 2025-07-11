<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-boxes"></i> <?= esc($title) ?></h2>
        <!-- Optional: Add button to go to product list or add new product -->
        <a href="<?= site_url('products') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-list"></i> Daftar Produk</a>
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

    <!-- Search Form -->
    <form method="get" action="<?= site_url('products/stock') ?>" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, kode, atau kategori produk..." value="<?= esc($searchTerm ?? '') ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i> Cari</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No.</th>
                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="text-center">Stok Saat Ini</th>
                    <th>Unit</th>
                    <th class="text-center">Aksi Penyesuaian</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php $no = ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1; ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($product->code ?: '-') ?></td>
                            <td><?= esc($product->name) ?></td>
                            <td><?= esc($product->category_name ?: '-') ?></td>
                            <td class="text-center fw-bold
                                <?php
                                if ($product->stock === null) echo 'text-muted';
                                elseif ($product->stock == 0) echo 'text-danger';
                                elseif ($product->stock > 0 && $product->stock <= 10) echo 'text-warning'; // Example threshold for low stock
                                else echo 'text-success';
                                ?>">
                                <?= $product->stock !== null ? esc($product->stock) : 'N/A' ?>
                            </td>
                            <td><?= esc($product->unit ?: '-') ?></td>
                            <td class="text-center">
                                <?php if ($product->stock !== null): // Only allow adjustment if stock is a concept for this product ?>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#adjustStockModal-<?= $product->id ?>">
                                    <i class="fas fa-edit"></i> Sesuaikan
                                </button>

                                <!-- Modal for Stock Adjustment -->
                                <div class="modal fade" id="adjustStockModal-<?= $product->id ?>" tabindex="-1" aria-labelledby="adjustStockModalLabel-<?= $product->id ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <?= form_open('products/adjust-stock/' . $product->id) ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="adjustStockModalLabel-<?= $product->id ?>">Sesuaikan Stok: <?= esc($product->name) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p>Stok Saat Ini: <strong><?= esc($product->stock) ?> <?= esc($product->unit) ?></strong></p>
                                                <div class="mb-3">
                                                    <label for="adjustment_type-<?= $product->id ?>" class="form-label">Jenis Penyesuaian:</label>
                                                    <select name="adjustment_type" id="adjustment_type-<?= $product->id ?>" class="form-select" required>
                                                        <option value="add">Tambah Stok</option>
                                                        <option value="subtract">Kurangi Stok</option>
                                                        <option value="set">Atur Stok ke Jumlah Baru</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="quantity-<?= $product->id ?>" class="form-label">Jumlah Penyesuaian:</label>
                                                    <input type="number" name="quantity" id="quantity-<?= $product->id ?>" class="form-control" required min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="notes-<?= $product->id ?>" class="form-label">Catatan (Opsional):</label>
                                                    <textarea name="notes" id="notes-<?= $product->id ?>" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                                            </div>
                                            <?= form_close() ?>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Tidak dikelola</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada produk yang cocok dengan pencarian atau belum ada produk yang stoknya dikelola.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager): ?>
        <?= $pager->links('default', 'bootstrap_custom') ?>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Optional: if you need any JS for the modals, like focusing first input
    <?php if (!empty($products)): ?>
    <?php foreach ($products as $product): ?>
    var adjustStockModal<?= $product->id ?> = document.getElementById('adjustStockModal-<?= $product->id ?>');
    if (adjustStockModal<?= $product->id ?>) {
        adjustStockModal<?= $product->id ?>.addEventListener('shown.bs.modal', function () {
            var quantityInput = document.getElementById('quantity-<?= $product->id ?>');
            if (quantityInput) {
                quantityInput.focus();
            }
        });
    }
    <?php endforeach; ?>
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
