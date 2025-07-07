<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class CategoryController extends ResourceController
{
    protected $modelName = 'App\Models\CategoryModel';
    protected $format    = 'html'; // Default to HTML response for web views

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface|string
     */
    public function index()
    {
        $data = [
            'categories' => $this->model->orderBy('name', 'ASC')->paginate(10),
            'pager'      => $this->model->pager,
            'title'      => 'Daftar Kategori'
        ];
        return view('categories/index', $data);
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
        $category = $this->model->find($id);
        if (!$category) {
            return redirect()->to('/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        $data = [
            'category' => $category,
            'title'    => 'Detail Kategori: ' . esc($category->name)
        ];
        return view('categories/show', $data); // Anda perlu membuat view ini
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface|string
     */
    public function new()
    {
        $data = [
            'title' => 'Tambah Kategori Baru',
            'validation' => service('validation')
        ];
        return view('categories/new', $data);
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $rules = $this->model->getValidationRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ];

        if ($this->model->insert($data) === false) {
             // Ambil error dari model jika ada (misalnya, kegagalan DB unik)
            $errors = $this->model->errors();
            return redirect()->back()->withInput()->with('errors', $errors)->with('error', 'Gagal menyimpan kategori. Silakan coba lagi.');
        }

        return redirect()->to('/categories')->with('message', 'Kategori berhasil ditambahkan.');
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
        $category = $this->model->find($id);
        if (!$category) {
            return redirect()->to('/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        $data = [
            'category' => $category,
            'title'    => 'Edit Kategori: ' . esc($category->name),
            'validation' => service('validation')
        ];
        return view('categories/edit', $data);
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
        $category = $this->model->find($id);
        if (!$category) {
            return redirect()->to('/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        // Log data yang diterima (kembali ke logging normal)
        $raw_body = $this->request->getBody();
        log_message('debug', '[CategoryController::update] Raw PUT body: ' . $raw_body);
        log_message('debug', '[CategoryController::update] Parsed (getVar) name: ' . $this->request->getVar('name'));
        log_message('debug', '[CategoryController::update] Parsed (getVar) description: ' . $this->request->getVar('description'));


        $rules = $this->model->getValidationRules();
        // Jika nama tidak diubah, aturan unik bisa diabaikan untuk field ini
        // Namun, untuk kesederhanaan, kita validasi semua. Jika nama sama, DB tidak akan error.
        // Jika nama diubah menjadi nama yang sudah ada (bukan dirinya sendiri), validasi akan gagal di DB (jika ada unique constraint).
        // Model CI4 akan menangani ini dengan baik jika ada unique constraint di DB.

        if (! $this->validate($rules)) { // Menggunakan semua rules dari model
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getVar('name'), // Menggunakan getVar untuk konsistensi PUT/PATCH
            'description' => $this->request->getVar('description'),
        ];

        $updateResult = $this->model->update($id, $data);
        if ($updateResult === false) {
            $errors = $this->model->errors();
            log_message('error', '[CategoryController::update] Model update failed. ID: ' . $id . ' Errors: ' . print_r($errors, true) . ' Data: ' . print_r($data, true));
            return redirect()->back()->withInput()->with('errors', $errors)->with('error', 'Gagal memperbarui kategori. Silakan coba lagi.');
        }

        $targetUrl = site_url('/categories');
        log_message('debug', '[CategoryController::update] Intended redirect URL: ' . $targetUrl);
        return redirect()->to($targetUrl)->with('message', 'Kategori berhasil diperbarui.');
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
        $category = $this->model->find($id);
        if (!$category) {
            return redirect()->to('/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        if ($this->model->delete($id) === false) {
            // Jika ada foreign key constraint, ini bisa gagal.
            // Misalnya, jika ada produk yang masih menggunakan kategori ini dan ON DELETE adalah RESTRICT.
            // Dalam migrasi produk, saya set ON DELETE SET NULL, jadi ini seharusnya aman.
            $errors = $this->model->errors();
            return redirect()->to('/categories')->with('errors', $errors)->with('error', 'Gagal menghapus kategori. Mungkin masih digunakan oleh produk.');
        }

        return redirect()->to('/categories')->with('message', 'Kategori berhasil dihapus.');
    }
}
