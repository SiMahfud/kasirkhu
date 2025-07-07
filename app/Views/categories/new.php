<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<h2><?= esc($title ?? 'Tambah Kategori Baru') ?></h2>

<form action="<?= site_url('categories') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="name" class="form-label">Nama Kategori</label>
        <input type="text" class="form-control <?= (isset(session()->getFlashdata('errors')['name'])) ? 'is-invalid' : '' ?>"
               id="name" name="name" value="<?= old('name') ?>" required>
        <?php if (isset(session()->getFlashdata('errors')['name'])): ?>
            <div class="invalid-feedback">
                <?= esc(session()->getFlashdata('errors')['name']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi (Opsional)</label>
        <textarea class="form-control <?= (isset(session()->getFlashdata('errors')['description'])) ? 'is-invalid' : '' ?>"
                  id="description" name="description" rows="3"><?= old('description') ?></textarea>
        <?php if (isset(session()->getFlashdata('errors')['description'])): ?>
            <div class="invalid-feedback">
                <?= esc(session()->getFlashdata('errors')['description']) ?>
            </div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= site_url('categories') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>
