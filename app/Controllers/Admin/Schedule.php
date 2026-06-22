<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ScheduleModel;
use App\Models\RouteModel;
use App\Models\BusModel;

class Schedule extends BaseController
{
    protected $scheduleModel;
    protected $routeModel;
    protected $busModel;

    public function __construct()
    {
        $this->scheduleModel = new ScheduleModel();
        $this->routeModel = new RouteModel();
        $this->busModel = new BusModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        
        // Base query with joins
        $query = $this->scheduleModel->select('schedules.*, routes.origin, routes.destination, buses.name as bus_name, buses.type as bus_type, d1.name as driver_1_name, d2.name as driver_2_name, cond.name as conductor_name')
                                      ->join('routes', 'routes.id = schedules.route_id')
                                      ->join('buses', 'buses.id = schedules.bus_id')
                                      ->join('users d1', 'd1.id = schedules.driver_1_id', 'left')
                                      ->join('users d2', 'd2.id = schedules.driver_2_id', 'left')
                                      ->join('users cond', 'cond.id = schedules.conductor_id', 'left')
                                      ->orderBy('schedules.departure_time', 'ASC');
        
        if (!empty($search)) {
            $query->groupStart()
                  ->like('routes.origin', $search)
                  ->orLike('routes.destination', $search)
                  ->orLike('buses.name', $search)
                  ->orLike('buses.type', $search)
                  ->orLike('schedules.departure_time', $search)
                  ->orLike('d1.name', $search)
                  ->orLike('d2.name', $search)
                  ->orLike('cond.name', $search)
                  ->groupEnd();
        }
        
        // Paginate schedules, 10 per page
        $schedules = $query->paginate(10, 'schedules');
        $pager = $this->scheduleModel->pager;

        return view('admin/schedule/index', [
            'title'     => 'Manajemen Jadwal - SiTeBus',
            'schedules' => $schedules,
            'pager'     => $pager,
            'search'    => $search
        ]);
    }

    public function create()
    {
        $routes = $this->routeModel->findAll();
        $buses  = $this->busModel->findAll();
        
        $userModel = new \App\Models\UserModel();
        $drivers1 = $userModel->where('role', 'petugas')->where('crew_role', 'driver_1')->findAll();
        $drivers2 = $userModel->where('role', 'petugas')->where('crew_role', 'driver_2')->findAll();
        $conductors = $userModel->where('role', 'petugas')->where('crew_role', 'conductor')->findAll();

        return view('admin/schedule/create', [
            'title'      => 'Tambah Jadwal Baru - SiTeBus',
            'routes'     => $routes,
            'buses'      => $buses,
            'drivers1'   => $drivers1,
            'drivers2'   => $drivers2,
            'conductors' => $conductors
        ]);
    }

