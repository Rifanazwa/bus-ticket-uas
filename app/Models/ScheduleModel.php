<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table            = 'schedules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['route_id', 'bus_id', 'departure_time', 'arrival_time', 'price', 'status', 'driver_1', 'driver_2', 'conductor'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'route_id'       => 'required|numeric',
        'bus_id'         => 'required|numeric',
        'departure_time' => 'required',
        'arrival_time'   => 'required',
        'price'          => 'required|decimal',
        'status'         => 'required|in_list[scheduled,ongoing,completed,cancelled]',
        'driver_1'       => 'permit_empty|max_length[100]',
        'driver_2'       => 'permit_empty|max_length[100]',
        'conductor'      => 'permit_empty|max_length[100]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Fetch schedule with route and bus details
    public function getDetailedSchedules($id = null)
    {
        $builder = $this->select('schedules.*, routes.origin, routes.destination, routes.distance_km, routes.estimated_duration, buses.name as bus_name, buses.type as bus_type, buses.total_seats, buses.seat_layout')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id');

        if ($id !== null) {
            return $builder->where('schedules.id', $id)->first();
        }

        return $builder->findAll();
    }

    /**
     * Dynamically project / clone baseline schedules from June 2026 to future dates.
     */
    public function checkAndGenerateSchedulesForDate(string $date)
    {
        // Only generate for dates after June 30, 2026
        if ($date <= '2026-06-30') {
            return;
        }

        // Check if schedules already exist for this date in the database
        $count = $this->where('DATE(departure_time)', $date)->countAllResults();
        if ($count > 0) {
            return;
        }

        // Determine day of the week (1 = Monday, 7 = Sunday)
        $dayOfWeek = date('N', strtotime($date));

        // Source baseline date in June 2026 (June 1 is Monday, June 7 is Sunday)
        $sourceDate = '2026-06-0' . $dayOfWeek;

        // Retrieve baseline schedules for that day of the week
        $sourceSchedules = $this->where('DATE(departure_time)', $sourceDate)->findAll();
        if (empty($sourceSchedules)) {
            return;
        }

        $newSchedules = [];
        $now = date('Y-m-d H:i:s');

        foreach ($sourceSchedules as $sched) {
            $depTime = new \DateTime($sched['departure_time']);
            $arrTime = new \DateTime($sched['arrival_time']);
            
            // Calculate time difference for multi-day/overnight trips
            $diff = $depTime->diff($arrTime);
            
            // Construct new departure time with target date
            $newDepTime = new \DateTime($date . ' ' . $depTime->format('H:i:s'));
            
            // Construct new arrival time keeping duration difference
            $newArrTime = clone $newDepTime;
            $newArrTime->add($diff);

            $newSchedules[] = [
                'route_id'       => $sched['route_id'],
                'bus_id'         => $sched['bus_id'],
                'departure_time' => $newDepTime->format('Y-m-d H:i:s'),
                'arrival_time'   => $newArrTime->format('Y-m-d H:i:s'),
                'price'          => $sched['price'],
                'status'         => 'scheduled',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        if (!empty($newSchedules)) {
            $this->insertBatch($newSchedules);
        }
    }
}
