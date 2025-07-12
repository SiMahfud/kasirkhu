<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Dapatkan instance UserModel
        $users = model(UserModel::class);

        // --- Buat Super Admin ---
        $user = new User([
            'username' => 'superadmin',
            'active'   => 1, // Aktifkan user secara langsung
        ]);
        // Menambahkan identitas email & password
        $user->addEmailIdentity([
            'email'    => 'superadmin@example.com',
            'password' => 'musiku3377'
        ]);
        $users->save($user);

        // Untuk menambahkan ke grup, kita perlu mendapatkan user yang baru saja disimpan
        $user = $users->findById($users->getInsertID());
        // Menambahkan user ke grup 'superadmin' dan 'user'
        $user->addGroup('superadmin', 'user');

        // --- Buat Admin Biasa ---
        $user = new User([
            'username' => 'admin',
            'active'   => 1,
        ]);
        $user->addEmailIdentity([
            'email'    => 'admin@example.com',
            'password' => 'belajarlah'
        ]);
        $users->save($user);

        $user = $users->findById($users->getInsertID());
        $user->addGroup('admin', 'user');

        // --- Buat User Biasa ---
        $user = new User([
            'username' => 'dian',
            'active'   => 1,
        ]);
        $user->addEmailIdentity([
            'email'    => 'dian@example.com',
            'password' => '123456'
        ]);
        $users->save($user);

        $user = $users->findById($users->getInsertID());
        $user->addGroup('user');
    }
}