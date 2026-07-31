<?php
declare(strict_types=1);

session_start();

define('BASE_PATH', __DIR__);

require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/src/helpers.php';
require BASE_PATH . '/src/db.php';
require BASE_PATH . '/controllers/DashboardController.php';
require BASE_PATH . '/controllers/CompaniesController.php';
require BASE_PATH . '/controllers/ClientsController.php';
require BASE_PATH . '/controllers/PositionsController.php';
require BASE_PATH . '/controllers/ProposalsController.php';
require BASE_PATH . '/controllers/ExportController.php';

$base   = rtrim(config('app')['base'], '/');
$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$uri    = '/' . ltrim(str_starts_with($rawUri, $base) ? substr($rawUri, strlen($base)) : $rawUri, '/');
if ($uri === '') $uri = '/';

$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    ['GET',  '#^/$#',                               'dashboard_index'],
    // Dashboard
    ['GET',  '#^/dashboard$#',                        'dashboard_index'],
    // Clients
    ['GET',  '#^/clients$#',                          'clients_index'],
    ['GET',  '#^/clients/create$#',                   'clients_create'],
    ['POST', '#^/clients/create$#',                   'clients_store'],
    ['GET',  '#^/clients/(\d+)/edit$#',               'clients_edit'],
    ['POST', '#^/clients/(\d+)/edit$#',               'clients_update'],
    ['POST', '#^/clients/(\d+)/delete$#',             'clients_destroy'],
    // Companies
    ['GET',  '#^/companies$#',                      'companies_index'],
    ['GET',  '#^/companies/create$#',               'companies_create'],
    ['POST', '#^/companies/create$#',               'companies_store'],
    ['GET',  '#^/companies/(\d+)/edit$#',           'companies_edit'],
    ['POST', '#^/companies/(\d+)/edit$#',           'companies_update'],
    ['POST', '#^/companies/(\d+)/delete$#',         'companies_destroy'],
    // Positions
    ['GET',  '#^/positions$#',                      'positions_index'],
    ['GET',  '#^/positions/create$#',               'positions_create'],
    ['POST', '#^/positions/create$#',               'positions_store'],
    ['GET',  '#^/positions/(\d+)/edit$#',           'positions_edit'],
    ['POST', '#^/positions/(\d+)/edit$#',           'positions_update'],
    ['POST', '#^/positions/(\d+)/delete$#',         'positions_destroy'],
    // Rate card
    ['GET',  '#^/rate-card$#',                      'rate_card_index'],
    // Proposals
    ['GET',  '#^/proposals$#',                      'proposals_index'],
    ['GET',  '#^/proposals/create$#',               'proposals_create'],
    ['POST', '#^/proposals/create$#',               'proposals_store'],
    ['GET',  '#^/proposals/(\d+)$#',                'proposals_show'],
    ['GET',  '#^/proposals/(\d+)/pdf$#',            'proposals_pdf'],
    ['GET',  '#^/proposals/(\d+)/export/excel$#',   'proposals_excel'],
    ['POST', '#^/proposals/(\d+)/delete$#',         'proposals_destroy'],
    ['POST', '#^/proposals/(\d+)/status$#',         'proposals_status'],
    ['POST', '#^/proposals/(\d+)/clone$#',          'proposals_clone'],
    ['POST', '#^/proposals/(\d+)/version$#',        'proposals_version'],
];

$matched = false;
foreach ($routes as [$routeMethod, $pattern, $handler]) {
    if ($method !== $routeMethod) continue;
    if (!preg_match($pattern, $uri, $matches)) continue;
    $args = array_map('intval', array_slice($matches, 1));
    $handler(...$args);
    $matched = true;
    break;
}

if (!$matched) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:40px">
          <h2>404 — Page not found</h2>
          <a href="' . url('/positions') . '">Go to Positions</a>
          </body></html>';
}
