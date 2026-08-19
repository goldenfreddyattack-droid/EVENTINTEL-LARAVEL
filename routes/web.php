<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::view('about', 'about')->name('about');

    Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');

    Route::get('/student', [\App\Http\Controllers\studmngt_controller::class, 'index'])->name('student.index');
    Route::get('/addstudent', [\App\Http\Controllers\studmngt_controller::class, 'addstud'])->name('student.addstud');
    Route::post('/studentstore', [App\Http\Controllers\studmngt_controller::class, 'store'])->name('student.store');
    Route::get('student/{id}/edit', [App\Http\Controllers\studmngt_controller::class, 'edit'])->name('student.edit');
    Route::put('student/{id}', [App\Http\Controllers\studmngt_controller::class, 'update'])->name('student.update');
    Route::delete('student/{id}', [App\Http\Controllers\studmngt_controller::class, 'delete'])->name('student.delete');
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Supplier routes
    Route::get('/supplier/dashboard', [\App\Http\Controllers\SupplierDashboardController::class, 'index'])->name('supplier.dashboard');

    Route::get('/supplier/setup', [\App\Http\Controllers\SupplierOnboardingController::class, 'index'])->name('supplier.setup');
    Route::post('/supplier/setup/details', [\App\Http\Controllers\SupplierOnboardingController::class, 'updateDetails'])->name('supplier.setup.details');
    Route::post('/supplier/setup/services', [\App\Http\Controllers\SupplierOnboardingController::class, 'storeService'])->name('supplier.setup.services.store');
    Route::delete('/supplier/setup/services/{id}', [\App\Http\Controllers\SupplierOnboardingController::class, 'destroyService'])->name('supplier.setup.services.destroy');

    Route::get('/supplier/services', [\App\Http\Controllers\SupplierServiceController::class, 'index'])->name('supplier.services');
    Route::post('/supplier/services', [\App\Http\Controllers\SupplierServiceController::class, 'store'])->name('supplier.services.store');
    Route::get('/supplier/services/{id}/image', [\App\Http\Controllers\SupplierServiceController::class, 'image'])->name('supplier.services.image');
    Route::delete('/supplier/services/{id}', [\App\Http\Controllers\SupplierServiceController::class, 'destroy'])->name('supplier.services.destroy');

    Route::get('/supplier/messages', [\App\Http\Controllers\SupplierMessagesController::class, 'index'])->name('supplier.messages');
    Route::post('/supplier/messages', [\App\Http\Controllers\SupplierMessagesController::class, 'send'])->name('supplier.messages.send');
    Route::get('/supplier/messages/api', [\App\Http\Controllers\SupplierMessagesController::class, 'api'])->name('supplier.messages.api');

    Route::get('/supplier/reviews', [\App\Http\Controllers\SupplierReviewsController::class, 'index'])->name('supplier.reviews');

    Route::get('/supplier/settings', [\App\Http\Controllers\SupplierSettingsController::class, 'index'])->name('supplier.settings');
    Route::post('/supplier/settings', [\App\Http\Controllers\SupplierSettingsController::class, 'update'])->name('supplier.settings.update');

    Route::get('/supplier/profile', [\App\Http\Controllers\SupplierProfileController::class, 'index'])->name('supplier.profile');
    Route::post('/supplier/profile', [\App\Http\Controllers\SupplierProfileController::class, 'update'])->name('supplier.profile.update');

    Route::get('/supplier/bookings', [\App\Http\Controllers\SupplierBookingsController::class, 'index'])->name('supplier.bookings');
    Route::post('/supplier/bookings/update', [\App\Http\Controllers\SupplierBookingsController::class, 'updateStatus'])->name('supplier.bookings.update');

    Route::match(['get', 'post'], '/supplier/{page?}', [App\Http\Controllers\SupplierPageController::class, 'show'])
        ->where('page', 'DASHBOARD|BOOKINGS|SERVICES|MESSAGES|REVIEWS|SETTINGS|FEED|PROFILE')
        ->defaults('page', 'DASHBOARD')
        ->name('supplier.page');

    Route::get('/supplier', function () {
        return redirect()->route('supplier.dashboard');
    })->name('supplier.home');

    Route::get('/supplier/newsfeed', function () {
        return redirect()->route('supplier.feed');
    })->name('supplier.newsfeed');

    Route::get('/supplier/feed', [\App\Http\Controllers\SupplierFeedController::class, 'index'])->name('supplier.feed');
        Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/admin/users', [\App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.users.store');
        Route::get('/admin/requests', [\App\Http\Controllers\AdminController::class, 'requests'])->name('admin.requests');
        Route::patch('/admin/requests/{userId}', [\App\Http\Controllers\AdminController::class, 'updateRequest'])->name('admin.requests.update');
        Route::get('/admin/summary', [\App\Http\Controllers\AdminController::class, 'legacyDashboard'])->name('admin.legacy');

        Route::get('/coordinator', [\App\Http\Controllers\CoordinatorController::class, 'dashboard'])->name('coordinator.dashboard');
        Route::get('/coordinator/events', [\App\Http\Controllers\CoordinatorController::class, 'events'])->name('coordinator.events');
        Route::patch('/coordinator/events/{eventId}', [\App\Http\Controllers\CoordinatorController::class, 'updateEvent'])->name('coordinator.events.update');
        Route::get('/coordinator/packages', [\App\Http\Controllers\CoordinatorController::class, 'packages'])->name('coordinator.packages');
        Route::post('/coordinator/packages', [\App\Http\Controllers\CoordinatorController::class, 'storePackage'])->name('coordinator.packages.store');
        Route::delete('/coordinator/packages/{id}', [\App\Http\Controllers\CoordinatorController::class, 'deletePackage'])->name('coordinator.packages.delete');
        Route::get('/coordinator/proposals', [\App\Http\Controllers\CoordinatorController::class, 'proposals'])->name('coordinator.proposals');
            Route::post('/coordinator/proposals', [\App\Http\Controllers\CoordinatorController::class, 'storeProposal'])->name('coordinator.proposals.store');
        Route::match(['get', 'post'], '/coordinator/messages', [\App\Http\Controllers\CoordinatorController::class, 'messages'])->name('coordinator.messages');
        Route::get('/coordinator/messages/api', [\App\Http\Controllers\CoordinatorController::class, 'messageApi'])->name('coordinator.messages.api');
        Route::get('/coordinator/suppliers', [\App\Http\Controllers\CoordinatorController::class, 'suppliers'])->name('coordinator.suppliers');
        Route::get('/coordinator/profile', [\App\Http\Controllers\CoordinatorController::class, 'profile'])->name('coordinator.profile');
        Route::post('/coordinator/profile', [\App\Http\Controllers\CoordinatorController::class, 'updateProfile'])->name('coordinator.profile.update');
        Route::get('/coordinator/reports', [\App\Http\Controllers\CoordinatorController::class, 'reports'])->name('coordinator.reports');
        Route::get('/coordinator/settings', [\App\Http\Controllers\CoordinatorController::class, 'settings'])->name('coordinator.settings');
        Route::post('/coordinator/settings', [\App\Http\Controllers\CoordinatorController::class, 'updateSettings'])->name('coordinator.settings.update');
});
