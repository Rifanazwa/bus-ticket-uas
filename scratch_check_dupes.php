<?php
define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootConsole($paths);

$db = \Config\Database::connect();
$query = $db->query("
    SELECT bs.seat_number, b.schedule_id, COUNT(*) as count 
    FROM booking_seats bs
    JOIN bookings b ON b.id = bs.booking_id
    WHERE b.booking_status != 'cancelled'
    GROUP BY bs.seat_number, b.schedule_id
    HAVING count > 1
");

$results = $query->getResultArray();
if (empty($results)) {
    echo "No duplicate active seats found in DB.\n";
} else {
    echo "Duplicate active seats found:\n";
    print_r($results);
}
