<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Transaction extends Entity
{
    protected $datamap = [];
    protected $dates   = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $casts   = [
        'id'             => 'integer',
        'user_id'        => 'integer',
        // total_amount, discount, final_amount will be handled by getters
    ];

    public function getTotalAmount(): float
    {
        return (float) ($this->attributes['total_amount'] ?? 0.0);
    }

    public function getDiscount(): float
    {
        return (float) ($this->attributes['discount'] ?? 0.0);
    }

    public function getFinalAmount(): float
    {
        return (float) ($this->attributes['final_amount'] ?? 0.0);
    }

    // If you need setters that also ensure float type, you can add them:
    // public function setTotalAmount($value): static
    // {
    //     $this->attributes['total_amount'] = (float) $value;
    //     return $this;
    // }
}
