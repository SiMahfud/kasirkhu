<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-6">
        <h2><?= esc($title ?? 'Daftar Produk') ?></h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= site_url('products/new') ?>" class="btn btn-primary btn-sm mb-2">Tambah Produk</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6 offset-md-6">
        <form action="<?= site_url('products') ?>" method="get" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari produk/kode/kategori..." value="<?= esc($searchTerm ?? '') ?>">
            <button type="submit" class="btn btn-outline-secondary">Cari</button>
        </form>
    </div>
</div>


<?php if (!empty($products) && is_array($products)): ?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Unit</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= esc($product->id) ?></td>
                <td><?= esc($product->code ?? '-') ?></td>
                <td><?= esc($product->name) ?></td>
                <td><?= esc($product->category_name ?? '-') ?></td> <!-- Dari join di model -->
                <td>Rp <?= number_format($product->price ?? 0, 2, ',', '.') ?></td>
                <td><?= esc($product->stock ?? 0) ?></td>
                <td><?= esc($product->unit ?? '-') ?></td>
                <td>
                    <a href="<?= site_url('products/' . $product->id) ?>" class="btn btn-info btn-sm">Lihat</a>
                    <a href="<?= site_url('products/' . $product->id . '/edit') ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form action="<?= site_url('products/' . $product->id) ?>" method="post" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                        <input type="hidden" name="_method" value="DELETE">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= $pager->links() ?>

<?php else: ?>
    <div class="alert alert-info mt-3">
        <?php if (!empty($searchTerm)): ?>
            Tidak ada produk yang cocok dengan pencarian "<?= esc($searchTerm) ?>".
        <?php else: ?>
            Belum ada data produk. Silakan <a href="<?= site_url('products/new') ?>">tambah produk baru</a>.
        <?php endif; ?>
    </div>
<?php endif ?>

<?= $this->endSection() ?>
