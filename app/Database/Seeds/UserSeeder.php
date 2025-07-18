<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Dapatkan provider pengguna (UserModel)
        $users = auth()->getProvider();

        // --- Data untuk Super Admin ---
        $superadmin = new User([
            'username' => 'superadmin',
            'email'    => 'superadmin@example.com',
            'password' => 'password123',
            'name'     => 'Super Admin',
            'role'     => 'superadmin'
        ]);
        $users->save($superadmin);

        // Ambil kembali data pengguna untuk mendapatkan ID dan objek utuh
        $superadmin = $users->findById($users->getInsertID());

        // Tambahkan pengguna ke grup 'superadmin'
        $superadmin->addGroup('superadmin');


        // --- Data untuk Admin ---
        $admin = new User([
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => 'password123',
            'name'     => 'Admin',
            'role'     => 'admin'
        ]);
        $users->save($admin);
        $admin = $users->findById($users->getInsertID());
        
        // Tambahkan pengguna ke grup 'admin'
        $admin->addGroup('admin');


        // --- Data untuk User Biasa ---
        $user = new User([
            'username' => 'regularuser',
            'email'    => 'user@example.com',
            'password' => 'password123',
            'name'     => 'Regular User',
            'role'     => 'user'
        ]);
        $users->save($user);
        $user = $users->findById($users->getInsertID());

        // **[PERBAIKAN]** Tambahkan pengguna ke grup default secara eksplisit
        // 1. Ambil nama grup default dari config
        $defaultGroup = config('AuthGroups')->defaultGroup;

        // 2. Gunakan metode addGroup() dengan nama grup tersebut
        $user->addGroup($defaultGroup);
    }
}