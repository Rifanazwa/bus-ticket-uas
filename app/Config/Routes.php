<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public Landing Page Routes (No Auth Required)
$routes->get('/', 'Public\LandingPage::index');
$routes->get('search', 'Public\LandingPage::search');

// Authentication Routes
$routes->get('login', 'Auth\Auth::login');
$routes->post('login', 'Auth\Auth::attemptLogin');
$routes->get('register', 'Auth\Auth::register');
$routes->post('register', 'Auth\Auth::attemptRegister');
$routes->get('logout', 'Auth\Auth::logout');

// Public Webhook Route (Exempted from Auth)
$routes->post('api/payment/webhook', 'Api\PaymentWebhook::index');

// Customer Routes (Protected)
$routes->group('customer', ['filter' => 'role:customer'], function($routes) {
    $routes->get('home', 'Customer\Home::index');
    $routes->get('search', 'Customer\Search::index');
    $routes->get('booking/create/(:num)', 'Customer\Booking::create/$1');
    $routes->post('booking/store', 'Customer\Booking::store');
    $routes->get('payment/(:num)', 'Customer\Payment::index/$1');
    $routes->get('payment/success', 'Customer\Payment::success');
    $routes->get('ticket/download/(:num)', 'Customer\Ticket::download/$1');
    $routes->post('chatbot/send', 'Customer\Chatbot::send');
    $routes->post('review/store', 'Customer\Review::store');
    $routes->post('promo/check', 'Customer\Booking::checkPromo');
});

// Admin Routes (Protected)
$routes->group('admin', ['filter' => 'role:admin'], function($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('dashboard/stats', 'Admin\Dashboard::stats');

    // Bus CRUD
    $routes->get('bus', 'Admin\Bus::index');
    $routes->get('bus/export', 'Admin\Bus::export');
    $routes->get('bus/template', 'Admin\Bus::template');
    $routes->post('bus/import', 'Admin\Bus::import');
    $routes->get('bus/create', 'Admin\Bus::create');
    $routes->post('bus/store', 'Admin\Bus::store');
    $routes->get('bus/edit/(:num)', 'Admin\Bus::edit/$1');
    $routes->post('bus/update/(:num)', 'Admin\Bus::update/$1');
    $routes->get('bus/delete/(:num)', 'Admin\Bus::delete/$1');

    // Route CRUD
    $routes->get('route', 'Admin\Route::index');
    $routes->get('route/export', 'Admin\Route::export');
    $routes->get('route/template', 'Admin\Route::template');
    $routes->post('route/import', 'Admin\Route::import');
    $routes->get('route/create', 'Admin\Route::create');
    $routes->post('route/store', 'Admin\Route::store');
    $routes->get('route/edit/(:num)', 'Admin\Route::edit/$1');
    $routes->post('route/update/(:num)', 'Admin\Route::update/$1');
    $routes->get('route/delete/(:num)', 'Admin\Route::delete/$1');

    // Officer CRUD
    $routes->get('officer', 'Admin\Officer::index');
    $routes->get('officer/export', 'Admin\Officer::export');
    $routes->get('officer/template', 'Admin\Officer::template');
    $routes->post('officer/import', 'Admin\Officer::import');
    $routes->get('officer/create', 'Admin\Officer::create');
    $routes->post('officer/store', 'Admin\Officer::store');
    $routes->get('officer/edit/(:num)', 'Admin\Officer::edit/$1');
    $routes->post('officer/update/(:num)', 'Admin\Officer::update/$1');
    $routes->get('officer/delete/(:num)', 'Admin\Officer::delete/$1');

    // Schedule CRUD
    $routes->get('schedule', 'Admin\Schedule::index');
    $routes->get('schedule/create', 'Admin\Schedule::create');
    $routes->post('schedule/store', 'Admin\Schedule::store');
    $routes->get('schedule/edit/(:num)', 'Admin\Schedule::edit/$1');
    $routes->post('schedule/update/(:num)', 'Admin\Schedule::update/$1');
    $routes->get('schedule/delete/(:num)', 'Admin\Schedule::delete/$1');

    // Promo CRUD
    $routes->get('promo', 'Admin\Promo::index');
    $routes->get('promo/create', 'Admin\Promo::create');
    $routes->post('promo/store', 'Admin\Promo::store');
    $routes->get('promo/edit/(:num)', 'Admin\Route::edit/$1');
    $routes->post('promo/update/(:num)', 'Admin\Promo::update/$1');
    $routes->get('promo/delete/(:num)', 'Admin\Promo::delete/$1');
});

// Petugas Routes (Protected)
$routes->group('petugas', ['filter' => 'role:petugas'], function($routes) {
    $routes->get('scan', 'Petugas\Scan::index');
    $routes->post('scan/verify', 'Petugas\Scan::verify');
    $routes->post('scan/confirm', 'Petugas\Scan::confirmBoarding');
});
