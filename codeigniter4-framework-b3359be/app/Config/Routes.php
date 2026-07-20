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

$routes->get('prefixe', 'PrefixeController::index');
$routes->post('prefixe/store', 'PrefixeController::store');

// Routes pour les opérations 
$routes->get('operateur/config', 'OperateurController::index');
$routes->post('operateur/addPrefixe', 'OperateurController::addPrefixe');
$routes->post('operateur/addOperation', 'OperateurController::addOperation');
$routes->post('operateur/addFrai', 'OperateurController::addFrai');