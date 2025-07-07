<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-10">
        <h2><?= esc($title ?? 'Daftar Kategori') ?></h2>
    </div>
    <div class="col-md-2 text-end">
        <a href="<?= site_url('categories/new') ?>" class="btn btn-primary btn-sm">Tambah Kategori</a>
    </div>
</div>


<?php if (!empty($categories) && is_array($categories)): ?>
    <table class="table table-striped table-hover mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Kategori</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= esc($category->id) ?></td>
                <td><?= esc($category->name) ?></td>
                <td><?= esc($category->description ?? '-') ?></td>
                <td>
                    <a href="<?= site_url('categories/' . $category->id) ?>" class="btn btn-info btn-sm">Lihat</a>
                    <a href="<?= site_url('categories/' . $category->id . '/edit') ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form action="<?= site_url('categories/' . $category->id) ?>" method="post" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
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
        Belum ada data kategori. Silakan <a href="<?= site_url('categories/new') ?>">tambah kategori baru</a>.
    </div>
<?php endif ?>

<?= $this->endSection() ?>
