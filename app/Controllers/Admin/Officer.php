<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\BusModel;

class Officer extends BaseController
{
    protected $userModel;
    protected $busModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->busModel  = new BusModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $search = $this->request->getGet('search');

        $builder = $this->userModel->where('role', 'petugas');

        if (!empty($search)) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('email', $search)
                    ->orLike('phone', $search)
                    ->groupEnd();
        }

        // Paginate officers, 10 per page
        $officers = $this->userModel->paginate(10, 'officers');
        $pager = $this->userModel->pager;

        foreach ($officers as &$officer) {
            $officer['bus'] = $officer['bus_id'] ? $this->busModel->find($officer['bus_id']) : null;
        }
        unset($officer);

        return view('admin/officer/index', [
            'title'    => 'Manajemen Petugas - SiTeBus',
            'officers' => $officers,
            'pager'    => $pager,
            'search'   => $search
        ]);
    }

    public function create()
    {
        $buses = $this->busModel->findAll();

        return view('admin/officer/create', [
            'title' => 'Tambah Petugas Baru - SiTeBus',
            'buses' => $buses
        ]);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'phone'    => 'required|min_length[8]|max_length[20]',
            'password' => 'required|min_length[6]',
            'bus_id'   => 'permit_empty|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $busId = $this->request->getPost('bus_id');
        if (empty($busId)) {
            $busId = null;
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'phone'    => $this->request->getPost('phone'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => 'petugas',
            'bus_id'   => $busId
        ];

        if ($this->userModel->save($data)) {
            return redirect()->to(base_url('admin/officer'))->with('success', 'Petugas berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan petugas.');
    }

    public function edit($id)
    {
        $officer = $this->userModel->where('role', 'petugas')->find($id);
        if (!$officer) {
            return redirect()->to(base_url('admin/officer'))->with('error', 'Petugas tidak ditemukan.');
        }

        $buses = $this->busModel->findAll();

        return view('admin/officer/edit', [
            'title'   => 'Edit Petugas - SiTeBus',
            'officer' => $officer,
            'buses'   => $buses
        ]);
    }

    public function update($id)
    {
        $rules = [
            'name'   => 'required|min_length[3]|max_length[100]',
            'email'  => "required|valid_email|is_unique[users.email,id,{$id}]",
            'phone'  => 'required|min_length[8]|max_length[20]',
            'bus_id' => 'permit_empty|numeric'
        ];

        // Only validate password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $busId = $this->request->getPost('bus_id');
        if (empty($busId)) {
            $busId = null;
        }

        $data = [
            'id'     => $id,
            'name'   => $this->request->getPost('name'),
            'email'  => $this->request->getPost('email'),
            'phone'  => $this->request->getPost('phone'),
            'bus_id' => $busId
        ];

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($this->userModel->save($data)) {
            return redirect()->to(base_url('admin/officer'))->with('success', 'Petugas berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui petugas.');
    }

    public function delete($id)
    {
        $officer = $this->userModel->where('role', 'petugas')->find($id);
        if (!$officer) {
            return redirect()->to(base_url('admin/officer'))->with('error', 'Petugas tidak ditemukan.');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to(base_url('admin/officer'))->with('success', 'Petugas berhasil dihapus.');
        }

        return redirect()->to(base_url('admin/officer'))->with('error', 'Gagal menghapus petugas.');
    }

    public function export()
    {
        $search = $this->request->getGet('search');
        
        $builder = $this->userModel->where('role', 'petugas');
        
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('email', $search)
                    ->orLike('phone', $search)
                    ->groupEnd();
        }
        
        $officers = $builder->findAll();

        $filename = 'petugas_terminal_' . date('Ymd_His') . '.csv';

        $stream = fopen('php://temp', 'w+');
        
        // Add UTF-8 BOM for Excel compliance
        fprintf($stream, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Header
        fputcsv($stream, ['name', 'email', 'phone', 'bus_code', 'bus_name']);

        foreach ($officers as $off) {
            $bus = $off['bus_id'] ? $this->busModel->find($off['bus_id']) : null;
            fputcsv($stream, [
                $off['name'],
                $off['email'],
                $off['phone'],
                $bus ? $bus['code'] : '',
                $bus ? $bus['name'] : ''
            ]);
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        return $this->response->download($filename, $csvContent)->setContentType('text/csv');
    }

    public function template()
    {
        $filename = 'template_import_petugas.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, ['name', 'email', 'phone', 'password', 'bus_code']);

        // Sample rows
        fputcsv($output, ['Budi Santoso', 'budi.santoso@sitebus.com', '081234567890', 'budi123', 'RI-EXE01']);
        fputcsv($output, ['Siti Aminah', 'siti.aminah@sitebus.com', '081298765432', 'siti123', '']);

        fclose($output);
        exit();
    }

    public function import()
    {
        $file = $this->request->getFile('csv_file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File upload gagal atau file tidak valid.');
        }

        $ext = $file->getClientExtension();
        if ($ext !== 'csv') {
            return redirect()->back()->with('error', 'Hanya file CSV (.csv) yang diperbolehkan.');
        }

        $filePath = $file->getTempName();
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return redirect()->back()->with('error', 'Gagal membuka file CSV.');
        }

        // Read header row
        $headers = fgetcsv($handle, 1000, ',');
        if (!$headers || count($headers) < 4) {
            fclose($handle);
            return redirect()->back()->with('error', 'Format CSV tidak valid. Harus memiliki setidaknya header: name, email, phone, password, bus_code');
        }

        // Clean headers (remove BOM or spaces)
        $headers = array_map(function($h) {
            return trim(strtolower(preg_replace('/[\x{FEFF}\x{200B}-\x{200D}]/u', '', $h)));
        }, $headers);

        // Find indices
        $nameIdx     = array_search('name', $headers);
        $emailIdx    = array_search('email', $headers);
        $phoneIdx    = array_search('phone', $headers);
        $passwordIdx = array_search('password', $headers);
        $busCodeIdx  = array_search('bus_code', $headers);

        if ($nameIdx === false || $emailIdx === false || $phoneIdx === false || $passwordIdx === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'Kolom header tidak sesuai. Pastikan ada kolom: name, email, phone, password');
        }

        $rowNum = 1;
        $errors = [];
        $importData = [];

        // Retrieve existing emails to prevent duplicates
        $existingUsers = $this->userModel->findAll();
        $existingEmails = array_map('strtolower', array_column($existingUsers, 'email'));
        $seenEmails = [];

        // Preload buses to avoid multiple database hits inside loop
        $buses = $this->busModel->findAll();
        $busMap = []; // code => id
        foreach ($buses as $bus) {
            $busMap[strtolower($bus['code'])] = $bus['id'];
        }

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNum++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $name     = isset($row[$nameIdx]) ? trim($row[$nameIdx]) : '';
            $email    = isset($row[$emailIdx]) ? trim($row[$emailIdx]) : '';
            $phone    = isset($row[$phoneIdx]) ? trim($row[$phoneIdx]) : '';
            $password = isset($row[$passwordIdx]) ? trim($row[$passwordIdx]) : '';
            $busCode  = ($busCodeIdx !== false && isset($row[$busCodeIdx])) ? trim($row[$busCodeIdx]) : '';

            // Validations
            if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                $errors[] = "Baris {$rowNum}: Data tidak lengkap (name, email, phone, dan password harus diisi).";
                continue;
            }

            if (strlen($name) > 100) {
                $errors[] = "Baris {$rowNum}: Nama '{$name}' terlalu panjang (max 100 karakter).";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Baris {$rowNum}: Format email '{$email}' tidak valid.";
                continue;
            }

            if (in_array(strtolower($email), $existingEmails) || in_array(strtolower($email), $seenEmails)) {
                $errors[] = "Baris {$rowNum}: Email '{$email}' sudah terdaftar/duplikat.";
                continue;
            }

            if (strlen($phone) > 20 || strlen($phone) < 8) {
                $errors[] = "Baris {$rowNum}: Nomor telepon '{$phone}' tidak valid (harus 8-20 karakter).";
                continue;
            }

            if (strlen($password) < 6) {
                $errors[] = "Baris {$rowNum}: Password harus minimal 6 karakter.";
                continue;
            }

            $busId = null;
            if (!empty($busCode)) {
                $lowerBusCode = strtolower($busCode);
                if (isset($busMap[$lowerBusCode])) {
                    $busId = $busMap[$lowerBusCode];
                } else {
                    $errors[] = "Baris {$rowNum}: Kode bus '{$busCode}' tidak ditemukan di database.";
                    continue;
                }
            }

            $seenEmails[] = strtolower($email);

            $importData[] = [
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'role'       => 'petugas',
                'bus_id'     => $busId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        fclose($handle);

        if (!empty($errors)) {
            return redirect()->back()->with('errors', $errors);
        }

        if (empty($importData)) {
            return redirect()->back()->with('error', 'Tidak ada data valid yang diimport.');
        }

        $this->userModel->insertBatch($importData);

        return redirect()->to(base_url('admin/officer'))->with('success', count($importData) . ' Petugas berhasil diimport.');
    }
}
