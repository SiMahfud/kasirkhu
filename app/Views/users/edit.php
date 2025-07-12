<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><?= esc($title ?? 'Edit User') ?></h4>
                </div>
                <div class="card-body">
                    <?php if (session()->has('errors')) : ?>
                        <ul class="alert alert-danger">
                        <?php foreach (session('errors') as $error) : ?>
                            <li><?= $error ?></li>
                        <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                    <?php if (session()->has('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif ?>

                    <?= form_open('admin/users/update/' . $user->id) ?>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= old('name', esc($user->name)) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?= old('username', esc($user->username)) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= old('email', esc($user->getIdentity('email')->secret ?? '')) ?>" required>
                        </div>

                        <hr>
                        <p class="text-muted">Leave password fields blank to keep the current password.</p>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password (Optional)</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted">Minimum 8 characters if changing.</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                        </div>
                        <hr>

                        <div class="mb-3">
                            <label for="group" class="form-label">Group</label>
                            <select class="form-select" id="group" name="group" required>
                                <?php
                                // $userGroups is passed by controller, containing names of current user's groups
                                // $available_groups is passed by controller, e.g., ['admin', 'cashier']
                                $selectedGroup = old('group', $userGroups[0] ?? ''); // Default to first group or empty
                                foreach ($available_groups as $group_option): ?>
                                <option value="<?= esc($group_option) ?>" <?= $selectedGroup == $group_option ? 'selected' : '' ?>>
                                    <?= esc(ucfirst($group_option)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update User</button>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
