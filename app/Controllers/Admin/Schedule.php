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
        // Fetch schedules with joined bus and route details
        $schedules = $this->scheduleModel->getDetailedSchedules();

        return view('admin/schedule/index', [
            'title'     => 'Manajemen Jadwal - SiTeBus',
            'schedules' => $schedules
        ]);
    }

    public function create()
    {
        $routes = $this->routeModel->findAll();
        $buses  = $this->busModel->findAll();

        return view('admin/schedule/create', [
            'title'  => 'Tambah Jadwal Baru - SiTeBus',
            'routes' => $routes,
            'buses'  => $buses
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

        return view('admin/schedule/edit', [
            'title'    => 'Edit Jadwal - SiTeBus',
            'schedule' => $schedule,
            'routes'   => $routes,
            'buses'    => $buses
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
}
