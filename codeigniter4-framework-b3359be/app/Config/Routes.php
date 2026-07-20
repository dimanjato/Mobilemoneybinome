<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group('/client', ['filter' => 'auth'], function($routes) {
$routes->get('/voirsolde','SoldController::actuel');
});
$routes->get('/etudiants', 'EtudiantController::lister');

