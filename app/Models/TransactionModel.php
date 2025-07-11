<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\Transaction'; // Assuming an Entity will be created later
    protected $useSoftDeletes   = true; // Enabled soft deletes as per migration
    protected $protectFields    = true;
    protected $allowedFields    = [
        'transaction_code',
        'user_id',
        'customer_name',
        'total_amount',
        'discount',
        'final_amount',
        'payment_method',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'user_id'      => 'integer',
        // Amount fields removed from casts
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true; // Enabled timestamps
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'transaction_code' => 'permit_empty|is_unique[transactions.transaction_code,id,{id}]',
        'user_id'          => 'required|integer',
        'total_amount'     => 'required|numeric',
        'final_amount'     => 'required|numeric',
        'discount'         => 'permit_empty|numeric',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateTransactionCode'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Generates a unique transaction code before insert.
     *
     * @param array $data
     * @return array
     */
    protected function generateTransactionCode(array $data): array
    {
        if (!isset($data['data']['transaction_code']) || empty($data['data']['transaction_code'])) {
            // Example: INV-YYYYMMDD-XXXX (XXXX is a random number or sequence)
            $datePart = date('Ymd');
            $randomPart = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4)); // Simple random part
            $data['data']['transaction_code'] = 'INV-' . $datePart . '-' . $randomPart;

            // Ensure it's unique, loop if somehow it's not (highly unlikely with this format)
            while ($this->where('transaction_code', $data['data']['transaction_code'])->countAllResults() > 0) {
                $randomPart = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
                $data['data']['transaction_code'] = 'INV-' . $datePart . '-' . $randomPart;
            }
        }
        return $data;
    }
}
