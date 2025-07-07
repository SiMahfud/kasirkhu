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
}
