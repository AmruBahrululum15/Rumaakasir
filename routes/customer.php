<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{CustomerAuthController,CustomerPortalController,PortalChatController};
use App\Http\Middleware\CustomerAuthenticated;

Route::get('/store/{business:slug}',[CustomerPortalController::class,'store'])->name('customer.store');
Route::get('/store/{business:slug}/reserve',[\App\Http\Controllers\PublicReservationController::class,'form'])->name('reservations.form');
Route::get('/store/{business:slug}/reserve/slots',[\App\Http\Controllers\PublicReservationController::class,'slots'])->middleware('throttle:60,1')->name('reservations.slots');
Route::post('/store/{business:slug}/reserve',[\App\Http\Controllers\PublicReservationController::class,'store'])->middleware('throttle:portal-write')->name('reservations.store');
Route::get('/store/{business:slug}/reservations/{token}',[\App\Http\Controllers\PublicReservationController::class,'status'])->where('token','[A-Za-z0-9]{48}')->middleware('throttle:60,1')->name('reservations.status');
Route::get('/store/{business:slug}/products/{product}',[CustomerPortalController::class,'product'])->name('customer.product');
Route::prefix('customer')->name('customer.')->group(function(){
    Route::get('/stores',[CustomerPortalController::class,'directory'])->name('stores');
    foreach(['login','register','forgot','reset'] as $mode) {
        Route::get('/'.$mode,[CustomerAuthController::class,'form'])->defaults('mode',$mode)->name($mode);
        Route::post('/'.$mode,[CustomerAuthController::class,$mode])->middleware('throttle:portal-auth')->name($mode.'.submit');
    }
    Route::middleware(CustomerAuthenticated::class)->group(function(){
        Route::get('/links/{token}',[\App\Http\Controllers\CustomerLinkController::class,'show'])->name('links.show');
        Route::post('/links/{token}',[\App\Http\Controllers\CustomerLinkController::class,'accept'])->middleware('throttle:portal-write')->name('links.accept');
        Route::get('/stores/{business:slug}/cart',[\App\Http\Controllers\PortalCartController::class,'index'])->name('cart');
        Route::post('/stores/{business:slug}/cart',[\App\Http\Controllers\PortalCartController::class,'add'])->middleware('throttle:portal-write')->name('cart.add');
        Route::post('/stores/{business:slug}/checkout',[\App\Http\Controllers\PortalCartController::class,'checkout'])->middleware('throttle:portal-write')->name('cart.checkout');
        Route::post('/logout',[CustomerAuthController::class,'logout'])->name('logout');
        Route::get('/profile',[\App\Http\Controllers\CustomerProfileController::class,'show'])->name('profile');
        Route::get('/orders/{order}',[\App\Http\Controllers\CustomerOrderPaymentController::class,'show'])->name('orders.show');
        Route::post('/orders/{order}/pay',[\App\Http\Controllers\CustomerOrderPaymentController::class,'pay'])->middleware('throttle:portal-write')->name('orders.pay');
        Route::get('/orders/{order}/qris',[\App\Http\Controllers\CustomerOrderPaymentController::class,'qris'])->name('orders.qris');
        Route::patch('/profile',[\App\Http\Controllers\CustomerProfileController::class,'update'])->middleware('throttle:portal-write');
        Route::get('/avatar',[\App\Http\Controllers\CustomerProfileController::class,'avatar'])->name('avatar');
        Route::post('/addresses/{address?}',[\App\Http\Controllers\CustomerProfileController::class,'address'])->middleware('throttle:portal-write')->name('addresses.save');
        foreach(['dashboard','orders','bookings','loyalty','vouchers'] as $section) Route::get('/'.$section,[CustomerPortalController::class,'history'])->defaults('section',$section)->name($section);
        Route::post('/stores/{business:slug}/join',[CustomerPortalController::class,'join'])->middleware('throttle:portal-write')->name('join');
        Route::post('/stores/{business:slug}/booking',[CustomerPortalController::class,'book'])->middleware('throttle:portal-write')->name('book');
        Route::patch('/bookings/{booking}/cancel',[CustomerPortalController::class,'cancel'])->name('bookings.cancel');
        Route::get('/bookings/{booking}',[\App\Http\Controllers\PublicReservationController::class,'ownStatus'])->name('bookings.show');
        Route::post('/stores/{business:slug}/chat',[PortalChatController::class,'start'])->middleware('throttle:portal-write')->name('chat.start');
        Route::get('/chat',[PortalChatController::class,'index'])->name('chat.index');
        Route::get('/chat/{conversation}',[PortalChatController::class,'index'])->name('chat.show');
        Route::post('/chat/{conversation}',[PortalChatController::class,'send'])->middleware('throttle:portal-chat')->name('chat.send');
    });
});
Route::middleware(['auth','tenant','credentials.changed'])->prefix('customer-chat')->name('customer-chat.')->group(function(){
    Route::get('/',[PortalChatController::class,'index'])->name('index');
    Route::get('/{conversation}',[PortalChatController::class,'index'])->name('show');
    Route::post('/{conversation}',[PortalChatController::class,'send'])->middleware('throttle:portal-chat')->name('send');
});
Route::middleware(['auth','tenant','credentials.changed'])->group(function(){
    Route::get('/notifications/feed',[\App\Http\Controllers\NotificationController::class,'feed'])->name('notifications.feed');
    Route::post('/notifications/{id}/open',[\App\Http\Controllers\NotificationController::class,'open'])->name('notifications.open');
    Route::put('/settings/location',[\App\Http\Controllers\StoreLocationController::class,'update'])->name('settings.location');
    Route::post('/portal-customers/invite',[\App\Http\Controllers\CustomerLinkController::class,'invite'])->middleware('throttle:customer-link')->name('portal-customers.invite');
    Route::get('/portal-customers',[\App\Http\Controllers\PlatformCustomerController::class,'staff'])->name('portal-customers.index');
    Route::get('/portal-orders',[\App\Http\Controllers\PortalStaffOrderController::class,'index'])->name('portal-orders.index');
    Route::get('/portal-orders/{order}',[\App\Http\Controllers\PortalStaffOrderController::class,'show'])->name('portal-orders.show');
    Route::get('/settings/customer-payments',[\App\Http\Controllers\BusinessPaymentSettingController::class,'edit'])->name('settings.customer-payments');
    Route::put('/settings/customer-payments',[\App\Http\Controllers\BusinessPaymentSettingController::class,'update'])->name('settings.customer-payments.update');
    Route::patch('/portal-orders/{order}',[\App\Http\Controllers\PortalStaffOrderController::class,'update'])->name('portal-orders.update');
});
Route::get('/super-admin/customer-portal',[CustomerPortalController::class,'summary'])->middleware(['auth','credentials.changed','role:Super Admin'])->name('super-admin.customer-portal');
Route::middleware(['auth','credentials.changed','role:Super Admin'])->prefix('super-admin/platform-customers')->name('super-admin.platform-customers.')->group(function(){
    Route::get('/',[\App\Http\Controllers\PlatformCustomerController::class,'index'])->name('index');
    Route::get('/{account}',[\App\Http\Controllers\PlatformCustomerController::class,'show'])->name('show');
});
