<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BusinessController;
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

require __DIR__.'/customer.php';

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->prefix('register-store')->name('store-registration.')->group(function () {
    Route::get('/regions', \App\Http\Controllers\RegistrationRegionController::class)->middleware('throttle:120,1')->name('regions');
    Route::get('/', [\App\Http\Controllers\StoreRegistrationController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\StoreRegistrationController::class, 'store'])->middleware('throttle:store-registration')->name('store');
    Route::view('/success', 'store-registration.success')->name('success');
    Route::view('/terms', 'store-registration.terms')->name('terms');
    Route::get('/activate/{registration}', [\App\Http\Controllers\StoreRegistrationController::class, 'activate'])->middleware('signed')->name('activate');
    Route::post('/activate/{registration}', [\App\Http\Controllers\StoreRegistrationController::class, 'activateAccount'])->middleware(['signed', 'throttle:10,1'])->name('activate-account');
});

Route::get('/user-photos/{id}', \App\Http\Controllers\UserPhotoController::class)
    ->whereNumber('id')->middleware(['auth', 'credentials.changed'])->name('users.photo');

// Super Admin manages businesses outside a tenant context, then explicitly
// selects one business before using the existing POS modules.
Route::middleware(['auth', 'credentials.changed', 'role:Super Admin'])->group(function () {
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/store-registrations', [\App\Http\Controllers\StoreRegistrationReviewController::class, 'index'])->name('store-registrations.index');
        Route::get('/store-registrations/{registration}', [\App\Http\Controllers\StoreRegistrationReviewController::class, 'show'])->name('store-registrations.show');
        Route::post('/store-registrations/{registration}/approve', [\App\Http\Controllers\StoreRegistrationReviewController::class, 'approve'])->name('store-registrations.approve');
        Route::post('/store-registrations/{registration}/reject', [\App\Http\Controllers\StoreRegistrationReviewController::class, 'reject'])->name('store-registrations.reject');
        Route::post('/store-registrations/{registration}/resend', [\App\Http\Controllers\StoreRegistrationReviewController::class, 'resend'])->middleware('throttle:5,60')->name('store-registrations.resend');
        Route::get('/subscriptions', [\App\Http\Controllers\SuperAdminPageController::class, 'subscriptions'])->name('subscriptions.index');
        Route::get('/users', [\App\Http\Controllers\SuperAdminPageController::class, 'users'])->name('users.index');
        Route::get('/owners', [\App\Http\Controllers\SuperAdminPageController::class, 'users'])->name('owners.index');
        Route::get('/activities', [\App\Http\Controllers\SuperAdminPageController::class, 'activities'])->name('activities.index');
        Route::get('/notifications', [\App\Http\Controllers\SuperAdminPageController::class, 'notifications'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::get('/profile', [\App\Http\Controllers\SuperAdminPageController::class, 'profile'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/settings', [\App\Http\Controllers\SuperAdminPageController::class, 'settings'])->name('settings.index');
        Route::get('/platform-settings', [\App\Http\Controllers\PlatformSettingController::class, 'index'])->name('platform-settings.index');
        Route::put('/platform-settings', [\App\Http\Controllers\PlatformSettingController::class, 'update'])->name('platform-settings.update');
    });
    Route::get('/super-admin/packages', [\App\Http\Controllers\PackageController::class, 'index'])->name('packages.index');
    Route::put('/super-admin/packages/{package}', [\App\Http\Controllers\PackageController::class, 'update'])->name('packages.update');
    Route::get('/super-admin/dashboard', [BusinessController::class, 'index'])->name('super-admin.dashboard');
    Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
    Route::get('/businesses/create', [BusinessController::class, 'create'])->name('businesses.create');
    Route::post('/businesses', [BusinessController::class, 'store'])->name('businesses.store');
    Route::get('/businesses/{business}', [BusinessController::class, 'show'])->name('businesses.show');
    Route::patch('/businesses/{business}/status', [BusinessController::class, 'status'])->name('businesses.status');
    Route::get('/businesses/{business}/edit', [BusinessController::class, 'edit'])->name('businesses.edit');
    Route::put('/businesses/{business}', [BusinessController::class, 'update'])->name('businesses.update');
    Route::post('/businesses/{business}/activate', [BusinessController::class, 'activate'])->name('businesses.activate');
});

Route::middleware(['auth', 'tenant', 'credentials.changed'])->group(function () {
    Route::prefix('subscription')->name('subscriptions.')
        ->middleware(\App\Http\Middleware\EnsureSubscriptionOwner::class)->group(function () {
            Route::get('/', [\App\Http\Controllers\SubscriptionController::class, 'index'])->name('index');
            Route::get('/checkout', [\App\Http\Controllers\SubscriptionController::class, 'paymentOptions'])->name('payment-options');
            Route::post('/checkout', [\App\Http\Controllers\SubscriptionController::class, 'store'])->middleware('throttle:10,1')->name('store');
            Route::get('/payment/{orderId}/result', [\App\Http\Controllers\SubscriptionController::class, 'result'])->name('result');
            Route::post('/payment/{orderId}/retry', [\App\Http\Controllers\SubscriptionController::class, 'retry'])->middleware('throttle:10,1')->name('retry');
            Route::get('/orders/{orderId}/invoice', [\App\Http\Controllers\SubscriptionController::class, 'invoice'])->name('invoice');
        });
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
            ->middleware('tenant.capacity:users')
            ->name('users.store');
    });

    // Approval must be registered before /users/{user}, otherwise the literal
    // "approvals" can be interpreted as a user route parameter.
    Route::middleware(['role:Atasan|Admin', 'permission:users.approve'])->group(function () {
        Route::get('/users/approvals', [UserApprovalController::class, 'index'])->name('users.approvals.index');
        Route::patch('/users/{user}/approve', [UserApprovalController::class, 'approve'])->middleware('tenant.capacity:users,approve')->name('users.approvals.approve');
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
            ->middleware('tenant.capacity:products')
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
        Route::get('/premium/analytics', [\App\Http\Controllers\PremiumAnalyticsController::class, 'index'])
            ->middleware('package.feature:advanced_dashboard')->name('premium.dashboard');
        Route::get('/premium/reports', [\App\Http\Controllers\PremiumAnalyticsController::class, 'index'])
            ->middleware('package.feature:advanced_reports')->name('premium.reports');
        Route::get('/premium/reports/csv', [\App\Http\Controllers\PremiumAnalyticsController::class, 'csv'])
            ->middleware('package.feature:export_csv')->name('premium.csv');


        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])
            ->middleware('package.feature:export_excel')
            ->name('reports.export.excel');

        Route::get('/reports/pdf', [ReportController::class, 'pdf'])
            ->middleware('package.feature:export_pdf')
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
        ->middleware('package.branding')
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
            ->middleware('package.feature:qr_order')
            ->name('tables.printAllQr');

    });

    Route::middleware('permission:tables.create')->group(function () {

        Route::get('/tables/create', [TableController::class, 'create'])
            ->name('tables.create');

        Route::post('/tables', [TableController::class, 'store'])
            ->middleware('tenant.capacity:tables')
            ->name('tables.store');

        Route::post('/tables/generate', [TableController::class, 'generate'])
            ->middleware('tenant.capacity:tables,generate')
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
    ->middleware(['auth', 'tenant', 'credentials.changed', 'package.feature:qr_order'])
    ->name('tables.qr');

Route::get('/tables/{table}/qr-image', [TableController::class, 'qrImage'])
    ->middleware(['auth', 'tenant', 'credentials.changed', 'package.feature:qr_order'])
    ->name('tables.qr.image');

/*
|--------------------------------------------------------------------------
| CUSTOMER ORDER
|--------------------------------------------------------------------------
*/

Route::get('/order/table/{qr_token}', [CustomerOrderController::class, 'menu'])
    ->middleware('package.qr')
    ->name('customer.menu');

/*
|--------------------------------------------------------------------------
| CUSTOMER CHECKOUT
|--------------------------------------------------------------------------
*/

Route::get(
    '/order/table/{table}/checkout',
    [CustomerCheckoutController::class, 'create']
)->middleware('package.qr')->name('customer.checkout');

Route::post(
    '/order/table/{table}/checkout',
    [CustomerCheckoutController::class, 'store']
)->middleware('package.qr')->name('customer.checkout.store');

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

Route::post('/premium/checkout/quote',\App\Http\Controllers\PremiumCheckoutQuoteController::class)
    ->middleware(['auth','tenant','credentials.changed','permission:sales.create','throttle:120,1'])->name('premium.checkout.quote');

Route::middleware(['auth','tenant','credentials.changed','role:Atasan','permission:settings.edit','package.feature:multi_outlet','package.feature:inter_outlet_stock_transfer'])->group(function () {
    Route::get('/premium/outlet-inventory',[\App\Http\Controllers\OutletInventoryController::class,'index'])->name('outlets.inventory.index');
    Route::post('/premium/outlet-inventory',[\App\Http\Controllers\OutletInventoryController::class,'store'])->name('outlets.inventory.store');
});

Route::middleware(['auth','tenant','credentials.changed','role:Atasan','permission:settings.edit','package.feature:multi_outlet'])->group(function () {
    Route::get('/premium/outlets',[\App\Http\Controllers\OutletController::class,'index'])->name('outlets.index');
    Route::post('/premium/outlets',[\App\Http\Controllers\OutletController::class,'store'])->name('outlets.store');
    Route::put('/premium/outlets/{outlet}',[\App\Http\Controllers\OutletController::class,'update'])->name('outlets.update');
    Route::post('/premium/outlets/select',[\App\Http\Controllers\OutletController::class,'select'])->name('outlets.select');
    Route::patch('/premium/outlets/users/{user}',[\App\Http\Controllers\OutletController::class,'assign'])->name('outlets.assign');
});

Route::middleware(['auth','tenant','credentials.changed','role:Atasan|Admin|Kasir','permission:sales.create','package.feature:booking'])->group(function () {
    Route::get('/premium/bookings',[\App\Http\Controllers\BookingController::class,'index'])->name('bookings.index');
    Route::get('/premium/bookings/report',[\App\Http\Controllers\BookingController::class,'report'])->name('bookings.report');
    Route::post('/premium/bookings',[\App\Http\Controllers\BookingController::class,'store'])->name('bookings.store');
    Route::post('/premium/bookings/services',[\App\Http\Controllers\BookingController::class,'service'])->middleware(['role:Atasan|Admin','permission:settings.edit'])->name('bookings.services.store');
    Route::post('/premium/bookings/settings',[\App\Http\Controllers\BookingController::class,'settings'])->middleware(['role:Atasan|Admin','permission:settings.edit'])->name('bookings.settings');
    Route::post('/premium/bookings/resources',[\App\Http\Controllers\BookingController::class,'resource'])->middleware(['role:Atasan|Admin','permission:settings.edit'])->name('bookings.resources');
    Route::post('/premium/bookings/schedules',[\App\Http\Controllers\BookingController::class,'schedule'])->middleware(['role:Atasan|Admin','permission:settings.edit'])->name('bookings.schedules');
    Route::put('/premium/bookings/services/{service}',[\App\Http\Controllers\BookingController::class,'serviceUpdate'])->middleware(['role:Atasan|Admin','permission:settings.edit'])->name('bookings.services.update');
    Route::put('/premium/bookings/resources/{resource}',[\App\Http\Controllers\BookingController::class,'resourceUpdate'])->middleware(['role:Atasan|Admin','permission:settings.edit'])->name('bookings.resources.update');
    Route::get('/premium/bookings/{booking}',[\App\Http\Controllers\BookingController::class,'show'])->name('bookings.show');
    Route::get('/premium/bookings/{booking}/receipt',[\App\Http\Controllers\BookingController::class,'receipt'])->name('bookings.receipt');
    Route::post('/premium/bookings/{booking}/cash',[\App\Http\Controllers\BookingController::class,'pay'])->name('bookings.cash');
    Route::post('/premium/bookings/{booking}/pos',[\App\Http\Controllers\BookingController::class,'link'])->name('bookings.pos');
    Route::post('/premium/bookings/{booking}/checkout',[\App\Http\Controllers\BookingController::class,'checkout'])->name('bookings.checkout');
    Route::get('/premium/bookings/{booking}/edit',[\App\Http\Controllers\BookingController::class,'index'])->name('bookings.edit');
    Route::put('/premium/bookings/{booking}',[\App\Http\Controllers\BookingController::class,'update'])->name('bookings.update');
    Route::patch('/premium/bookings/{booking}/status',[\App\Http\Controllers\BookingController::class,'status'])->name('bookings.status');
});

Route::middleware(['auth','tenant','credentials.changed','role:Atasan|Admin|Kasir','permission:sales.create','package.feature:laundry_services'])->group(function () {
    Route::get('/premium/laundry',[\App\Http\Controllers\LaundryController::class,'index'])->name('laundry.index');
    Route::post('/premium/laundry',[\App\Http\Controllers\LaundryController::class,'store'])->name('laundry.store');
    Route::post('/premium/laundry/services',[\App\Http\Controllers\LaundryController::class,'service'])->middleware(['role:Atasan|Admin','permission:settings.edit'])->name('laundry.services.store');
    Route::get('/premium/laundry/{order}',[\App\Http\Controllers\LaundryController::class,'show'])->name('laundry.show');
    Route::patch('/premium/laundry/{order}/status',[\App\Http\Controllers\LaundryController::class,'status'])->name('laundry.status');
    Route::post('/premium/laundry/{order}/payments',[\App\Http\Controllers\LaundryController::class,'pay'])->name('laundry.pay');
});

Route::middleware(['auth','tenant','credentials.changed','role:Atasan|Admin|Kasir','permission:sales.create','package.feature:approval_transactions'])->group(function () {
    Route::get('/premium/sale-approvals',[\App\Http\Controllers\SaleApprovalController::class,'index'])->name('sale-approvals.index');
    Route::post('/premium/sale-approvals',[\App\Http\Controllers\SaleApprovalController::class,'store'])->name('sale-approvals.store');
    Route::patch('/premium/sale-approvals/{approval}',[\App\Http\Controllers\SaleApprovalController::class,'review'])->middleware(['role:Atasan|Admin','permission:sales.approve-refunds'])->name('sale-approvals.review');
});

Route::middleware(['auth','tenant','credentials.changed','role:Atasan|Admin','permission:settings.edit','package.feature:promotions'])->group(function () {
    Route::get('/premium/promotions',[\App\Http\Controllers\PromotionController::class,'index'])->name('promotions.index');
    Route::post('/premium/promotions',[\App\Http\Controllers\PromotionController::class,'store'])->name('promotions.store');
    Route::get('/premium/promotions/{promotion}/edit',[\App\Http\Controllers\PromotionController::class,'edit'])->name('promotions.edit');
    Route::put('/premium/promotions/{promotion}',[\App\Http\Controllers\PromotionController::class,'update'])->name('promotions.update');
    Route::get('/premium/promotions/{promotion}/history',[\App\Http\Controllers\PromotionController::class,'history'])->name('promotions.history');
});

Route::middleware(['auth','tenant','credentials.changed','package.feature:customer_loyalty'])->group(function () {
    Route::get('/premium/loyalty',[\App\Http\Controllers\LoyaltyController::class,'index'])->middleware('permission:customers.view')->name('loyalty.index');
    Route::put('/premium/loyalty',[\App\Http\Controllers\LoyaltyController::class,'update'])->middleware(['role:Atasan','permission:settings.edit'])->name('loyalty.update');
    Route::get('/premium/loyalty/{customer}',[\App\Http\Controllers\LoyaltyController::class,'show'])->middleware('permission:customers.view')->name('loyalty.show');
});

/*
|--------------------------------------------------------------------------
| AI ASSISTANT
|--------------------------------------------------------------------------
|
| AI membaca data bisnis POS sehingga endpoint tetap berada
| di belakang autentikasi. Tidak ada perubahan pada data/database.
|
*/

Route::middleware(['auth', 'tenant', 'credentials.changed'])->group(function () {

    Route::get('/ai-assistant', [\App\Http\Controllers\AiAssistantController::class, 'index'])
        ->middleware('package.feature:ai_agent')
        ->name('ai.index');

    Route::post('/ai-assistant/chat', [\App\Http\Controllers\AiAssistantController::class, 'chat'])
        ->middleware('package.feature:ai_agent')
        ->middleware('throttle:20,1')
        ->name('ai.chat');

    Route::post('/ai-assistant/reset', [\App\Http\Controllers\AiAssistantController::class, 'reset'])
        ->middleware('package.feature:ai_agent')
        ->name('ai.reset');

});
