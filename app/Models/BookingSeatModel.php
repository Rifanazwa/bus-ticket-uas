<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingSeatModel extends Model
{
    protected $table            = 'booking_seats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['booking_id', 'seat_number', 'passenger_name'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'booking_id'     => 'required|numeric',
        'seat_number'    => 'required|max_length[10]',
        'passenger_name' => 'required|max_length[100]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
