<?php

namespace App\Models;

// Ganti 'use CodeIgniter\Model;' dengan use dari UserModel Shield
// use CodeIgniter\Model;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';

    // Izinkan kolom-kolom ini untuk diisi melalui 'save', 'insert', atau 'update'
    // Sertakan kolom bawaan Shield DAN kolom kustom Anda
    protected $allowedFields  = [
        'username',
        'status',
        'status_message',
        'active',
        'last_active',
        'deleted_at',
        // Kolom kustom Anda
        'nama',
        'role',
    ];

    // Tipe data untuk kolom, berguna untuk casting otomatis
    protected $returnType     = 'CodeIgniter\Shield\Entities\User';
    protected $useTimestamps    = true;
    protected $skipValidation   = false;
    
    // Anda bisa menambahkan fungsi kustom di sini
    // Contoh: fungsi untuk mencari semua admin
    public function findAllAdmins()
    {
        return $this->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                    ->where('auth_groups_users.group', 'admin')
                    ->findAll();
    }
}