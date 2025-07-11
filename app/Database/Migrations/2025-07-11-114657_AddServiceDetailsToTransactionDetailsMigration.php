<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServiceDetailsToTransactionDetailsMigration extends Migration
{
    public function up()
    {
        $fields = [
            'service_item_details' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'subtotal', // Place it after the subtotal column
                'comment' => 'JSON encoded details for service items, e.g., pages, paper type, custom price reason'
            ],
            // We might also want to store the originally selected product price vs the final calculated/manual price for services
            // For now, price_per_unit will store the final price used for calculation.
        ];
        $this->forge->addColumn('transaction_details', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaction_details', 'service_item_details');
    }
}
