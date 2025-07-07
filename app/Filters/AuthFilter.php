<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if (!$session->get('isLoggedIn')) {
            // Simpan URL yang diminta agar bisa redirect kembali setelah login
            // Hindari menyimpan URL login itu sendiri untuk mencegah loop redirect
            if (uri_string() !== 'login') {
                 $session->set('redirect_url', current_url());
            }
            return redirect()->to(site_url('login'))->with('error', 'Anda harus login untuk mengakses halaman ini.');
        }

        // Jika ada argumen role (misalnya 'admin'), cek role pengguna
        if (!empty($arguments)) {
            $userRole = $session->get('role');
            $allowedRoles = is_array($arguments) ? $arguments : [$arguments];

            if (!in_array($userRole, $allowedRoles)) {
                // Jika ingin menampilkan pesan error spesifik atau redirect ke halaman tertentu
                // return redirect()->to('/')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
                // Atau throw exception untuk halaman 403 Forbidden
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak memiliki hak akses yang cukup.');
            }
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
