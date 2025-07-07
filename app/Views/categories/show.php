<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<h2><?= esc($title ?? 'Detail Kategori') ?></h2>

<div class="card">
    <div class="card-header">
        <h4>ID Kategori: <?= esc($category->id) ?></h4>
    </div>
    <div class="card-body">
        <h5 class="card-title">Nama Kategori: <?= esc($category->name) ?></h5>
        <p class="card-text"><strong>Deskripsi:</strong> <?= nl2br(esc($category->description ?? 'Tidak ada deskripsi.')) ?></p>
        <p class="card-text"><small class="text-muted">Dibuat pada: <?= esc($category->created_at) ?></small></p>
        <p class="card-text"><small class="text-muted">Diperbarui pada: <?= esc($category->updated_at) ?></small></p>
    </div>
    <div class="card-footer">
        <a href="<?= site_url('categories/' . $category->id . '/edit') ?>" class="btn btn-warning">Edit</a>
        <a href="<?= site_url('categories') ?>" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>
</div>

<?= $this->endSection() ?>
