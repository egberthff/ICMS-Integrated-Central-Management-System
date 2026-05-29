<?php
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public Routes
$routes->get('/', 'PageController::login');
$routes->post('/login', 'AuthController::login', ['filter' => 'ratelimit']);

// UI Routes (Protected) - All handled by dynamic route
// $routes->get('/switch-role', 'PageController::switchRole');
// $routes->get('/dashboard', 'PageController::dashboard');
// $routes->get('/timesheet', 'PageController::timesheet');
// $routes->get('/payslip', 'PageController::handlePage'); // Will be handled dynamically

// $routes->get('/human-resource/add-new-employee', 'HumanResourceController::addNewEmployee');
// $routes->get('/human-resource/add-new-employee', 'PageController::addNewEmployee');

$routes->group('admin', ['filter' => 'rbac:admin|manage'], function ($routes) {
    $routes->get('admin/users', 'PageController::index/$1');
    $routes->get('admin/roles', 'PageController::index/$1');
    $routes->get('admin/permissions', 'PageController::index/$1');
});

$routes->get('user/(:segment)/roles', 'AdminController::getUserRoles/$1');

// Auth API Routes
$routes->post('/api/auth/switch-role', 'AuthController::switchRole', ['filter' => 'rbac']);

// Protected Payroll Routes
$routes->group('api/v1/payroll', ['filter' => 'rbac:employee|view_payslip'], function ($routes) {
    $routes->post('disburse', 'PayrollController::disburse');
    $routes->get('summary', 'PayrollController::getSummary');
    $routes->get('payslip/(:num)', 'PayrollController::viewPayslip/$1');
    $routes->get('payslips', 'PayrollController::listPayslips');
    $routes->get('latest-payslip', 'PayrollController::latestPayslip');
    $routes->post('calculate-payslip', 'PayrollAdminController::calculatePayslip');
});

// Protected Employee Routes
$routes->group('api/v1/employee', ['filter' => 'rbac:timesheet|submit'], function ($routes) {
    $routes->post('get-timesheet', 'EmployeeController::getTimesheet');
    $routes->post('submit-timesheet', 'EmployeeController::submitTimesheet');
    $routes->post('submit-hours', 'EmployeeController::submitHours');

    // Timesheet processing (employee view + preview only)
    $routes->post('timesheet-processing/timesheet', 'Employee\\TimesheetProcessingController::getTimesheetForPeriod');
    $routes->post('timesheet-processing/preview-payslip', 'Employee\\TimesheetProcessingController::previewPayslip');
    $routes->post('timesheet-processing/payslip', 'Employee\\TimesheetProcessingController::getPayslipForPeriod');
});


// Admin API Routes
$routes->group('api/v1/admin', ['filter' => 'rbac:admin|manage'], function ($routes) {
    // User Management
    $routes->get('users', 'AdminController::listUsers');
    $routes->post('users/create', 'AdminController::createUser');
    $routes->delete('users/(:segment)', 'AdminController::deleteUser/$1');
    $routes->get('users/(:segment)/roles', 'AdminController::getUserRoles/$1');

    // Role Management
    $routes->post('roles/save', 'AdminController::saveRole');
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

//Dynamic route for rendering UI pages
$routes->get('(:any)', 'PageController::index/$1');