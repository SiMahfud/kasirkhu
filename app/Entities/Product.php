<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Product extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at']; // deleted_at tidak dipakai, tapi tidak masalah ada di sini
    protected $casts   = [
        'id'          => 'integer',
        'category_id' => 'integer',
        'price'       => 'float',
        'stock'       => 'integer',
    ];
}
