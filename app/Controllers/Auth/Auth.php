<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function login()
    {
        // If already logged in, redirect based on role
        if (session()->get('isLoggedIn')) {
            return $this->redirectUserByRole(session()->get('userRole'));
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau Password salah.');
        }

        // Set session
        $sessionData = [
            'userId'     => $user['id'],
            'userName'   => $user['name'],
            'userEmail'  => $user['email'],
            'userPhone'  => $user['phone'],
            'userRole'   => $user['role'],
            'isLoggedIn' => true,
        ];
        session()->set($sessionData);

        return $this->redirectUserByRole($user['role'])->with('success', 'Selamat datang kembali, ' . $user['name'] . '!');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectUserByRole(session()->get('userRole'));
        }

        return view('auth/register');
    }

    public function attemptRegister()
    {
        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'phone'            => 'required|min_length[8]|max_length[20]',
            'password'         => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'phone'    => $this->request->getPost('phone'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => 'customer', // default role registered is customer
        ];

        if ($this->userModel->save($data)) {
            return redirect()->to(base_url('login'))->with('success', 'Registrasi berhasil! Silakan masuk.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal melakukan registrasi. Silakan coba lagi.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Anda telah berhasil keluar.');
    }

    private function redirectUserByRole($role)
    {
        switch ($role) {
            case 'admin':
                return redirect()->to(base_url('admin/dashboard'));
            case 'petugas':
                return redirect()->to(base_url('petugas/scan'));
            case 'customer':
            default:
                return redirect()->to(base_url('customer/home'));
        }
    }
}
