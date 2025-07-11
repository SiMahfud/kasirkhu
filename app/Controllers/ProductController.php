<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\CategoryModel; // Untuk mengambil daftar kategori

class ProductController extends ResourceController
{
    protected $modelName = 'App\Models\ProductModel';
    protected $format    = 'html';

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface|string
     */
    public function index()
    {
        $searchTerm = $this->request->getGet('search'); // Ambil term pencarian dari query string
        $productDetails = $this->model->getProductsWithCategoryDetails($searchTerm, 10);

        $data = [
            'products'   => $productDetails['products'],
            'pager'      => $productDetails['pager'],
            'title'      => 'Daftar Produk',
            'searchTerm' => $searchTerm // Kirim searchTerm ke view untuk ditampilkan di input pencarian
        ];
        return view('products/index', $data);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string
     */
    public function show($id = null)
    {
        // Ambil produk dengan join kategori untuk detail
        $product = $this->model
                        ->select('products.*, categories.name as category_name')
                        ->join('categories', 'categories.id = products.category_id', 'left')
                        ->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');
        }

        $data = [
            'product' => $product,
            'title'   => 'Detail Produk: ' . esc($product->name)
        ];
        return view('products/show', $data); // Buat view ini nanti
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface|string
     */
    public function new()
    {
        $categoryModel = new CategoryModel();
        $data = [
            'title'      => 'Tambah Produk Baru',
            'categories' => $categoryModel->orderBy('name', 'ASC')->findAll(),
            'validation' => service('validation')
        ];
        return view('products/new', $data);
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $rules = $this->model->getValidationRules();

        if (!$this->validate($rules, $this->model->getValidationMessages())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getVar('name'),
            'code'        => $this->request->getVar('code') ?: null, // Set null jika kosong
            'category_id' => $this->request->getVar('category_id'),
            'price'       => $this->request->getVar('price'),
            'unit'        => $this->request->getVar('unit') ?: null,
            'description' => $this->request->getVar('description') ?: null,
            'stock'       => $this->request->getVar('stock') ?: null, // null jika tidak diisi, model akan set default jika ada
        ];

        if ($this->model->insert($data) === false) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors())
                             ->with('error', 'Gagal menyimpan produk. Silakan coba lagi.');
        }

        return redirect()->to('/products')->with('message', 'Produk berhasil ditambahkan.');
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string
     */
    public function edit($id = null)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');
        }

        $categoryModel = new CategoryModel();
        $data = [
            'title'      => 'Edit Produk: ' . esc($product->name),
            'product'    => $product,
            'categories' => $categoryModel->orderBy('name', 'ASC')->findAll(),
            'validation' => service('validation')
        ];
        return view('products/edit', $data);
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');
        }

        // Perlu placeholder {id} untuk aturan is_unique
        $rules = $this->model->getValidationRules(['id' => $id]);


        if (!$this->validate($rules, $this->model->getValidationMessages())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getVar('name'),
            'code'        => $this->request->getVar('code') ?: null,
            'category_id' => $this->request->getVar('category_id'),
            'price'       => $this->request->getVar('price'),
            'unit'        => $this->request->getVar('unit') ?: null,
            'description' => $this->request->getVar('description') ?: null,
            'stock'       => $this->request->getVar('stock') ?: null,
        ];

        if ($this->model->update($id, $data) === false) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors())
                             ->with('error', 'Gagal memperbarui produk. Silakan coba lagi.');
        }

        return redirect()->to('/products')->with('message', 'Produk berhasil diperbarui.');
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');
        }

        if ($this->model->delete($id) === false) {
            // Ini bisa gagal jika ada constraint database, misal di tabel transaction_details
            return redirect()->to('/products')->with('errors', $this->model->errors())
                             ->with('error', 'Gagal menghapus produk. Mungkin produk ini sudah ada dalam transaksi.');
        }

        return redirect()->to('/products')->with('message', 'Produk berhasil dihapus.');
    }

    //--------------------------------------------------------------------
    // Additional methods for stock management
    //--------------------------------------------------------------------

    /**
     * Display a list of products with their stock levels.
     *
     * @return string
     */
    public function stockReport()
    {
        $searchTerm = $this->request->getGet('search');
        // Fetch products with category name and stock information
        // Only show products that are expected to have stock (e.g., not services if distinguishable)
        // For now, showing all products that have a 'stock' column (model default behavior)
        $productDetails = $this->model->getProductsWithCategoryDetails($searchTerm, 15, true); // true to include all, even null stock

        $data = [
            'products'   => $productDetails['products'],
            'pager'      => $productDetails['pager'],
            'title'      => 'Laporan Stok Produk',
            'searchTerm' => $searchTerm,
            'message'    => session()->getFlashdata('message'),
            'error'      => session()->getFlashdata('error'),
        ];
        return view('products/stock_report', $data);
    }

    /**
     * Process stock adjustment for a product.
     *
     * @param int|string|null $id Product ID
     * @return ResponseInterface
     */
    public function adjustStock($id = null)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return redirect()->to('/products/stock')->with('error', 'Produk tidak ditemukan.');
        }

        $rules = [
            'adjustment_type' => 'required|in_list[add,subtract,set]',
            'quantity'        => 'required|integer|greater_than_equal_to[0]',
            'notes'           => 'permit_empty|string|max_length[255]'
        ];

        $messages = [
            'adjustment_type' => ['required' => 'Jenis penyesuaian harus dipilih.', 'in_list' => 'Jenis penyesuaian tidak valid.'],
            'quantity' => ['required' => 'Kuantitas harus diisi.', 'integer' => 'Kuantitas harus berupa angka.', 'greater_than_equal_to' => 'Kuantitas tidak boleh negatif.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $adjustmentType = $this->request->getPost('adjustment_type');
        $quantity = (int)$this->request->getPost('quantity');
        // $notes = $this->request->getPost('notes'); // For logging later if needed

        $currentStock = $product->stock ?? 0; // Assume 0 if stock is null
        $newStock = $currentStock;

        switch ($adjustmentType) {
            case 'add':
                $newStock = $currentStock + $quantity;
                break;
            case 'subtract':
                $newStock = $currentStock - $quantity;
                if ($newStock < 0) {
                    return redirect()->back()->withInput()->with('error', 'Stok tidak boleh menjadi negatif.');
                }
                break;
            case 'set':
                $newStock = $quantity;
                break;
        }

        if ($this->model->update($id, ['stock' => $newStock])) {
            // Log stock adjustment (future enhancement: create a stock_adjustments table)
            // For now, just a success message
            return redirect()->to('/products/stock')->with('message', 'Stok produk ' . esc($product->name) . ' berhasil disesuaikan menjadi ' . $newStock . '.');
        }

        return redirect()->to('/products/stock')->with('error', 'Gagal menyesuaikan stok produk: ' . implode(', ', $this->model->errors()));
    }
}
