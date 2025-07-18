<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameToUsersTable extends Migration
{
    public function up()
    {
        // Users Table
        $this->forge->addField([            
            // --- TAMBAHKAN KOLOM KUSTOM ANDA DI SINI ---
            'nama'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'role'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            // --- AKHIR DARI KOLOM KUSTOM ---
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['name', 'role']);
    }
}
