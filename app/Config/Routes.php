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

    // Tambahkan rute lain yang dilindungi di sini jika ada
    // Contoh: $routes->get('/dashboard', 'DashboardController::index');
});


// Rute yang tidak dilindungi bisa diletakkan di luar grup
// Contoh:
// $routes->get('/about', 'PageController::about');
