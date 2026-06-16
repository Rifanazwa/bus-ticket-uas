<?php
define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootConsole($paths);

$db = \Config\Database::connect();

$code = 'TKT-B2E50CC12813';
echo "Searching for Ticket Code: '$code'\n";

// Search in tickets
$ticket = $db->table('tickets')
    ->select('tickets.*, bookings.booking_code, bookings.payment_status, bookings.booking_status, bookings.schedule_id')
    ->join('bookings', 'bookings.id = tickets.booking_id', 'left')
    ->where('tickets.qr_code', $code)
    ->get()
    ->getRowArray();

if ($ticket) {
    echo "Ticket Found in Database:\n";
    print_r($ticket);
    
    // Check schedule details
    $schedule = $db->table('schedules')
        ->select('schedules.*, buses.name as bus_name, buses.type as bus_type')
        ->join('buses', 'buses.id = schedules.bus_id', 'left')
        ->where('schedules.id', $ticket['schedule_id'])
        ->get()
        ->getRowArray();
        
    echo "\nSchedule Details:\n";
    print_r($schedule);
} else {
    echo "Ticket NOT found in database!\n";
    
    // List all tickets
    echo "\nAll tickets currently in database:\n";
    $allTickets = $db->table('tickets')->get()->getResultArray();
    print_r($allTickets);
}
