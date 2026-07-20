<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/etudiants', 'EtudiantController::lister');
$routes->get('/login', 'UserController::index');
$routes->post('/login', 'UserController::login');
$routes->get('/logout', 'UserController::logout');
$routes->get('/dashboard', 'DashboardController::index'); // Crée cette page pour ton accueil

