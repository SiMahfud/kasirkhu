<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\Product';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name', 'code', 'category_id', 'price', 'unit', 'description', 'stock'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        // 'price' => 'float', // Dihapus sementara karena masalah dengan integer dari DB
        'stock' => 'int'
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Tidak digunakan, tapi biarkan saja

    // Validation
    protected $validationRules      = [
        'name'        => 'required|min_length[3]|max_length[255]',
        'code'        => 'permit_empty|max_length[100]|is_unique[products.code,id,{id}]', // {id} untuk ignore self on update
        'category_id' => 'required|is_natural_no_zero|is_not_unique[categories.id]', // Memastikan category_id ada di tabel categories, kolom id
        'price'       => 'required|decimal|greater_than_equal_to[0]',
        'unit'        => 'permit_empty|max_length[50]',
        'description' => 'permit_empty|max_length[1000]',
        'stock'       => 'permit_empty|integer|greater_than_equal_to[0]',
    ];
    protected $validationMessages   = [
        'name' => [
            'required'   => 'Nama produk harus diisi.',
            'min_length' => 'Nama produk minimal 3 karakter.',
            'max_length' => 'Nama produk maksimal 255 karakter.',
        ],
        'code' => [
            'max_length' => 'Kode produk maksimal 100 karakter.',
            'is_unique'  => 'Kode produk sudah digunakan.',
        ],
        'category_id' => [
            'required'           => 'Kategori produk harus dipilih.',
            'is_natural_no_zero' => 'Kategori produk tidak valid.',
            'is_not_unique'      => 'Kategori yang dipilih tidak valid atau tidak ditemukan.'
        ],
        'price' => [
            'required'              => 'Harga produk harus diisi.',
            'decimal'               => 'Harga produk harus angka desimal.',
            'greater_than_equal_to' => 'Harga produk tidak boleh negatif.',
        ],
        'stock' => [
            'integer'               => 'Stok produk harus angka bulat.',
            'greater_than_equal_to' => 'Stok produk tidak boleh negatif.',
        ],
    ];
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

    public function getProductsWithCategoryDetails($searchTerm = null, $paginate = 10)
    {
        $builder = $this->select('products.*, categories.name as category_name')
                        ->join('categories', 'categories.id = products.category_id', 'left');

        if ($searchTerm) {
            $builder->groupStart()
                        ->like('products.name', $searchTerm)
                        ->orLike('products.code', $searchTerm)
                        ->orLike('categories.name', $searchTerm) // Cari juga berdasarkan nama kategori
                    ->groupEnd();
        }

        return [
            'products' => $builder->paginate($paginate),
            'pager'    => $this->pager,
        ];
    }
}
