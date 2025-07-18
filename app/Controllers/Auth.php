<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\UserModel;
use CodeIgniter\Shield\Authentication\Authenticators\Session;

class Auth extends Controller
{
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        // Inisialisasi session jika belum otomatis
        // $this->session = service('session');
    }

    public function login()
    {
        // Jika sudah login, redirect ke halaman utama (misalnya dashboard atau produk)
        if (auth()->loggedIn()) {
            return redirect()->to('/transactions'); // Ganti '/dashboard' dengan rute tujuan Anda
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

        $credentials = [
            'username'    => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
        ];

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // 4. Lakukan upaya login menggunakan Shield
        $result = $authenticator->attempt($credentials);
        if (! $result->isOK()) {
            // Jika login gagal, kembali ke form login dengan pesan error dari Shield
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        return redirect()->to('/transactions')->with('message', 'Login berhasil!');

    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/login')->with('message', 'Anda telah berhasil logout.');
    }
}
