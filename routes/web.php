<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerCheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'tenant', 'credentials.changed'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant', 'credentials.changed'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    // Dashboard is the authenticated landing page. Module routes below retain
    // their existing permission middleware, so users without a module grant
    // cannot access that module but are never blocked from their landing page.
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    */

    // Tambah user
    Route::middleware('permission:users.create')->group(function () {

        Route::get('/users/create', [UserController::class, 'create'])
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');
    });

    // Approval must be registered before /users/{user}, otherwise the literal
    // "approvals" can be interpreted as a user route parameter.
    Route::middleware(['role:Atasan|Admin', 'permission:users.approve'])->group(function () {
        Route::get('/users/approvals', [UserApprovalController::class, 'index'])->name('users.approvals.index');
        Route::patch('/users/{user}/approve', [UserApprovalController::class, 'approve'])->name('users.approvals.approve');
        Route::patch('/users/{user}/reject', [UserApprovalController::class, 'reject'])->name('users.approvals.reject');
    });

    // Lihat user
    Route::middleware('permission:users.view')->group(function () {

        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/{user}', [UserController::class, 'show'])
            ->name('users.show');
    });

    // Edit user
    Route::middleware('permission:users.edit')->group(function () {

        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->name('users.edit');

        Route::put('/users/{user}', [UserController::class, 'update'])
            ->name('users.update');

        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggleStatus');
    });

    // Hapus user
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');

    /*
    |--------------------------------------------------------------------------
    | CATEGORIES
    |--------------------------------------------------------------------------
    */

    // Daftar kategori
    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('permission:categories.view')
        ->name('categories.index');

    // Tambah kategori
    // HARUS berada sebelum /categories/{category}
    Route::middleware('permission:categories.create')->group(function () {

        Route::get('/categories/create', [CategoryController::class, 'create'])
            ->name('categories.create');

        Route::post('/categories', [CategoryController::class, 'store'])
            ->name('categories.store');
    });

    // Edit kategori
    Route::middleware('permission:categories.edit')->group(function () {

        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])
            ->name('categories.edit');

        Route::put('/categories/{category}', [CategoryController::class, 'update'])
            ->name('categories.update');

        Route::patch('/categories/{category}', [CategoryController::class, 'update'])
            ->name('categories.update.patch');
    });

    // Detail kategori
    Route::get('/categories/{category}', [CategoryController::class, 'show'])
        ->middleware('permission:categories.view')
        ->name('categories.show');

    // Hapus kategori
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('permission:categories.delete')
        ->name('categories.destroy');

    /*
    |--------------------------------------------------------------------------
    | SUPPLIERS
    |--------------------------------------------------------------------------
    */

    // Daftar supplier
    Route::get('/suppliers', [SupplierController::class, 'index'])
        ->middleware('permission:suppliers.view')
        ->name('suppliers.index');

    // Tambah supplier
    // HARUS berada sebelum /suppliers/{supplier}
    Route::middleware('permission:suppliers.create')->group(function () {

        Route::get('/suppliers/create', [SupplierController::class, 'create'])
            ->name('suppliers.create');

        Route::post('/suppliers', [SupplierController::class, 'store'])
            ->name('suppliers.store');
    });

    // Edit supplier
    Route::middleware('permission:suppliers.edit')->group(function () {

        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])
            ->name('suppliers.edit');

        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])
            ->name('suppliers.update');

        Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])
            ->name('suppliers.update.patch');
    });

    // Detail supplier
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])
        ->middleware('permission:suppliers.view')
        ->name('suppliers.show');

    // Hapus supplier
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])
        ->middleware('permission:suppliers.delete')
        ->name('suppliers.destroy');

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */

    // Daftar produk
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:products.view')
        ->name('products.index');

    // Tambah produk
    // HARUS berada sebelum /products/{product}
    Route::middleware('permission:products.create')->group(function () {

        Route::get('/products/create', [ProductController::class, 'create'])
            ->name('products.create');

        Route::post('/products', [ProductController::class, 'store'])
            ->name('products.store');
    });

    // Edit produk
    Route::middleware('permission:products.edit')->group(function () {

        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit');

        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->name('products.update');

        Route::patch('/products/{product}', [ProductController::class, 'update'])
            ->name('products.update.patch');

        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggleStatus');
    });

    // Detail produk
    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->middleware('permission:products.view')
        ->name('products.show');

    // Hapus produk
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('permission:products.delete')
        ->name('products.destroy');

    /*
    |--------------------------------------------------------------------------
    | CUSTOMERS / PELANGGAN
    |--------------------------------------------------------------------------
    */

    // Daftar pelanggan
    Route::get('/customers', [CustomerController::class, 'index'])
        ->middleware('permission:customers.view')
        ->name('customers.index');

    // Edit pelanggan
    Route::middleware('permission:customers.edit')->group(function () {

        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
            ->name('customers.edit');

        Route::put('/customers/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update');

        Route::patch('/customers/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update.patch');
    });

    // Detail pelanggan
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])
        ->middleware('permission:customers.view')
        ->name('customers.show');

    // Hapus pelanggan
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
        ->middleware('permission:customers.delete')
        ->name('customers.destroy');

    /*
    |--------------------------------------------------------------------------
    | PURCHASES / PEMBELIAN
    |--------------------------------------------------------------------------
    */

    // Daftar pembelian
    Route::get('/purchases', [PurchaseController::class, 'index'])
        ->middleware('permission:purchases.view')
        ->name('purchases.index');

    // Tambah pembelian
    // HARUS berada sebelum /purchases/{purchase}
    Route::middleware('permission:purchases.create')->group(function () {

        Route::get('/purchases/create', [PurchaseController::class, 'create'])
            ->name('purchases.create');

        Route::post('/purchases', [PurchaseController::class, 'store'])
            ->name('purchases.store');
    });

    // Edit pembelian
    Route::middleware('permission:purchases.edit')->group(function () {

        Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])
            ->name('purchases.edit');

        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])
            ->name('purchases.update');

        Route::patch('/purchases/{purchase}', [PurchaseController::class, 'update'])
            ->name('purchases.update.patch');
    });

    // Detail pembelian
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])
        ->middleware('permission:purchases.view')
        ->name('purchases.show');

    // Hapus pembelian
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])
        ->middleware('permission:purchases.delete')
        ->name('purchases.destroy');

    /*
    |--------------------------------------------------------------------------
    | SALES / PENJUALAN
    |--------------------------------------------------------------------------
    */

    // Daftar penjualan
    Route::get('/sales', [SaleController::class, 'index'])
        ->middleware('permission:sales.view')
        ->name('sales.index');

    // Tambah penjualan
    // HARUS berada sebelum /sales/{sale}
    Route::middleware('permission:sales.create')->group(function () {

        Route::get('/sales/create', [SaleController::class, 'create'])
            ->name('sales.create');

        Route::post('/sales', [SaleController::class, 'store'])
            ->name('sales.store');
    });

    // Edit penjualan
    Route::middleware('permission:sales.edit')->group(function () {

        Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])
            ->name('sales.edit');

        Route::put('/sales/{sale}', [SaleController::class, 'update'])
            ->name('sales.update');

        Route::patch('/sales/{sale}', [SaleController::class, 'update'])
            ->name('sales.update.patch');
    });

    // Detail penjualan
    Route::get('/sales/{sale}', [SaleController::class, 'show'])
        ->middleware('permission:sales.view')
        ->name('sales.show');

    // Struk
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])
        ->middleware('permission:sales.view')
        ->name('sales.receipt');

    // Hapus penjualan
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])
        ->middleware('permission:sales.delete')
        ->name('sales.destroy');

    /*
    |--------------------------------------------------------------------------
    | REPORTS / LAPORAN
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:reports.view')->group(function () {

        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])
            ->name('reports.export.excel');

        Route::get('/reports/pdf', [ReportController::class, 'pdf'])
            ->name('reports.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | SETTINGS / PENGATURAN
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', [SettingController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('settings.index');

    Route::put('/settings', [SettingController::class, 'update'])
        ->middleware('permission:settings.edit')
        ->name('settings.update');

});

/*
|--------------------------------------------------------------------------
| TABLES / MEJA & QR
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant', 'credentials.changed'])->group(function () {

    Route::middleware('permission:tables.view')->group(function () {

        Route::get('/tables', [TableController::class, 'index'])
            ->name('tables.index');

        Route::get('/tables/print-all-qr', [TableController::class, 'printAllQr'])
            ->name('tables.printAllQr');

    });

    Route::middleware('permission:tables.create')->group(function () {

        Route::get('/tables/create', [TableController::class, 'create'])
            ->name('tables.create');

        Route::post('/tables', [TableController::class, 'store'])
            ->name('tables.store');

        Route::post('/tables/generate', [TableController::class, 'generate'])
            ->name('tables.generate');
    });

    // Keep parameterized routes below static routes such as /tables/create.
    // Otherwise Laravel tries to resolve the word "create" as a table model
    // and returns 404 before the create route can be reached.
    Route::middleware('permission:tables.view')->group(function () {

        Route::get('/tables/{table}', [TableController::class, 'show'])
            ->name('tables.show');
    });

    Route::middleware('permission:tables.edit')->group(function () {

        Route::get('/tables/{table}/edit', [TableController::class, 'edit'])
            ->name('tables.edit');

        Route::put('/tables/{table}', [TableController::class, 'update'])
            ->name('tables.update');

        Route::patch('/tables/{table}/toggle-status', [TableController::class, 'toggleStatus'])
            ->name('tables.toggleStatus');
    });

    Route::delete('/tables/{table}', [TableController::class, 'destroy'])
        ->middleware('permission:tables.delete')
        ->name('tables.destroy');

});

/*
|--------------------------------------------------------------------------
| TABLE QR
|--------------------------------------------------------------------------
*/

