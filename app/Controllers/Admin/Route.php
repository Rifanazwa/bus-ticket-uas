<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RouteModel;

class Route extends BaseController
{
    protected $routeModel;

    public function __construct()
    {
        $this->routeModel = new RouteModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        
        if (!empty($search)) {
            $this->routeModel->groupStart()
                             ->like('origin', $search)
                             ->orLike('destination', $search)
                             ->groupEnd();
        }
        
        // Paginate routes, 10 per page
        $routes = $this->routeModel->paginate(10, 'routes');
        $pager = $this->routeModel->pager;

        return view('admin/route/index', [
            'title'  => 'Manajemen Rute - SiTeBus',
            'routes' => $routes,
            'pager'  => $pager,
            'search' => $search
        ]);
    }

    public function create()
    {
        return view('admin/route/create', [
            'title' => 'Tambah Rute Baru - SiTeBus'
        ]);
    }

    public function store()
    {
        $rules = [
            'origin'             => 'required|max_length[100]',
            'destination'        => 'required|max_length[100]',
            'distance_km'        => 'required|decimal',
            'estimated_duration' => 'required|numeric|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $origin = $this->request->getPost('origin');
        $destination = $this->request->getPost('destination');

        // Check duplicates
        $duplicate = $this->routeModel->where('origin', $origin)->where('destination', $destination)->first();
        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', "Rute dari '{$origin}' ke '{$destination}' sudah terdaftar.");
        }

        $data = [
            'origin'             => $origin,
            'destination'        => $destination,
            'distance_km'        => $this->request->getPost('distance_km'),
            'estimated_duration' => $this->request->getPost('estimated_duration'),
        ];

        if ($this->routeModel->save($data)) {
            return redirect()->to(base_url('admin/route'))->with('success', 'Rute berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan rute.');
    }

    public function edit($id)
    {
        $route = $this->routeModel->find($id);
        if (!$route) {
            return redirect()->to(base_url('admin/route'))->with('error', 'Rute tidak ditemukan.');
        }

        return view('admin/route/edit', [
            'title' => 'Edit Rute - SiTeBus',
            'route' => $route
        ]);
    }

    public function update($id)
    {
        $rules = [
            'origin'             => 'required|max_length[100]',
            'destination'        => 'required|max_length[100]',
            'distance_km'        => 'required|decimal',
            'estimated_duration' => 'required|numeric|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $origin = $this->request->getPost('origin');
        $destination = $this->request->getPost('destination');

        // Check duplicates excluding this one
        $duplicate = $this->routeModel->where('origin', $origin)->where('destination', $destination)->where('id !=', $id)->first();
        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', "Rute dari '{$origin}' ke '{$destination}' sudah terdaftar.");
        }

        $data = [
            'id'                 => $id,
            'origin'             => $origin,
            'destination'        => $destination,
            'distance_km'        => $this->request->getPost('distance_km'),
            'estimated_duration' => $this->request->getPost('estimated_duration'),
        ];

        if ($this->routeModel->save($data)) {
            return redirect()->to(base_url('admin/route'))->with('success', 'Rute berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui rute.');
    }

    public function delete($id)
    {
        if ($this->routeModel->delete($id)) {
            return redirect()->to(base_url('admin/route'))->with('success', 'Rute berhasil dihapus.');
        }

        return redirect()->to(base_url('admin/route'))->with('error', 'Gagal menghapus rute.');
    }

    public function export()
    {
        $search = $this->request->getGet('search');
        
        if (!empty($search)) {
            $this->routeModel->groupStart()
                             ->like('origin', $search)
                             ->orLike('destination', $search)
                             ->groupEnd();
        }
        
        $routes = $this->routeModel->findAll();
        
        $filename = 'rute_perjalanan_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compliance
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Header
        fputcsv($output, ['origin', 'destination', 'distance_km', 'estimated_duration']);
        
        foreach ($routes as $route) {
            fputcsv($output, [
                $route['origin'],
                $route['destination'],
                $route['distance_km'],
                $route['estimated_duration']
            ]);
        }
        
        fclose($output);
        exit();
    }

    public function template()
    {
        $filename = 'template_import_rute.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, ['origin', 'destination', 'distance_km', 'estimated_duration']);
        
        // Sample rows
        fputcsv($output, ['Jakarta', 'Bandung', '150.00', '180']);
        fputcsv($output, ['Bandung', 'Jakarta', '150.00', '180']);
        fputcsv($output, ['Jakarta', 'Cirebon', '220.00', '180']);
        
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
            return redirect()->back()->with('error', 'Format CSV tidak valid. Harus memiliki header: origin, destination, distance_km, estimated_duration');
        }
        
        // Clean headers (remove BOM or spaces)
        $headers = array_map(function($h) {
            return trim(strtolower(preg_replace('/[\x{FEFF}\x{200B}-\x{200D}]/u', '', $h)));
        }, $headers);
        
        // Find indices
        $originIdx = array_search('origin', $headers);
        $destinationIdx = array_search('destination', $headers);
        $distanceIdx = array_search('distance_km', $headers);
        $durationIdx = array_search('estimated_duration', $headers);
        
        if ($originIdx === false || $destinationIdx === false || $distanceIdx === false || $durationIdx === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'Kolom header tidak sesuai. Pastikan ada kolom: origin, destination, distance_km, estimated_duration');
        }
        
        $rowNum = 1;
        $errors = [];
        $importData = [];
        
        // Retrieve existing routes to prevent duplicates
        $existingRoutes = $this->routeModel->findAll();
        $existingKeys = [];
        foreach ($existingRoutes as $r) {
            $existingKeys[] = strtolower($r['origin']) . '-' . strtolower($r['destination']);
        }
        $seenKeys = [];
        
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNum++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            $origin = isset($row[$originIdx]) ? trim($row[$originIdx]) : '';
            $destination = isset($row[$destinationIdx]) ? trim($row[$destinationIdx]) : '';
            $distance = isset($row[$distanceIdx]) ? trim($row[$distanceIdx]) : '';
            $duration = isset($row[$durationIdx]) ? trim($row[$durationIdx]) : '';
            
            // Validations
            if (empty($origin) || empty($destination) || empty($distance) || empty($duration)) {
                $errors[] = "Baris {$rowNum}: Data tidak lengkap (semua kolom harus diisi).";
                continue;
            }
            
            if (strlen($origin) > 100) {
                $errors[] = "Baris {$rowNum}: Kota Asal '{$origin}' terlalu panjang (max 100 karakter).";
                continue;
            }
            
            if (strlen($destination) > 100) {
                $errors[] = "Baris {$rowNum}: Kota Tujuan '{$destination}' terlalu panjang (max 100 karakter).";
                continue;
            }
            
            if (!is_numeric($distance) || (float)$distance <= 0) {
                $errors[] = "Baris {$rowNum}: Jarak (KM) '{$distance}' tidak valid (harus angka desimal positif).";
                continue;
            }
            
            if (!is_numeric($duration) || (int)$duration <= 0) {
                $errors[] = "Baris {$rowNum}: Estimasi Durasi '{$duration}' tidak valid (harus angka bulat positif).";
                continue;
            }
            
            $key = strtolower($origin) . '-' . strtolower($destination);
            if (in_array($key, $existingKeys) || in_array($key, $seenKeys)) {
                $errors[] = "Baris {$rowNum}: Rute dari '{$origin}' ke '{$destination}' sudah terdaftar/duplikat.";
                continue;
            }
            
            $seenKeys[] = $key;
            
            $importData[] = [
                'origin'             => $origin,
                'destination'        => $destination,
                'distance_km'        => (float)$distance,
                'estimated_duration' => (int)$duration,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ];
        }
        
        fclose($handle);
        
        if (!empty($errors)) {
            return redirect()->back()->with('errors', $errors);
        }
        
        if (empty($importData)) {
            return redirect()->back()->with('error', 'Tidak ada data valid yang diimport.');
        }
        
        $this->routeModel->insertBatch($importData);
        
        return redirect()->to(base_url('admin/route'))->with('success', count($importData) . ' Rute berhasil diimport.');
    }
}
