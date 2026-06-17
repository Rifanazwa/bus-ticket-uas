<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $userModel = new \App\Models\UserModel();
        $userExists = $userModel->find(session()->get('userId'));
        if (!$userExists) {
            session()->destroy();
            return redirect()->to(base_url('login'))->with('error', 'Sesi Anda telah kedaluwarsa atau akun Anda tidak ditemukan. Silakan masuk kembali.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
