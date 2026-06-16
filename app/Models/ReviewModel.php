<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table            = 'reviews';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['booking_id', 'user_id', 'rating', 'comment', 'sentiment'];

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
        'booking_id' => 'required|numeric',
        'user_id'    => 'required|numeric',
        'rating'     => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
        'comment'    => 'permit_empty|string',
        'sentiment'  => 'permit_empty|in_list[positive,neutral,negative]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Fetch reviews with customer and route details
    public function getDetailedReviews($routeId = null)
    {
        $builder = $this->select('reviews.*, users.name as customer_name, routes.origin, routes.destination, buses.name as bus_name')
            ->join('users', 'users.id = reviews.user_id')
            ->join('bookings', 'bookings.id = reviews.booking_id')
            ->join('schedules', 'schedules.id = bookings.schedule_id')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id');

        if ($routeId !== null) {
            $builder->where('routes.id', $routeId);
        }

        return $builder->orderBy('reviews.created_at', 'DESC')->findAll();
    }
}
