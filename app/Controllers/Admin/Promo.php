<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PromoModel;

class Promo extends BaseController
{
    protected $promoModel;

    public function __construct()
    {
        $this->promoModel = new PromoModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $promos = $this->promoModel->findAll();

        return view('admin/promo/index', [
            'title'  => 'Manajemen Promo & Voucher - SiTeBus',
            'promos' => $promos
        ]);
    }

    public function create()
    {
        return view('admin/promo/create', [
            'title' => 'Tambah Promo/Voucher Baru - SiTeBus'
        ]);
    }

    public function store()
    {
        $rules = [
            'code'           => 'required|max_length[30]|is_unique[promos.code]',
            'discount_type'  => 'required|in_list[percent,fixed]',
            'discount_value' => 'required|decimal',
            'valid_from'     => 'required|valid_date',
            'valid_until'    => 'required|valid_date',
            'usage_limit'    => 'required|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'code'           => strtoupper($this->request->getPost('code')),
            'discount_type'  => $this->request->getPost('discount_type'),
            'discount_value' => $this->request->getPost('discount_value'),
            'valid_from'     => $this->request->getPost('valid_from'),
            'valid_until'    => $this->request->getPost('valid_until'),
            'usage_limit'    => $this->request->getPost('usage_limit'),
        ];

        if ($this->promoModel->save($data)) {
            return redirect()->to(base_url('admin/promo'))->with('success', 'Promo berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan promo.');
    }

    public function edit($id)
    {
        $promo = $this->promoModel->find($id);
        if (!$promo) {
            return redirect()->to(base_url('admin/promo'))->with('error', 'Promo tidak ditemukan.');
        }

        return view('admin/promo/edit', [
            'title' => 'Edit Promo/Voucher - SiTeBus',
            'promo' => $promo
        ]);
    }

    public function update($id)
    {
        $rules = [
            'code'           => "required|max_length[30]|is_unique[promos.code,id,{$id}]",
            'discount_type'  => 'required|in_list[percent,fixed]',
            'discount_value' => 'required|decimal',
            'valid_from'     => 'required|valid_date',
            'valid_until'    => 'required|valid_date',
            'usage_limit'    => 'required|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'             => $id,
            'code'           => strtoupper($this->request->getPost('code')),
            'discount_type'  => $this->request->getPost('discount_type'),
            'discount_value' => $this->request->getPost('discount_value'),
            'valid_from'     => $this->request->getPost('valid_from'),
            'valid_until'    => $this->request->getPost('valid_until'),
            'usage_limit'    => $this->request->getPost('usage_limit'),
        ];

        if ($this->promoModel->save($data)) {
            return redirect()->to(base_url('admin/promo'))->with('success', 'Promo berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui promo.');
    }

    public function delete($id)
    {
        if ($this->promoModel->delete($id)) {
            return redirect()->to(base_url('admin/promo'))->with('success', 'Promo berhasil dihapus.');
        }

        return redirect()->to(base_url('admin/promo'))->with('error', 'Gagal menghapus promo.');
    }
}
