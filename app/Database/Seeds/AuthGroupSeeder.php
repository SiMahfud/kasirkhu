<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuthGroupSeeder extends Seeder
{
    public function run()
    {
        $groups = service('authorization');

        // Membuat Grup
        $groups->createGroup('superadmin', 'Site overlords');
        $groups->createGroup('admin', 'Site administrators');
        $groups->createGroup('cashier', 'Cashier users');
        $groups->createGroup('user', 'Regular users');

        // Membuat Izin (Permissions)
        // Format: $groups->addPermissionToGroup('permission.name', 'group.name');

        // Izin untuk Superadmin (memiliki semua izin)
        $groups->addPermissionToGroup('admin.access', 'superadmin');
        $groups->addPermissionToGroup('admin.settings', 'superadmin');
        $groups->addPermissionToGroup('users.create', 'superadmin');
        $groups->addPermissionToGroup('users.edit', 'superadmin');
        $groups->addPermissionToGroup('users.delete', 'superadmin');
        $groups->addPermissionToGroup('beta.access', 'superadmin');

        // Izin untuk Admin
        $groups->addPermissionToGroup('admin.access', 'admin');
        $groups->addPermissionToGroup('users.create', 'admin');
        $groups->addPermissionToGroup('users.edit', 'admin');

        // Izin untuk User (contoh)
        // Tidak ada izin admin, hanya izin umum jika ada
    }
}