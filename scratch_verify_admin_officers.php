<?php
define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootConsole($paths);

$session = \Config\Services::session();
$session->set([
    'userId'     => 2,
    'userName'   => 'Admin Test',
    'userRole'   => 'admin',
    'isLoggedIn' => true
]);

echo "Running Verification Tests with Mock IncomingRequest...\n";

// Mock IncomingRequest
$config = new \Config\App();
$uri = new \CodeIgniter\HTTP\URI('http://localhost:8080/admin/officer');
$request = new \CodeIgniter\HTTP\IncomingRequest($config, $uri, null, new \CodeIgniter\HTTP\UserAgent());
\Config\Services::injectMock('request', $request);

// 1. Verify Officer Controller
try {
    $controller = new \App\Controllers\Admin\Officer();
    $controller->initController(
        $request,
        \Config\Services::response(),
        \Config\Services::logger()
    );
    ob_start();
    $response = $controller->index();
    $body = ob_get_clean();

    if ($response instanceof \CodeIgniter\HTTP\ResponseInterface) {
        $body = $response->getBody();
    } else {
        $body = (string)$response;
    }

    if (strpos($body, 'Manajemen Petugas') !== false && strpos($body, 'Template CSV') !== false) {
        echo "SUCCESS: Admin Officer Index view loaded correctly.\n";
    } else {
        echo "FAILED: Admin Officer Index view did not load correctly.\n";
        echo "Body snippet: " . substr($body, 0, 300) . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR in Officer Controller: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// 2. Verify Promo Check Controller Route
try {
    $bookingController = new \App\Controllers\Customer\Booking();
    
    // Create request with JSON body
    $promoUri = new \CodeIgniter\HTTP\URI('http://localhost:8080/customer/promo/check');
    $promoRequest = new \CodeIgniter\HTTP\IncomingRequest($config, $promoUri, null, new \CodeIgniter\HTTP\UserAgent());
    $promoRequest->setBody(json_encode([
        'promo_code' => 'MUDIKASIK',
        'total_price' => 100000
    ]));
    $promoRequest->setHeader('Content-Type', 'application/json');
    \Config\Services::injectMock('request', $promoRequest);
    
    $bookingController->initController(
        $promoRequest,
        \Config\Services::response(),
        \Config\Services::logger()
    );
    
    $response = $bookingController->checkPromo();
    $body = $response->getBody();
    
    $data = json_decode($body, true);
    if ($data && isset($data['valid']) && $data['valid'] === true && $data['discount_amount'] == 10000) {
        echo "SUCCESS: Promo check AJAX endpoint works (MUDIKASIK returns 10% discount).\n";
    } else {
        echo "FAILED: Promo check AJAX endpoint did not return correct response.\n";
        echo "Response body: " . $body . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR in Promo Check Endpoint: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
