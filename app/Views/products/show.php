<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<h2><?= esc($title ?? 'Detail Produk') ?></h2>

<div class="card">
    <div class="card-header">
        <h4>ID Produk: <?= esc($product->id) ?></h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="card-title">Nama Produk: <?= esc($product->name) ?></h5>
                <p class="card-text"><strong>Kode Produk:</strong> <?= esc($product->code ?? '-') ?></p>
                <p class="card-text"><strong>Kategori:</strong> <?= esc($product->category_name ?? 'Tidak ada kategori') ?></p> <!-- category_name dari join -->
                <p class="card-text"><strong>Harga:</strong> Rp <?= number_format($product->price ?? 0, 2, ',', '.') ?></p>
                <p class="card-text"><strong>Stok:</strong> <?= esc($product->stock ?? 0) ?> <?= esc($product->unit ?? '') ?></p>
                <p class="card-text"><strong>Deskripsi:</strong></p>
                <p><?= nl2br(esc($product->description ?? 'Tidak ada deskripsi.')) ?></p>
            </div>
            <div class="col-md-4 text-md-end">
                <!-- Jika ada gambar produk, bisa ditampilkan di sini -->
                <!-- <img src="..." class="img-fluid" alt="Gambar Produk"> -->
            </div>
        </div>
    </div>
    <div class="card-footer">
        <p class="card-text"><small class="text-muted">Dibuat pada: <?= esc($product->created_at->humanize() ?? $product->created_at) ?></small></p>
        <p class="card-text"><small class="text-muted">Diperbarui pada: <?= esc($product->updated_at->humanize() ?? $product->updated_at) ?></small></p>
        <hr>
        <a href="<?= site_url('products/' . $product->id . '/edit') ?>" class="btn btn-warning">Edit</a>
        <a href="<?= site_url('products') ?>" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>
</div>

<?= $this->endSection() ?>
