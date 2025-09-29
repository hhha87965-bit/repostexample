<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Excel Upload Routes
$routes->group('excelupload', function($routes) {
    $routes->get('/', 'ExcelUploadController::index');
    $routes->post('upload', 'ExcelUploadController::upload');
    $routes->get('files', 'ExcelUploadController::files');
    $routes->get('view/(:num)', 'ExcelUploadController::view/$1');
    $routes->post('addrow/(:num)', 'ExcelUploadController::addRow/$1');
    $routes->post('editrow/(:num)', 'ExcelUploadController::editRow/$1');
    $routes->get('deleterow/(:num)', 'ExcelUploadController::deleteRow/$1');
    $routes->get('delete/(:num)', 'ExcelUploadController::delete/$1');
    $routes->get('download/(:num)', 'ExcelUploadController::download/$1');
    $routes->get('export-excel/(:num)', 'ExcelUploadController::exportExcel/$1');
    $routes->get('export-pdf/(:num)', 'ExcelUploadController::exportPdf/$1');
    $routes->get('logs', 'ExcelUploadController::logs');
    $routes->get('logs/(:num)', 'ExcelUploadController::logs/$1');
});