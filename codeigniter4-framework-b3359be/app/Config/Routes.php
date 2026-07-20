<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group('/client', ['filter' => 'auth'], function($routes) {
    $routes->get('voirsolde', 'SoldController::actuel');
    $routes->get('depot', 'TransactionController::depot');
    $routes->post('depot', 'TransactionController::depot');
    $routes->get('retrait', 'TransactionController::retrait');
    $routes->post('retrait', 'TransactionController::retrait');
    $routes->get('transfert', 'TransactionController::transfert');
    $routes->post('transfert', 'TransactionController::transfert');
    $routes->get('historique', 'TransactionController::historique');
});
$routes->get('/login', 'UserController::index');
$routes->post('/login', 'UserController::login');
$routes->get('/logout', 'UserController::logout');

$routes->get('prefixe', 'PrefixeController::index');
$routes->post('prefixe/store', 'PrefixeController::store');

// Routes pour les opérations 
$routes->get('operateur/config', 'OperateurController::index');
$routes->post('operateur/addPrefixe', 'OperateurController::addPrefixe');
$routes->post('operateur/addOperation', 'OperateurController::addOperation');
$routes->post('operateur/addFrai', 'OperateurController::addFrai');