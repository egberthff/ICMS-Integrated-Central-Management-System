<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public Routes
$routes->get('/', 'PageController::login');
$routes->post('/login', 'AuthController::login');

// UI Routes (Protected)
$routes->get('/switch-role', 'PageController::switchRole');
$routes->get('/dashboard', 'PageController::dashboard');

// $routes->group('admin', ['filter' => 'rbac:admin|manage'], function($routes){
    $routes->get('admin/users', 'PageController::users');
    $routes->get('admin/roles', 'PageController::roles');
    $routes->get('admin/permissions', 'PageController::permissions');
// });
 
$routes->get('user/(:segment)/roles', 'AdminController::getUserRoles/$1');

// Auth API Routes
$routes->post('/api/auth/switch-role', 'AuthController::switchRole', ['filter' => 'rbac']);

// Protected Payroll Routes
$routes->group('api/v1/payroll', ['filter' => 'rbac:payroll|execute'], function($routes) {
    $routes->post('disburse', 'PayrollController::disburse');
    $routes->get('summary', 'PayrollController::getSummary');
});

// Protected Employee Routes
$routes->group('api/v1/employee', ['filter' => 'rbac:timesheet|submit'], function($routes) {
    $routes->post('submit-hours', 'EmployeeController::submitHours');
});

// Admin API Routes
$routes->group('api/v1/admin', ['filter' => 'rbac:admin|manage'], function($routes) {
    // User Management
    $routes->get('users', 'AdminController::listUsers');
    $routes->post('users/create', 'AdminController::createUser');
    $routes->delete('users/(:segment)', 'AdminController::deleteUser/$1');
    $routes->get('users/(:segment)/roles', 'AdminController::getUserRoles/$1');
    
    // Role Management
    $routes->post('roles/create', 'AdminController::createRole');
    $routes->get('roles', 'AdminController::listRoles');
    $routes->post('roles/assign', 'AdminController::assignRoleToUser');
    $routes->post('roles/revoke', 'AdminController::removeRoleFromUser');
    $routes->get('roles/(:segment)/permissions', 'AdminController::getRolePermissions/$1');
    
    // Permission Management
    $routes->post('permissions/create', 'AdminController::createPermission');
    $routes->get('permissions', 'AdminController::listPermissions');
    $routes->post('permissions/assign', 'AdminController::assignPermissionToRole');
    $routes->post('permissions/revoke', 'AdminController::removePermissionFromRole');
    $routes->delete('permissions/(:segment)', 'AdminController::deletePermission/$1');
});

