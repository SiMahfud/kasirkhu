<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4><?= esc($title ?? 'Store Settings') ?></h4>
                </div>
                <div class="card-body">
                    <?php if (session()->has('message')) : ?>
                        <div class="alert alert-success">
                            <?= session('message') ?>
                        </div>
                    <?php endif ?>
                    <?php if (session()->has('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif ?>
                    <?php if (session()->has('errors')) : ?>
                        <ul class="alert alert-danger">
                        <?php foreach (session('errors') as $error) : ?>
                            <li><?= $error ?></li>
                        <?php endforeach ?>
                        </ul>
                    <?php endif ?>

                    <?= form_open('admin/settings/update') ?>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="store_name" class="form-label">Store Name</label>
                            <input type="text" class="form-control" id="store_name" name="store_name"
                                   value="<?= esc(old('store_name', $settings['store_name'] ?? '')) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="store_address" class="form-label">Store Address</label>
                            <textarea class="form-control" id="store_address" name="store_address" rows="3"><?= esc(old('store_address', $settings['store_address'] ?? '')) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="store_phone" class="form-label">Store Phone</label>
                            <input type="text" class="form-control" id="store_phone" name="store_phone"
                                   value="<?= esc(old('store_phone', $settings['store_phone'] ?? '')) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="receipt_footer_message" class="form-label">Receipt Footer Message</label>
                            <textarea class="form-control" id="receipt_footer_message" name="receipt_footer_message" rows="3"><?= esc(old('receipt_footer_message', $settings['receipt_footer_message'] ?? '')) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                        <a href="<?= base_url('/') ?>" class="btn btn-secondary">Cancel</a>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
