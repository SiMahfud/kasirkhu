<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'username'
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'cashier',
                'after'      => 'email'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['name', 'role']);
    }
}
