<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TransactionDetail extends Entity
{
    protected $datamap = [];
    protected $dates   = [
        'created_at',
        'updated_at',
    ]; // No deleted_at for transaction details
    protected $casts   = [
        'id'             => 'integer',
        'transaction_id' => 'integer',
        'product_id'     => 'integer',
        'quantity'       => 'integer',
        // price_per_unit, subtotal will be handled by getters
    ];

    public function getPricePerUnit(): float
    {
        return (float) ($this->attributes['price_per_unit'] ?? 0.0);
    }

    public function getSubtotal(): float
    {
        return (float) ($this->attributes['subtotal'] ?? 0.0);
    }
}
