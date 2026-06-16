<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $userRole = session()->get('userRole');

        // Check if role is allowed
        if ($arguments && !in_array($userRole, $arguments)) {
            // Redirect based on current role to avoid infinite redirects
            switch ($userRole) {
                case 'admin':
                    return redirect()->to(base_url('admin/dashboard'))->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
                case 'petugas':
                    return redirect()->to(base_url('petugas/scan'))->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
                case 'customer':
                default:
                    return redirect()->to(base_url('customer/home'))->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
