<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusModel;

class Bus extends BaseController
{
    protected $busModel;

    public function __construct()
    {
        $this->busModel = new BusModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $userModel = new \App\Models\UserModel();
        $search = $this->request->getGet('search');
        
        if (!empty($search)) {
            $this->busModel->groupStart()
                           ->like('code', $search)
                           ->orLike('name', $search)
                           ->groupEnd();
        }
        
        // Paginate buses, 10 per page
        $buses = $this->busModel->paginate(10, 'buses');
        $pager = $this->busModel->pager;
        
        foreach ($buses as &$bus) {
            $bus['officers'] = $userModel->where('role', 'petugas')->where('bus_id', $bus['id'])->findAll();
        }
        unset($bus);

        return view('admin/bus/index', [
            'title'  => 'Manajemen Bus - SiTeBus',
            'buses'  => $buses,
            'pager'  => $pager,
            'search' => $search
        ]);
    }

    public function create()
    {
        $userModel = new \App\Models\UserModel();
        $officers = $userModel->where('role', 'petugas')->findAll();

        return view('admin/bus/create', [
            'title'    => 'Tambah Bus Baru - SiTeBus',
            'officers' => $officers
        ]);
    }

    public function store()
    {
        $rules = [
            'code'        => 'required|max_length[20]|is_unique[buses.code]',
            'name'        => 'required|max_length[100]',
            'type'        => 'required',
            'total_seats' => 'required|numeric|greater_than[0]',
            'seat_layout' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'code'        => $this->request->getPost('code'),
            'name'        => $this->request->getPost('name'),
            'type'        => $this->request->getPost('type'),
            'total_seats' => $this->request->getPost('total_seats'),
            'seat_layout' => $this->request->getPost('seat_layout')
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $this->busModel->save($data);
        $busId = $this->busModel->getInsertID();

        // Assign selected officers to this bus
        $assignedOfficerIds = $this->request->getPost('officers') ?? [];
        $userModel = new \App\Models\UserModel();
        if (!empty($assignedOfficerIds)) {
            $userModel->whereIn('id', $assignedOfficerIds)->set(['bus_id' => $busId])->update();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan bus.');
        }

        return redirect()->to(base_url('admin/bus'))->with('success', 'Bus berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $bus = $this->busModel->find($id);
        if (!$bus) {
            return redirect()->to(base_url('admin/bus'))->with('error', 'Bus tidak ditemukan.');
        }

        $userModel = new \App\Models\UserModel();
        $officers = $userModel->where('role', 'petugas')->findAll();

        return view('admin/bus/edit', [
            'title'    => 'Edit Bus - SiTeBus',
            'bus'      => $bus,
            'officers' => $officers
        ]);
    }

    public function update($id)
    {
        $rules = [
            'code'        => "required|max_length[20]|is_unique[buses.code,id,{$id}]",
            'name'        => 'required|max_length[100]',
            'type'        => 'required',
            'total_seats' => 'required|numeric|greater_than[0]',
            'seat_layout' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'          => $id,
            'code'        => $this->request->getPost('code'),
            'name'        => $this->request->getPost('name'),
            'type'        => $this->request->getPost('type'),
            'total_seats' => $this->request->getPost('total_seats'),
            'seat_layout' => $this->request->getPost('seat_layout')
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $this->busModel->save($data);

        // Update assigned officers
        $assignedOfficerIds = $this->request->getPost('officers') ?? [];
        $userModel = new \App\Models\UserModel();
        // Reset old assignments first
        $userModel->where('bus_id', $id)->set(['bus_id' => null])->update();
        // Add new assignments
        if (!empty($assignedOfficerIds)) {
            $userModel->whereIn('id', $assignedOfficerIds)->set(['bus_id' => $id])->update();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui bus.');
        }

        return redirect()->to(base_url('admin/bus'))->with('success', 'Bus berhasil diperbarui.');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Reset officer assignments
        $userModel = new \App\Models\UserModel();
        $userModel->where('bus_id', $id)->set(['bus_id' => null])->update();

        $this->busModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(base_url('admin/bus'))->with('error', 'Gagal menghapus bus.');
        }

        return redirect()->to(base_url('admin/bus'))->with('success', 'Bus berhasil dihapus.');
    }

    public function export()
    {
        $buses = $this->busModel->findAll();
        
        $filename = 'armada_bus_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, ['code', 'name', 'type']);
        
        foreach ($buses as $bus) {
            fputcsv($output, [
                $bus['code'],
                $bus['name'],
                $bus['type']
            ]);
        }
        
        fclose($output);
        exit();
    }

    public function template()
    {
        $filename = 'template_import_bus.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, ['code', 'name', 'type']);
        
        // Sample rows
        fputcsv($output, ['JB-EXE01', 'Joss Bus (Eksekutif)', 'Eksekutif']);
        fputcsv($output, ['JB-BIS01', 'Joss Bus (Bisnis)', 'Bisnis']);
        fputcsv($output, ['JB-EKO01', 'Joss Bus (Ekonomi)', 'Ekonomi']);
        
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
        if (!$headers || count($headers) < 3) {
            fclose($handle);
            return redirect()->back()->with('error', 'Format CSV tidak valid. Harus memiliki header: code, name, type');
        }
        
        // Clean headers (remove BOM or spaces)
        $headers = array_map(function($h) {
            return trim(strtolower(preg_replace('/[\x{FEFF}\x{200B}-\x{200D}]/u', '', $h)));
        }, $headers);
        
        // Find indices
        $codeIdx = array_search('code', $headers);
        $nameIdx = array_search('name', $headers);
        $typeIdx = array_search('type', $headers);
        
        if ($codeIdx === false || $nameIdx === false || $typeIdx === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'Kolom header tidak sesuai. Pastikan ada kolom: code, name, type');
        }
        
        $rowNum = 1;
        $errors = [];
        $importData = [];
        $existingCodes = array_column($this->busModel->select('code')->findAll(), 'code');
        $seenCodes = [];
        
        // Layout helper generators
        $execLayout = [];
        for ($row = 1; $row <= 10; $row++) {
            $execLayout[] = ['row' => $row, 'col' => 'A', 'number' => $row . 'A', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'B', 'number' => $row . 'B', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'aisle', 'number' => null, 'type' => 'aisle'];
            $execLayout[] = ['row' => $row, 'col' => 'C', 'number' => $row . 'C', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'D', 'number' => $row . 'D', 'type' => 'seat'];
        }

        $vipLayout = [];
        for ($row = 1; $row <= 8; $row++) {
            $vipLayout[] = ['row' => $row, 'col' => 'A', 'number' => $row . 'A', 'type' => 'seat'];
            $vipLayout[] = ['row' => $row, 'col' => 'B', 'number' => $row . 'B', 'type' => 'seat'];
            $vipLayout[] = ['row' => $row, 'col' => 'aisle', 'number' => null, 'type' => 'aisle'];
            $vipLayout[] = ['row' => $row, 'col' => 'C', 'number' => $row . 'C', 'type' => 'seat'];
        }

        $ekoLayout = [];
        for ($row = 1; $row <= 12; $row++) {
            $ekoLayout[] = ['row' => $row, 'col' => 'A', 'number' => $row . 'A', 'type' => 'seat'];
            $ekoLayout[] = ['row' => $row, 'col' => 'B', 'number' => $row . 'B', 'type' => 'seat'];
            $ekoLayout[] = ['row' => $row, 'col' => 'aisle', 'number' => null, 'type' => 'aisle'];
            $ekoLayout[] = ['row' => $row, 'col' => 'C', 'number' => $row . 'C', 'type' => 'seat'];
            $ekoLayout[] = ['row' => $row, 'col' => 'D', 'number' => $row . 'D', 'type' => 'seat'];
        }
        
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNum++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            $code = isset($row[$codeIdx]) ? trim($row[$codeIdx]) : '';
            $name = isset($row[$nameIdx]) ? trim($row[$nameIdx]) : '';
            $type = isset($row[$typeIdx]) ? trim($row[$typeIdx]) : '';
            
            // Validations
            if (empty($code) || empty($name) || empty($type)) {
                $errors[] = "Baris {$rowNum}: Data tidak lengkap (BUS ID, Nama, dan Kelas Bus harus diisi).";
                continue;
            }
            
            if (strlen($code) > 20) {
                $errors[] = "Baris {$rowNum}: BUS ID '{$code}' terlalu panjang (max 20 karakter).";
                continue;
            }
            
            if (strlen($name) > 100) {
                $errors[] = "Baris {$rowNum}: Nama PO '{$name}' terlalu panjang (max 100 karakter).";
                continue;
            }
            
            if (in_array($code, $existingCodes) || in_array($code, $seenCodes)) {
                $errors[] = "Baris {$rowNum}: BUS ID '{$code}' sudah terdaftar/duplikat.";
                continue;
            }
            
            $validTypes = ['Eksekutif', 'Bisnis', 'Ekonomi'];
            if (!in_array($type, $validTypes)) {
                $errors[] = "Baris {$rowNum}: Kelas Bus '{$type}' tidak valid. Harus salah satu dari: Eksekutif, Bisnis, Ekonomi.";
                continue;
            }
            
            $seenCodes[] = $code;
            
            // Build details
            $layout = [];
            $seats = 0;
            if ($type === 'Bisnis') {
                $layout = $vipLayout;
                $seats = 24;
            } elseif ($type === 'Eksekutif') {
                $layout = $execLayout;
                $seats = 40;
            } else {
                $layout = $ekoLayout;
                $seats = 48;
            }
            
            $importData[] = [
                'code'        => $code,
                'name'        => $name,
                'type'        => $type,
                'seat_layout' => json_encode($layout),
                'total_seats' => $seats,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }
        
        fclose($handle);
        
        if (!empty($errors)) {
            return redirect()->back()->with('errors', $errors);
        }
        
        if (empty($importData)) {
            return redirect()->back()->with('error', 'Tidak ada data valid yang diimport.');
        }
        
        $this->busModel->insertBatch($importData);
        
        return redirect()->to(base_url('admin/bus'))->with('success', count($importData) . ' Bus berhasil diimport.');
    }
}
