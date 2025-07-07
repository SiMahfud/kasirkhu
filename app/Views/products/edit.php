<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<h2><?= esc($title ?? 'Edit Produk') ?></h2>

<form action="<?= site_url('products/' . $product->id) ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">

    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Produk*</label>
                <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                       id="name" name="name" value="<?= old('name', $product->name) ?>" required>
                <?php if (session('errors.name')): ?>
                    <div class="invalid-feedback"><?= esc(session('errors.name')) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="code" class="form-label">Kode Produk (Opsional)</label>
                <input type="text" class="form-control <?= session('errors.code') ? 'is-invalid' : '' ?>"
                       id="code" name="code" value="<?= old('code', $product->code ?? '') ?>">
                <?php if (session('errors.code')): ?>
                    <div class="invalid-feedback"><?= esc(session('errors.code')) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori*</label>
                <select class="form-select <?= session('errors.category_id') ? 'is-invalid' : '' ?>"
                        id="category_id" name="category_id" required>
                    <option value="">Pilih Kategori...</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= esc($category->id) ?>" <?= old('category_id', $product->category_id) == $category->id ? 'selected' : '' ?>>
                            <?= esc($category->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.category_id')): ?>
                    <div class="invalid-feedback"><?= esc(session('errors.category_id')) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="price" class="form-label">Harga Jual*</label>
                <input type="number" step="0.01" class="form-control <?= session('errors.price') ? 'is-invalid' : '' ?>"
                       id="price" name="price" value="<?= old('price', $product->price) ?>" required>
                <?php if (session('errors.price')): ?>
                    <div class="invalid-feedback"><?= esc(session('errors.price')) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label for="stock" class="form-label">Stok (Opsional)</label>
                <input type="number" class="form-control <?= session('errors.stock') ? 'is-invalid' : '' ?>"
                       id="stock" name="stock" value="<?= old('stock', $product->stock ?? 0) ?>">
                <?php if (session('errors.stock')): ?>
                    <div class="invalid-feedback"><?= esc(session('errors.stock')) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-2">
             <div class="mb-3">
                <label for="unit" class="form-label">Unit (Opsional)</label>
                <input type="text" class="form-control <?= session('errors.unit') ? 'is-invalid' : '' ?>"
                       id="unit" name="unit" value="<?= old('unit', $product->unit ?? 'pcs') ?>" placeholder="pcs, kg, item">
                <?php if (session('errors.unit')): ?>
                    <div class="invalid-feedback"><?= esc(session('errors.unit')) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi (Opsional)</label>
        <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>"
                  id="description" name="description" rows="3"><?= old('description', $product->description ?? '') ?></textarea>
        <?php if (session('errors.description')): ?>
            <div class="invalid-feedback"><?= esc(session('errors.description')) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Update Produk</button>
    <a href="<?= site_url('products') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>