Route::get('/tables/{table}/qr', [TableController::class, 'qr'])
    ->middleware(['auth', 'tenant', 'credentials.changed'])
    ->name('tables.qr');

Route::get('/tables/{table}/qr-image', [TableController::class, 'qrImage'])
    ->middleware(['auth', 'tenant', 'credentials.changed'])
    ->name('tables.qr.image');

/*
|--------------------------------------------------------------------------
| CUSTOMER ORDER
|--------------------------------------------------------------------------
*/

Route::get('/order/table/{qr_token}', [CustomerOrderController::class, 'menu'])
    ->name('customer.menu');

/*
|--------------------------------------------------------------------------
| CUSTOMER CHECKOUT
|--------------------------------------------------------------------------
*/

Route::get(
    '/order/table/{table}/checkout',
    [CustomerCheckoutController::class, 'create']
)->name('customer.checkout');

Route::post(
    '/order/table/{table}/checkout',
    [CustomerCheckoutController::class, 'store']
)->name('customer.checkout.store');

Route::get(
    '/order/table/{table}/checkout/success/{sale}',
    [CustomerCheckoutController::class, 'success']
)->name('customer.checkout.success');

/*
|--------------------------------------------------------------------------
| MIDTRANS WEBHOOK
|--------------------------------------------------------------------------
|
| Route ini harus PUBLIC karena dipanggil langsung oleh server Midtrans.
| Jangan menggunakan middleware auth.
|
*/

Route::post(
    '/midtrans/webhook',
    [MidtransWebhookController::class, 'handle']
)->name('midtrans.webhook');

/*
|--------------------------------------------------------------------------
| NOTIFIKASI
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant', 'credentials.changed'])->group(function () {

    Route::get(
        '/notifications',
        [
            NotificationController::class,
            'index',
        ]
    )->name(
        'notifications.index'
    );

    Route::patch(
        '/notifications/{id}/read',
        [
            NotificationController::class,
            'read',
        ]
    )->name(
        'notifications.read'
    );

    Route::patch(
        '/notifications/read-all',
        [
            NotificationController::class,
            'readAll',
        ]
    )->name(
        'notifications.read-all'
    );

    Route::delete(
        '/notifications/{id}',
        [
            NotificationController::class,
            'destroy',
        ]
    )->name(
        'notifications.destroy'
    );

    Route::delete(
        '/notifications',
        [
            NotificationController::class,
            'destroyAll',
        ]
    )->name(
        'notifications.destroy-all'
    );

});

require __DIR__.'/auth.php';
