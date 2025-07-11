<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Auth Routes - harus di atas grup yang dilindungi agar bisa diakses
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::attemptLogin'); // Ini adalah rute POST untuk proses login
$routes->get('/logout', 'Auth::logout');

// Grup rute yang dilindungi autentikasi
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Category Routes
    $routes->resource('categories', ['controller' => 'CategoryController']);

    // Product Routes
    $routes->resource('products', ['controller' => 'ProductController']);

    // Transaction Routes
    // Using group for transactions and defining specific routes to match form actions
    $routes->get('transactions', 'TransactionController::index');
    $routes->get('transactions/new', 'TransactionController::new');
    $routes->post('transactions/create', 'TransactionController::create'); // Matches form post
    $routes->get('transactions/(:num)', 'TransactionController::show/$1');
    $routes->get('transactions/edit/(:num)', 'TransactionController::edit/$1'); // Though edit is disabled
    $routes->post('transactions/update/(:num)', 'TransactionController::update/$1'); // Though update is disabled
    $routes->post('transactions/delete/(:num)', 'TransactionController::delete/$1'); // Matches form post for delete

    // Tambahkan rute lain yang dilindungi di sini jika ada
    // Contoh: $routes->get('/dashboard', 'DashboardController::index');
});


// Rute yang tidak dilindungi bisa diletakkan di luar grup
// Contoh:
// $routes->get('/about', 'PageController::about');
