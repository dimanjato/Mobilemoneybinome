<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/solde', 'SoldController::actuel');
$routes->get('/etudiants', 'EtudiantController::lister');

