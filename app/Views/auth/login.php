<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><?= esc($title ?? 'Login') ?></h4>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('errors')): // Untuk error validasi form ?>
                    <div class="alert alert-danger">
                        <p class="mb-1"><strong>Terjadi kesalahan input:</strong></p>
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>
                 <?php if (session()->getFlashdata('message')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
                <?php endif; ?>

                <form action="<?= site_url('login') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control <?= (session('errors.username')) ? 'is-invalid' : '' ?>"
                               id="username" name="username" value="<?= old('username') ?>" required>
                        <?php if (session('errors.username')): ?>
                            <div class="invalid-feedback"><?= esc(session('errors.username')) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control <?= (session('errors.password')) ? 'is-invalid' : '' ?>"
                               id="password" name="password" required>
                        <?php if (session('errors.password')): ?>
                            <div class="invalid-feedback"><?= esc(session('errors.password')) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <p>User admin default: admin / password123</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?php $this->section('styles') ?>
<style>
    body {
        /* Optional: background color for login page */
        /* background-color: #f8f9fa; */
    }
    .card {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
</style>
<?php $this->endSection() ?>
