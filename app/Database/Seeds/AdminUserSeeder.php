<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // $userModel = new \App\Models\UserModel(); // Dihapus karena UserModel belum tentu ada/dibutuhkan di sini

        // Cek apakah UserModel sudah ada. Jika belum, kita bisa menggunakan query builder.
        // Untuk sekarang, asumsikan UserModel akan dibuat atau sudah ada.
        // Jika tidak, kita pakai DB Query Builder: $this->db->table('users')->insert($data);

        $adminData = [
            'name'     => 'Administrator',
            'username' => 'admin',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role'     => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Cek apakah user admin sudah ada
        $existingAdmin = $this->db->table('users')->where('username', 'admin')->get()->getRow();

        if (!$existingAdmin) {
            $this->db->table('users')->insert($adminData);
            // echo "Admin user created.\n"; // Dihapus agar tidak mengganggu output tes
        } else {
            // echo "Admin user already exists.\n"; // Dihapus
        }
    }
}
