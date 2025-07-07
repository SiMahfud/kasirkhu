<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\UserModel;

class Auth extends Controller
{
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        // Inisialisasi session jika belum otomatis
        $this->session = service('session');
    }

    public function login()
    {
        // Jika sudah login, redirect ke halaman utama (misalnya dashboard atau produk)
        if ($this->session->get('isLoggedIn')) {
            return redirect()->to('/'); // Ganti dengan rute dashboard jika ada
        }

        $data = [
            'title' => 'Login Pengguna',
        ];
        return view('auth/login', $data);
    }

    public function attemptLogin()
    {
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[6]',
        ];

        $messages = [
            'username' => [
                'required'   => 'Username harus diisi.',
                'min_length' => 'Username minimal 3 karakter.',
            ],
            'password' => [
                'required'   => 'Password harus diisi.',
                'min_length' => 'Password minimal 6 karakter.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $username)->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Username tidak ditemukan.');
        }

        if (!password_verify($password, $user->password)) {
            return redirect()->back()->withInput()->with('error', 'Password salah.');
        }

        // Login berhasil, set session
        $sessionData = [
            'user_id'    => $user->id,
            'username'   => $user->username,
            'name'       => $user->name,
            'role'       => $user->role,
            'isLoggedIn' => true,
        ];
        $this->session->set($sessionData);
        log_message('error', '[AuthController::attemptLogin] Session data set: ' . print_r($this->session->get(), true)); // LOG SESSION

        // Redirect ke halaman setelah login (misalnya / atau /dashboard)
        // Untuk sekarang, kita redirect ke halaman produk
        return redirect()->to('/products')->with('message', 'Login berhasil! Selamat datang, ' . $user->name);
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/login')->with('message', 'Anda telah berhasil logout.');
    }
}
