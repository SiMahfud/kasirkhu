<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= esc($title ?? 'User Management') ?></h4>
        <?php if (auth()->user() && auth()->user()->can('admin.users.create')): ?>
            <a href="<?= base_url('admin/users/new') ?>" class="btn btn-primary">Add New User</a>
        <?php endif ?>
    </div>

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

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)) : ?>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?= esc($user->id) ?></td>
                            <td><?= esc($user->name) ?></td>
                            <td><?= esc($user->username) ?></td>
                            <td><?= esc(ucfirst($user->role)) ?></td>
                            <td><?= esc($user->created_at) ?></td>
                            <td>
                                <?php if (auth()->user() && auth()->user()->can('admin.users.edit')): ?>
                                    <a href="<?= base_url('admin/users/edit/' . $user->id) ?>" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                <?php endif ?>
                                <?php if (auth()->user() && auth()->user()->can('admin.users.delete') && auth()->user()->id != $user->id): // Prevent delete button for self ?>
                                    <?= form_open('admin/users/delete/' . $user->id, ['class' => 'd-inline', 'onsubmit' => 'return confirm(\'Are you sure you want to delete this user? This action cannot be undone.\');']) ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    <?= form_close() ?>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="text-center">No users found.</td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager) : ?>
        <div class="mt-3">
            <?= $pager->links('default', 'bootstrap_custom') ?>
        </div>
    <?php endif ?>
</div>

<style>
    /* For Bootstrap Icons if not globally included */
    @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css");
</style>
<?= $this->endSection() ?>
