<?php

use App\Http\Controllers\AccountsReceivableController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CatalogItemController;
use App\Http\Controllers\CctvController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SITIO WEB PÚBLICO
|--------------------------------------------------------------------------
|
| Mientras no exista PublicWebsiteController se seguirá mostrando la
| pantalla Welcome de Laravel.
|
| Cuando construyamos el sitio web público, estas rutas se activarán
| automáticamente sin modificar nuevamente este archivo.
|
*/

if (class_exists(PublicWebsiteController::class)) {
    Route::get('/', [PublicWebsiteController::class, 'home'])
        ->name('home');

    Route::get('/servicios', [PublicWebsiteController::class, 'services'])
        ->name('public.services');

    Route::get('/portafolio', [PublicWebsiteController::class, 'portfolio'])
        ->name('public.portfolio');

    Route::get('/contacto', [PublicWebsiteController::class, 'contact'])
        ->name('public.contact');
} else {
    Route::inertia('/', 'Welcome')
        ->name('home');
}

/*
|--------------------------------------------------------------------------
| SISTEMA ADMINISTRATIVO
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    if (class_exists(DashboardController::class)) {
        Route::get(
            'dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');
    } else {
        Route::inertia('dashboard', 'Dashboard')
            ->name('dashboard');
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLEADOS
    |--------------------------------------------------------------------------
    */

    Route::get(
        'employees',
        [EmployeeController::class, 'index']
    )->name('employees.index');

    Route::get(
        'employees/create',
        [EmployeeController::class, 'create']
    )->name('employees.create');

    Route::post(
        'employees',
        [EmployeeController::class, 'store']
    )->name('employees.store');

    Route::get(
        'employees/{employee}/edit',
        [EmployeeController::class, 'edit']
    )->name('employees.edit');

    Route::patch(
        'employees/{employee}',
        [EmployeeController::class, 'update']
    )->name('employees.update');

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */

    Route::get(
        'clients',
        [ClientController::class, 'index']
    )->name('clients.index');

    Route::get(
        'clients/create',
        [ClientController::class, 'create']
    )->name('clients.create');

    Route::post(
        'clients',
        [ClientController::class, 'store']
    )->name('clients.store');

    Route::get(
        'clients/{client}/edit',
        [ClientController::class, 'edit']
    )->name('clients.edit');

    Route::patch(
        'clients/{client}',
        [ClientController::class, 'update']
    )->name('clients.update');

    /*
    |--------------------------------------------------------------------------
    | CATÁLOGO
    |--------------------------------------------------------------------------
    */

    Route::get(
        'catalog',
        [CatalogItemController::class, 'index']
    )->name('catalog.index');

    Route::get(
        'catalog/create',
        [CatalogItemController::class, 'create']
    )->name('catalog.create');

    Route::post(
        'catalog',
        [CatalogItemController::class, 'store']
    )->name('catalog.store');

    Route::get(
        'catalog/{catalogItem}/edit',
        [CatalogItemController::class, 'edit']
    )->name('catalog.edit');

    Route::patch(
        'catalog/{catalogItem}',
        [CatalogItemController::class, 'update']
    )->name('catalog.update');

    /*
    |--------------------------------------------------------------------------
    | INVENTARIO
    |--------------------------------------------------------------------------
    */

    if (class_exists(InventoryController::class)) {
        Route::get(
            'inventory',
            [InventoryController::class, 'index']
        )->name('inventory.index');

        Route::get(
            'inventory/movements',
            [InventoryController::class, 'movements']
        )->name('inventory.movements');

        Route::get(
            'inventory/create',
            [InventoryController::class, 'create']
        )->name('inventory.create');

        Route::post(
            'inventory',
            [InventoryController::class, 'store']
        )->name('inventory.store');

        Route::post(
            'inventory/adjustments',
            [InventoryController::class, 'storeAdjustment']
        )->name('inventory.adjustments.store');

        Route::get(
            'inventory/{inventoryItem}',
            [InventoryController::class, 'show']
        )->name('inventory.show');

        Route::get(
            'inventory/{inventoryItem}/edit',
            [InventoryController::class, 'edit']
        )->name('inventory.edit');

        Route::patch(
            'inventory/{inventoryItem}',
            [InventoryController::class, 'update']
        )->name('inventory.update');

        Route::get(
            'inventory/{inventoryItem}/movements',
            [InventoryController::class, 'itemMovements']
        )->name('inventory.item-movements');
    }

    /*
    |--------------------------------------------------------------------------
    | COTIZACIONES
    |--------------------------------------------------------------------------
    */

    Route::get(
        'quotations',
        [QuotationController::class, 'index']
    )->name('quotations.index');

    Route::get(
        'quotations/create',
        [QuotationController::class, 'create']
    )->name('quotations.create');

    Route::post(
        'quotations',
        [QuotationController::class, 'store']
    )->name('quotations.store');

    Route::get(
        'quotations/{quotation}',
        [QuotationController::class, 'show']
    )->name('quotations.show');

    Route::get(
        'quotations/{quotation}/edit',
        [QuotationController::class, 'edit']
    )->name('quotations.edit');

    Route::patch(
        'quotations/{quotation}',
        [QuotationController::class, 'update']
    )->name('quotations.update');

    Route::patch(
        'quotations/{quotation}/status',
        [QuotationController::class, 'updateStatus']
    )->name('quotations.status');

    Route::post(
        'quotations/{quotation}/convert-to-sale',
        [SaleController::class, 'convertFromQuotation']
    )->name('quotations.convert-to-sale');

    /*
    |--------------------------------------------------------------------------
    | VENTAS
    |--------------------------------------------------------------------------
    */

    Route::get(
        'sales',
        [SaleController::class, 'index']
    )->name('sales.index');

    Route::get(
        'sales/create',
        [SaleController::class, 'create']
    )->name('sales.create');

    Route::post(
        'sales',
        [SaleController::class, 'store']
    )->name('sales.store');

    Route::get(
        'sales/{sale}',
        [SaleController::class, 'show']
    )->name('sales.show');

    Route::post(
        'sales/{sale}/payments',
        [SaleController::class, 'addPayment']
    )->name('sales.payments.store');

    Route::patch(
        'sales/{sale}/cancel',
        [SaleController::class, 'cancel']
    )->name('sales.cancel');

    /*
    |--------------------------------------------------------------------------
    | RECIBOS
    |--------------------------------------------------------------------------
    */

    if (class_exists(ReceiptController::class)) {
        Route::get(
            'receipts',
            [ReceiptController::class, 'index']
        )->name('receipts.index');

        Route::get(
            'receipts/{salePayment}',
            [ReceiptController::class, 'show']
        )->name('receipts.show');

        Route::get(
            'receipts/{salePayment}/print',
            [ReceiptController::class, 'print']
        )->name('receipts.print');
    }

    /*
    |--------------------------------------------------------------------------
    | CUENTAS POR COBRAR
    |--------------------------------------------------------------------------
    */

    if (class_exists(AccountsReceivableController::class)) {
        Route::get(
            'accounts-receivable',
            [AccountsReceivableController::class, 'index']
        )->name('accounts-receivable.index');

        Route::get(
            'accounts-receivable/{sale}',
            [AccountsReceivableController::class, 'show']
        )->name('accounts-receivable.show');

        Route::post(
            'accounts-receivable/{sale}/payments',
            [AccountsReceivableController::class, 'storePayment']
        )->name('accounts-receivable.payments.store');
    }

    /*
    |--------------------------------------------------------------------------
    | ÓRDENES DE TRABAJO
    |--------------------------------------------------------------------------
    */

    if (class_exists(WorkOrderController::class)) {
        Route::get(
            'work-orders',
            [WorkOrderController::class, 'index']
        )->name('work-orders.index');

        Route::get(
            'work-orders/create',
            [WorkOrderController::class, 'create']
        )->name('work-orders.create');

        Route::post(
            'work-orders',
            [WorkOrderController::class, 'store']
        )->name('work-orders.store');

        Route::get(
            'work-orders/{workOrder}',
            [WorkOrderController::class, 'show']
        )->name('work-orders.show');

        Route::get(
            'work-orders/{workOrder}/edit',
            [WorkOrderController::class, 'edit']
        )->name('work-orders.edit');

        Route::patch(
            'work-orders/{workOrder}',
            [WorkOrderController::class, 'update']
        )->name('work-orders.update');

        Route::patch(
            'work-orders/{workOrder}/status',
            [WorkOrderController::class, 'updateStatus']
        )->name('work-orders.status');
    }

    /*
    |--------------------------------------------------------------------------
    | TAREAS
    |--------------------------------------------------------------------------
    */

    if (class_exists(TaskController::class)) {
        Route::get(
            'tasks',
            [TaskController::class, 'index']
        )->name('tasks.index');

        Route::get(
            'tasks/create',
            [TaskController::class, 'create']
        )->name('tasks.create');

        Route::post(
            'tasks',
            [TaskController::class, 'store']
        )->name('tasks.store');

        Route::get(
            'tasks/{task}',
            [TaskController::class, 'show']
        )->name('tasks.show');

        Route::get(
            'tasks/{task}/edit',
            [TaskController::class, 'edit']
        )->name('tasks.edit');

        Route::patch(
            'tasks/{task}',
            [TaskController::class, 'update']
        )->name('tasks.update');

        Route::patch(
            'tasks/{task}/start',
            [TaskController::class, 'start']
        )->name('tasks.start');

        Route::patch(
            'tasks/{task}/pause',
            [TaskController::class, 'pause']
        )->name('tasks.pause');

        Route::patch(
            'tasks/{task}/resume',
            [TaskController::class, 'resume']
        )->name('tasks.resume');

        Route::patch(
            'tasks/{task}/finish',
            [TaskController::class, 'finish']
        )->name('tasks.finish');

        Route::patch(
            'tasks/{task}/send-for-review',
            [TaskController::class, 'sendForReview']
        )->name('tasks.send-for-review');

        Route::patch(
            'tasks/{task}/report-issue',
            [TaskController::class, 'reportIssue']
        )->name('tasks.report-issue');
    }

    /*
    |--------------------------------------------------------------------------
    | INSTALACIONES
    |--------------------------------------------------------------------------
    */

    if (class_exists(InstallationController::class)) {
        Route::get(
            'installations',
            [InstallationController::class, 'index']
        )->name('installations.index');

        Route::get(
            'installations/create',
            [InstallationController::class, 'create']
        )->name('installations.create');

        Route::post(
            'installations',
            [InstallationController::class, 'store']
        )->name('installations.store');

        Route::get(
            'installations/{installation}',
            [InstallationController::class, 'show']
        )->name('installations.show');

        Route::get(
            'installations/{installation}/edit',
            [InstallationController::class, 'edit']
        )->name('installations.edit');

        Route::patch(
            'installations/{installation}',
            [InstallationController::class, 'update']
        )->name('installations.update');

        Route::patch(
            'installations/{installation}/status',
            [InstallationController::class, 'updateStatus']
        )->name('installations.status');
    }

    /*
    |--------------------------------------------------------------------------
    | CCTV / CÁMARAS DE SEGURIDAD
    |--------------------------------------------------------------------------
    */

    if (class_exists(CctvController::class)) {
        Route::get(
            'cctv',
            [CctvController::class, 'index']
        )->name('cctv.index');

        Route::get(
            'cctv/create',
            [CctvController::class, 'create']
        )->name('cctv.create');

        Route::post(
            'cctv',
            [CctvController::class, 'store']
        )->name('cctv.store');

        Route::get(
            'cctv/{cctvJob}',
            [CctvController::class, 'show']
        )->name('cctv.show');

        Route::get(
            'cctv/{cctvJob}/edit',
            [CctvController::class, 'edit']
        )->name('cctv.edit');

        Route::patch(
            'cctv/{cctvJob}',
            [CctvController::class, 'update']
        )->name('cctv.update');

        Route::patch(
            'cctv/{cctvJob}/status',
            [CctvController::class, 'updateStatus']
        )->name('cctv.status');

        Route::post(
            'cctv/{cctvJob}/visits',
            [CctvController::class, 'storeVisit']
        )->name('cctv.visits.store');
    }

    /*
    |--------------------------------------------------------------------------
    | CAJA
    |--------------------------------------------------------------------------
    */

    if (class_exists(CashController::class)) {
        Route::get(
            'cash',
            [CashController::class, 'index']
        )->name('cash.index');

        Route::get(
            'cash/history',
            [CashController::class, 'history']
        )->name('cash.history');

        Route::post(
            'cash/open',
            [CashController::class, 'open']
        )->name('cash.open');

        Route::post(
            'cash/close',
            [CashController::class, 'close']
        )->name('cash.close');

        Route::post(
            'cash/movements',
            [CashController::class, 'storeMovement']
        )->name('cash.movements.store');
    }

    /*
    |--------------------------------------------------------------------------
    | OTROS INGRESOS
    |--------------------------------------------------------------------------
    */

    if (class_exists(IncomeController::class)) {
        Route::get(
            'income',
            [IncomeController::class, 'index']
        )->name('income.index');

        Route::get(
            'income/create',
            [IncomeController::class, 'create']
        )->name('income.create');

        Route::post(
            'income',
            [IncomeController::class, 'store']
        )->name('income.store');

        Route::get(
            'income/{income}/edit',
            [IncomeController::class, 'edit']
        )->name('income.edit');

        Route::patch(
            'income/{income}',
            [IncomeController::class, 'update']
        )->name('income.update');
    }

    /*
    |--------------------------------------------------------------------------
    | GASTOS
    |--------------------------------------------------------------------------
    */

    if (class_exists(ExpenseController::class)) {
        Route::get(
            'expenses',
            [ExpenseController::class, 'index']
        )->name('expenses.index');

        Route::get(
            'expenses/create',
            [ExpenseController::class, 'create']
        )->name('expenses.create');

        Route::post(
            'expenses',
            [ExpenseController::class, 'store']
        )->name('expenses.store');

        Route::get(
            'expenses/{expense}/edit',
            [ExpenseController::class, 'edit']
        )->name('expenses.edit');

        Route::patch(
            'expenses/{expense}',
            [ExpenseController::class, 'update']
        )->name('expenses.update');
    }

    /*
    |--------------------------------------------------------------------------
    | REPORTES
    |--------------------------------------------------------------------------
    */

    if (class_exists(ReportController::class)) {
        Route::get(
            'reports',
            [ReportController::class, 'index']
        )->name('reports.index');

        Route::get(
            'reports/sales',
            [ReportController::class, 'sales']
        )->name('reports.sales');

        Route::get(
            'reports/cash-flow',
            [ReportController::class, 'cashFlow']
        )->name('reports.cash-flow');

        Route::get(
            'reports/profitability',
            [ReportController::class, 'profitability']
        )->name('reports.profitability');

        Route::get(
            'reports/accounts-receivable',
            [ReportController::class, 'accountsReceivable']
        )->name('reports.accounts-receivable');

        Route::get(
            'reports/production',
            [ReportController::class, 'production']
        )->name('reports.production');

        Route::get(
            'reports/inventory',
            [ReportController::class, 'inventory']
        )->name('reports.inventory');

        Route::get(
            'reports/{report}/export',
            [ReportController::class, 'export']
        )->name('reports.export');
    }

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRACIÓN DEL SITIO WEB
    |--------------------------------------------------------------------------
    */

    if (class_exists(WebsiteController::class)) {
        Route::get(
            'website',
            [WebsiteController::class, 'index']
        )->name('website.index');

        Route::patch(
            'website/settings',
            [WebsiteController::class, 'updateSettings']
        )->name('website.settings.update');

        Route::post(
            'website/content',
            [WebsiteController::class, 'storeContent']
        )->name('website.content.store');

        Route::patch(
            'website/content/{content}',
            [WebsiteController::class, 'updateContent']
        )->name('website.content.update');

        Route::delete(
            'website/content/{content}',
            [WebsiteController::class, 'destroyContent']
        )->name('website.content.destroy');
    }

    /*
    |--------------------------------------------------------------------------
    | USUARIOS, ROLES Y PERMISOS
    |--------------------------------------------------------------------------
    */

    if (class_exists(UserManagementController::class)) {
        Route::get(
            'users',
            [UserManagementController::class, 'index']
        )->name('users.index');

        Route::get(
            'users/create',
            [UserManagementController::class, 'create']
        )->name('users.create');

        Route::post(
            'users',
            [UserManagementController::class, 'store']
        )->name('users.store');

        Route::get(
            'users/permissions',
            [UserManagementController::class, 'permissions']
        )->name('users.permissions');

        Route::post(
            'users/roles',
            [UserManagementController::class, 'storeRole']
        )->name('users.roles.store');

        Route::patch(
            'users/roles/{role}',
            [UserManagementController::class, 'updateRole']
        )->name('users.roles.update');

        Route::delete(
            'users/roles/{role}',
            [UserManagementController::class, 'destroyRole']
        )->name('users.roles.destroy');

        Route::get(
            'users/{user}/edit',
            [UserManagementController::class, 'edit']
        )->name('users.edit');

        Route::patch(
            'users/{user}',
            [UserManagementController::class, 'update']
        )->name('users.update');

        Route::patch(
            'users/{user}/status',
            [UserManagementController::class, 'updateStatus']
        )->name('users.status');

        Route::post(
            'users/{user}/reset-password',
            [UserManagementController::class, 'resetPassword']
        )->name('users.reset-password');
    }

    /*
    |--------------------------------------------------------------------------
    | AUDITORÍA
    |--------------------------------------------------------------------------
    */

    if (class_exists(AuditController::class)) {
        Route::get(
            'audit',
            [AuditController::class, 'index']
        )->name('audit.index');

        Route::get(
            'audit/{auditLog}',
            [AuditController::class, 'show']
        )->name('audit.show');
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN GENERAL DEL SISTEMA
    |--------------------------------------------------------------------------
    */

    if (class_exists(SystemSettingController::class)) {
        Route::get(
            'settings/system',
            [SystemSettingController::class, 'index']
        )->name('system-settings.index');

        Route::patch(
            'settings/system',
            [SystemSettingController::class, 'update']
        )->name('system-settings.update');
    }
});

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN PERSONAL DEL USUARIO
|--------------------------------------------------------------------------
|
| Rutas originales del starter de Laravel:
| perfil, contraseña, apariencia, etc.
|
*/

require __DIR__.'/settings.php';