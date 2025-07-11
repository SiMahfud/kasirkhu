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
        'total_amount'   => 'decimal',
        'discount'       => 'decimal',
        'final_amount'   => 'decimal',
    ];
}