    public function store()
    {
        $rules = [
            'route_id'       => 'required|numeric',
            'bus_id'         => 'required|numeric',
            'departure_time' => 'required',
            'arrival_time'   => 'required',
            'price'          => 'required|decimal',
            'status'         => 'required|in_list[scheduled,ongoing,completed,cancelled]',
            'driver_1_id'    => 'permit_empty|numeric',
            'driver_2_id'    => 'permit_empty|numeric',
            'conductor_id'   => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'route_id'       => $this->request->getPost('route_id'),
            'bus_id'         => $this->request->getPost('bus_id'),
            'departure_time' => $this->request->getPost('departure_time'),
            'arrival_time'   => $this->request->getPost('arrival_time'),
            'price'          => $this->request->getPost('price'),
            'status'         => $this->request->getPost('status'),
            'driver_1_id'    => $this->request->getPost('driver_1_id') ?: null,
            'driver_2_id'    => $this->request->getPost('driver_2_id') ?: null,
            'conductor_id'   => $this->request->getPost('conductor_id') ?: null,
        ];

        if ($this->scheduleModel->save($data)) {
            return redirect()->to(base_url('admin/schedule'))->with('success', 'Jadwal berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan jadwal.');
    }

    public function edit($id)
    {
        $schedule = $this->scheduleModel->find($id);
        if (!$schedule) {
            return redirect()->to(base_url('admin/schedule'))->with('error', 'Jadwal tidak ditemukan.');
        }

        $routes = $this->routeModel->findAll();
        $buses  = $this->busModel->findAll();
        
        $userModel = new \App\Models\UserModel();
        $drivers1 = $userModel->where('role', 'petugas')->where('crew_role', 'driver_1')->findAll();
        $drivers2 = $userModel->where('role', 'petugas')->where('crew_role', 'driver_2')->findAll();
        $conductors = $userModel->where('role', 'petugas')->where('crew_role', 'conductor')->findAll();

        return view('admin/schedule/edit', [
            'title'      => 'Edit Jadwal - SiTeBus',
            'schedule'   => $schedule,
            'routes'     => $routes,
            'buses'      => $buses,
            'drivers1'   => $drivers1,
            'drivers2'   => $drivers2,
            'conductors' => $conductors
        ]);
    }

    public function update($id)
    {
        $rules = [
            'route_id'       => 'required|numeric',
            'bus_id'         => 'required|numeric',
            'departure_time' => 'required',
            'arrival_time'   => 'required',
            'price'          => 'required|decimal',
            'status'         => 'required|in_list[scheduled,ongoing,completed,cancelled]',
            'driver_1_id'    => 'permit_empty|numeric',
            'driver_2_id'    => 'permit_empty|numeric',
            'conductor_id'   => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'             => $id,
            'route_id'       => $this->request->getPost('route_id'),
            'bus_id'         => $this->request->getPost('bus_id'),
            'departure_time' => $this->request->getPost('departure_time'),
            'arrival_time'   => $this->request->getPost('arrival_time'),
            'price'          => $this->request->getPost('price'),
            'status'         => $this->request->getPost('status'),
            'driver_1_id'    => $this->request->getPost('driver_1_id') ?: null,
            'driver_2_id'    => $this->request->getPost('driver_2_id') ?: null,
            'conductor_id'   => $this->request->getPost('conductor_id') ?: null,
        ];

        if ($this->scheduleModel->save($data)) {
            return redirect()->to(base_url('admin/schedule'))->with('success', 'Jadwal berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui jadwal.');
    }

    public function delete($id)
    {
        if ($this->scheduleModel->delete($id)) {
            return redirect()->to(base_url('admin/schedule'))->with('success', 'Jadwal berhasil dihapus.');
        }

        return redirect()->to(base_url('admin/schedule'))->with('error', 'Gagal menghapus jadwal.');
    }

    public function template()
    {
        $filename = 'template_import_jadwal.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, ['origin', 'destination', 'bus_name', 'departure_time', 'arrival_time', 'price', 'status', 'driver_1', 'driver_2', 'conductor']);
        
        // Sample rows
        fputcsv($output, ['Jakarta', 'Bandung', 'Joss Bus 1', '2026-06-17 08:00:00', '2026-06-17 11:00:00', '120000', 'scheduled', 'Bambang Wijaya', 'Sutrisno', 'Asep Sunandar']);
        fputcsv($output, ['Bandung', 'Jakarta', 'Joss Bus 2', '2026-06-17 13:00:00', '2026-06-17 16:00:00', '125000', 'scheduled', 'Joko Sunarto', 'Heri Prasetyo', 'Dadang Hermawan']);
        
        fclose($output);
        exit();
    }

    public function export()
    {
        $search = $this->request->getGet('search');
        
        // Base query with joins
        $query = $this->scheduleModel->select('schedules.*, routes.origin, routes.destination, buses.name as bus_name, buses.type as bus_type, d1.name as driver_1_name, d2.name as driver_2_name, cond.name as conductor_name')
                                      ->join('routes', 'routes.id = schedules.route_id')
                                      ->join('buses', 'buses.id = schedules.bus_id')
                                      ->join('users d1', 'd1.id = schedules.driver_1_id', 'left')
                                      ->join('users d2', 'd2.id = schedules.driver_2_id', 'left')
                                      ->join('users cond', 'cond.id = schedules.conductor_id', 'left')
                                      ->orderBy('schedules.departure_time', 'ASC');
        
        if (!empty($search)) {
            $query->groupStart()
                  ->like('routes.origin', $search)
                  ->orLike('routes.destination', $search)
                  ->orLike('buses.name', $search)
                  ->orLike('buses.type', $search)
                  ->orLike('schedules.departure_time', $search)
                  ->orLike('d1.name', $search)
                  ->orLike('d2.name', $search)
                  ->orLike('cond.name', $search)
                  ->groupEnd();
        }
        
        $schedules = $query->findAll();
        
        $filename = 'jadwal_keberangkatan_' . date('Ymd_His') . '.csv';
        
        $stream = fopen('php://temp', 'w+');
        
        // Add UTF-8 BOM for Excel compliance
        fprintf($stream, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Header
        fputcsv($stream, ['origin', 'destination', 'bus_name', 'departure_time', 'arrival_time', 'price', 'status', 'driver_1', 'driver_2', 'conductor']);
        
        foreach ($schedules as $sched) {
            fputcsv($stream, [
                $sched['origin'],
                $sched['destination'],
                $sched['bus_name'],
                $sched['departure_time'],
                $sched['arrival_time'],
                $sched['price'],
                $sched['status'],
                $sched['driver_1_name'] ?? '',
                $sched['driver_2_name'] ?? '',
                $sched['conductor_name'] ?? ''
            ]);
        }
        
        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);
        
        return $this->response->download($filename, $csvContent)->setContentType('text/csv');
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
        if (!$headers || count($headers) < 7) {
            fclose($handle);
            return redirect()->back()->with('error', 'Format CSV tidak valid. Harus memiliki setidaknya header: origin, destination, bus_name, departure_time, arrival_time, price, status');
        }
        
        // Clean headers (remove BOM or spaces)
        $headers = array_map(function($h) {
            return trim(strtolower(preg_replace('/[\x{FEFF}\x{200B}-\x{200D}]/u', '', $h)));
        }, $headers);
        
        // Find indices
        $originIdx = array_search('origin', $headers);
        $destinationIdx = array_search('destination', $headers);
        $busNameIdx = array_search('bus_name', $headers);
        $departureIdx = array_search('departure_time', $headers);
        $arrivalIdx = array_search('arrival_time', $headers);
        $priceIdx = array_search('price', $headers);
        $statusIdx = array_search('status', $headers);
        $driver1Idx = array_search('driver_1', $headers);
        $driver2Idx = array_search('driver_2', $headers);
        $conductorIdx = array_search('conductor', $headers);
        
        if ($originIdx === false || $destinationIdx === false || $busNameIdx === false || 
            $departureIdx === false || $arrivalIdx === false || $priceIdx === false || $statusIdx === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'Kolom header tidak sesuai. Pastikan ada kolom: origin, destination, bus_name, departure_time, arrival_time, price, status');
        }
        
        // Cache routes and buses to optimize lookup speed
        $routes = $this->routeModel->findAll();
        $routeMap = [];
        foreach ($routes as $r) {
            $routeMap[strtolower($r['origin']) . '-' . strtolower($r['destination'])] = $r['id'];
        }
        
        $buses = $this->busModel->findAll();
        $busMap = [];
        foreach ($buses as $b) {
            $busMap[strtolower($b['name'])] = $b['id'];
        }

        // Cache all crews by name and role to map in import
        $userModel = new \App\Models\UserModel();
        $allCrews = $userModel->where('role', 'petugas')->findAll();
        $crewMapByName = [];
        foreach ($allCrews as $c) {
            $crewMapByName[strtolower($c['name'])][$c['crew_role']] = $c['id'];
        }
        
        $rowNum = 1;
        $errors = [];
        $importData = [];
        
        // Cache existing schedules to prevent double scheduling a bus at the same time
        $existingSchedules = $this->scheduleModel->findAll();
        $existingKeys = [];
        foreach ($existingSchedules as $s) {
            $existingKeys[] = $s['bus_id'] . '-' . strtolower($s['departure_time']);
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
            $busName = isset($row[$busNameIdx]) ? trim($row[$busNameIdx]) : '';
            $departureTime = isset($row[$departureIdx]) ? trim($row[$departureIdx]) : '';
            $arrivalTime = isset($row[$arrivalIdx]) ? trim($row[$arrivalIdx]) : '';
            $price = isset($row[$priceIdx]) ? trim($row[$priceIdx]) : '';
            $status = isset($row[$statusIdx]) ? trim(strtolower($row[$statusIdx])) : '';
            $driver_1 = ($driver1Idx !== false && isset($row[$driver1Idx])) ? trim($row[$driver1Idx]) : '';
            $driver_2 = ($driver2Idx !== false && isset($row[$driver2Idx])) ? trim($row[$driver2Idx]) : '';
            $conductor = ($conductorIdx !== false && isset($row[$conductorIdx])) ? trim($row[$conductorIdx]) : '';
            
            // Validations
            if (empty($origin) || empty($destination) || empty($busName) || empty($departureTime) || empty($arrivalTime) || empty($price) || empty($status)) {
                $errors[] = "Baris {$rowNum}: Data tidak lengkap (semua kolom utama harus diisi).";
                continue;
            }
            
            // Check route
            $routeKey = strtolower($origin) . '-' . strtolower($destination);
            if (!isset($routeMap[$routeKey])) {
                $errors[] = "Baris {$rowNum}: Rute dari '{$origin}' ke '{$destination}' tidak terdaftar.";
                continue;
            }
            $routeId = $routeMap[$routeKey];
            
            // Check bus
            $busKey = strtolower($busName);
            if (!isset($busMap[$busKey])) {
                $errors[] = "Baris {$rowNum}: Bus '{$busName}' tidak terdaftar.";
                continue;
            }
            $busId = $busMap[$busKey];
            
            // Format check for dates
            $depTimestamp = strtotime($departureTime);
            $arrTimestamp = strtotime($arrivalTime);
            if (!$depTimestamp) {
                $errors[] = "Baris {$rowNum}: Waktu keberangkatan '{$departureTime}' tidak valid.";
                continue;
            }
            if (!$arrTimestamp) {
                $errors[] = "Baris {$rowNum}: Waktu kedatangan '{$arrivalTime}' tidak valid.";
                continue;
            }
            if ($arrTimestamp <= $depTimestamp) {
                $errors[] = "Baris {$rowNum}: Waktu kedatangan harus setelah waktu keberangkatan.";
                continue;
            }
            
            // Price check
            if (!is_numeric($price) || (float)$price <= 0) {
                $errors[] = "Baris {$rowNum}: Harga tiket '{$price}' tidak valid.";
                continue;
            }
            
            // Status check
            if (!in_array($status, ['scheduled', 'ongoing', 'completed', 'cancelled'])) {
                $errors[] = "Baris {$rowNum}: Status '{$status}' tidak valid (harus scheduled, ongoing, completed, atau cancelled).";
                continue;
            }

            // Crew validation and mapping
            $d1_id = null;
            if (!empty($driver_1)) {
                $dKey = strtolower($driver_1);
                if (isset($crewMapByName[$dKey]['driver_1'])) {
                    $d1_id = $crewMapByName[$dKey]['driver_1'];
                } else {
                    $errors[] = "Baris {$rowNum}: Sopir Utama '{$driver_1}' tidak terdaftar atau perannya tidak sesuai.";
                    continue;
                }
            }

            $d2_id = null;
            if (!empty($driver_2)) {
                $dKey = strtolower($driver_2);
                if (isset($crewMapByName[$dKey]['driver_2'])) {
                    $d2_id = $crewMapByName[$dKey]['driver_2'];
                } else {
                    $errors[] = "Baris {$rowNum}: Sopir Cadangan '{$driver_2}' tidak terdaftar atau perannya tidak sesuai.";
                    continue;
                }
            }

            $cond_id = null;
            if (!empty($conductor)) {
                $cKey = strtolower($conductor);
                if (isset($crewMapByName[$cKey]['conductor'])) {
                    $cond_id = $crewMapByName[$cKey]['conductor'];
                } else {
                    $errors[] = "Baris {$rowNum}: Kondektur '{$conductor}' tidak terdaftar atau perannya tidak sesuai.";
                    continue;
                }
            }
            
            // Duplicate check (same bus at same departure time)
            $formattedDepTime = date('Y-m-d H:i:s', $depTimestamp);
            $formattedArrTime = date('Y-m-d H:i:s', $arrTimestamp);
            
            $schedKey = $busId . '-' . strtolower($formattedDepTime);
            if (in_array($schedKey, $existingKeys) || in_array($schedKey, $seenKeys)) {
                $errors[] = "Baris {$rowNum}: Jadwal untuk Bus '{$busName}' pada waktu {$formattedDepTime} sudah terdaftar atau duplikat di file.";
                continue;
            }
            
            $seenKeys[] = $schedKey;
            
            $importData[] = [
                'route_id'       => $routeId,
                'bus_id'         => $busId,
                'departure_time' => $formattedDepTime,
                'arrival_time'   => $formattedArrTime,
                'price'          => (float)$price,
                'status'         => $status,
                'driver_1_id'    => $d1_id,
                'driver_2_id'    => $d2_id,
                'conductor_id'   => $cond_id,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];
        }
        
        fclose($handle);
        
        if (!empty($errors)) {
            return redirect()->back()->with('errors', $errors);
        }
        
        if (empty($importData)) {
            return redirect()->back()->with('error', 'Tidak ada data valid yang diimport.');
        }
        
        $this->scheduleModel->insertBatch($importData);
        
        return redirect()->to(base_url('admin/schedule'))->with('success', count($importData) . ' Jadwal keberangkatan berhasil diimport.');
    }

    /**
     * Prints the Surat Jalan / Passenger Manifest for a specific schedule.
     * Accessible via: GET /admin/schedule/manifest/(:num)
     */
    public function manifest($id)
    {
        $schedule = $this->scheduleModel->getDetailedSchedules($id);
        if (!$schedule) {
            return redirect()->to(base_url('admin/schedule'))->with('error', 'Jadwal tidak ditemukan.');
        }

        // Get list of booked tickets that are checked in / boarded
        $db = \Config\Database::connect();
        
        // We select bookings for this schedule that are confirmed/completed (not cancelled)
        $passengers = $db->table('booking_seats')
            ->select('booking_seats.seat_number, booking_seats.passenger_name, bookings.booking_code, users.phone as passenger_phone, tickets.status as boarding_status')
            ->join('bookings', 'bookings.id = booking_seats.booking_id')
            ->join('users', 'users.id = bookings.user_id')
            ->join('tickets', 'tickets.booking_id = bookings.id', 'left')
            ->where('bookings.schedule_id', $id)
            ->where('bookings.booking_status !=', 'cancelled')
            ->orderBy('CAST(booking_seats.seat_number AS UNSIGNED)', 'ASC')
            ->orderBy('booking_seats.seat_number', 'ASC')
            ->get()->getResultArray();

        return view('admin/schedule/manifest', [
            'title'      => 'Surat Jalan & Manifes Penumpang — SiTeBus',
            'schedule'   => $schedule,
            'passengers' => $passengers
        ]);
    }
}
