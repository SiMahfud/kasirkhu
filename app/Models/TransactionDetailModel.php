<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionDetailModel extends Model
{
    protected $table            = 'transaction_details'; // Corrected table name
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\TransactionDetail'; // Assuming an Entity will be created later
    protected $useSoftDeletes   = false; // No soft deletes in migration for details
    protected $protectFields    = true;
    protected $allowedFields    = [
        'transaction_id',
        'product_id',
        'quantity',
        'price_per_unit',
        'subtotal',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'quantity'       => 'integer',
        'price_per_unit' => 'float',
        'subtotal'       => 'float',
        'transaction_id' => 'integer',
        'product_id'     => 'integer',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true; // Enabled timestamps as per migration
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Not used as useSoftDeletes is false

    // Validation
    protected $validationRules      = [
        'transaction_id' => 'required|integer',
        'product_id'     => 'required|integer',
        'quantity'       => 'required|integer|greater_than[0]',
        'price_per_unit' => 'required|numeric', // Using 'numeric' for broader compatibility, can be 'float'
        'subtotal'       => 'required|numeric', // Using 'numeric'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
