
<?php

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\AdminMembershipController;
use App\Http\Controllers\ArtistArtworkController;
use App\Http\Controllers\AdminArtworkController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ArtworkPurchaseController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// ========================================
// HOME / DISCOVER
// ========================================

Route::get('/', [GalleryController::class, 'home'])
    ->name('home');

// ========================================
// ARTWORKS
// ========================================

Route::get('/artworks', [GalleryController::class, 'artworks'])
    ->name('artworks.index');

Route::get('/artworks/{slug}', [GalleryController::class, 'artwork'])
    ->name('artworks.show');

// ========================================
// ARTISTS
// ========================================

Route::get('/artists', [GalleryController::class, 'artists'])
    ->name('artists.index');

Route::get('/artists/{slug}', [GalleryController::class, 'artist'])
    ->name('artists.show');

// ========================================
// EXHIBITIONS
// ========================================

Route::get('/exhibitions', [GalleryController::class, 'exhibitions'])
    ->name('exhibitions.index');

Route::get('/exhibitions/{slug}', [GalleryController::class, 'exhibition'])
    ->name('exhibitions.show');

// ========================================
// FAVORITES
// ========================================

Route::view('/favorites', 'artfy.page', [
    'title' => 'Your favorites',
    'eyebrow' => 'A personal collection',
    'description' => 'Save the artworks that speak to you and keep your favorite discoveries close.',
    'pageType' => 'favorites',
])->name('favorites.index');

// ========================================
// LOGIN
// ========================================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

// ========================================
// REGISTRATION
// ========================================

Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.submit');

// ========================================
// CHOOSE ACCOUNT TYPE
// ========================================

Route::view('/choose-role', 'auth.choose-role')
    ->name('auth.choose-role');

// ========================================
// SSLCOMMERZ PAYMENT CALLBACKS
// Keep outside the auth group.
// ========================================

Route::post('/payment/sslcommerz/success', [
    ArtworkPurchaseController::class,
    'success',
])->name('payment.sslcommerz.success');

Route::post('/payment/sslcommerz/fail', [
    ArtworkPurchaseController::class,
    'fail',
])->name('payment.sslcommerz.fail');

Route::post('/payment/sslcommerz/cancel', [
    ArtworkPurchaseController::class,
    'cancel',
])->name('payment.sslcommerz.cancel');

Route::post('/payment/sslcommerz/ipn', [
    ArtworkPurchaseController::class,
    'ipn',
])->name('payment.sslcommerz.ipn');

// ========================================
// LOGOUT
// ========================================

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ========================================
// PROTECTED ROUTES
// ========================================

Route::middleware('auth')->group(function () {

    // ARTWORK PURCHASE / CHECKOUT

    Route::get('/artworks/{slug}/buy', [
        ArtworkPurchaseController::class,
        'create',
    ])->name('artworks.purchase.create');

    Route::post('/artworks/{slug}/buy', [
        ArtworkPurchaseController::class,
        'store',
    ])->name('artworks.purchase.store');

    // DELIVERY ADDRESS GEOCODING

    Route::post('/artworks/delivery/geocode', [
        ArtworkPurchaseController::class,
        'geocodeDestination',
    ])->name('artworks.delivery.geocode');

    // ARTIST MEMBERSHIP APPLICATION

    Route::get('/artist/application', [
        AuthController::class,
        'showArtistApplication',
    ])->name('artist.application');

    Route::post('/artist/application', [
        AuthController::class,
        'submitArtistApplication',
    ])->name('artist.application.submit');

    // ARTIST MEMBERSHIP PAYMENT

    Route::post('/artist/application/payment', [
        AuthController::class,
        'submitMembershipPayment',
    ])->name('artist.application.payment');

    // ARTIST PROFILE EDITING

    Route::get('/artist/my-profile', [
        ArtistProfileController::class,
        'edit',
    ])->name('artist-profile.edit');

    Route::put('/artist/my-profile', [
        ArtistProfileController::class,
        'update',
    ])->name('artist-profile.update');

    // MY PROFILE

    Route::get('/my-profile', [
        ArtistProfileController::class,
        'show',
    ])->name('my-profile');

    Route::get('/my-profile/edit', [
        ArtistProfileController::class,
        'editArtLover',
    ])->name('my-profile.edit');

    Route::put('/my-profile', [
        ArtistProfileController::class,
        'updateArtLover',
    ])->name('my-profile.update');

    // ====================================
    // MY ARTWORKS
    // ====================================

    // Keep this route name for compatibility with existing views.
    Route::get('/artist/studio', [
        ArtistArtworkController::class,
        'index',
    ])->name('artist.studio');

    // CREATE / UPLOAD

    Route::get('/artist/studio/artworks/create', [
        ArtistArtworkController::class,
        'create',
    ])->name('artist.artworks.create');

    Route::post('/artist/studio/artworks', [
        ArtistArtworkController::class,
        'store',
    ])->name('artist.artworks.store');

    // EDIT

    Route::get('/artist/my-artworks/{artwork}/edit', [
        ArtistArtworkController::class,
        'edit',
    ])->name('artist.artworks.edit');

    // UPDATE

    Route::put('/artist/my-artworks/{artwork}', [
        ArtistArtworkController::class,
        'update',
    ])->name('artist.artworks.update');

    // DELETE

    Route::delete('/artist/studio/artworks/{artwork}', [
        ArtistArtworkController::class,
        'destroy',
    ])->name('artist.artworks.destroy');

    // ====================================
    // ADMIN DASHBOARD
    // ====================================

    Route::get('/admin', [
        AdminMembershipController::class,
        'index',
    ])->name('admin.dashboard');

    // DEDICATED ADMIN PAGES

    Route::get('/admin/memberships', [
        AdminMembershipController::class,
        'memberships',
    ])->name('admin.memberships.index');

    Route::get('/admin/payments', [
        AdminMembershipController::class,
        'payments',
    ])->name('admin.payments.index');

    Route::get('/admin/reports', [
        AdminMembershipController::class,
        'reports',
    ])->name('admin.reports.index');

    Route::get('/admin/sales', [
        AdminMembershipController::class,
        'sales',
    ])->name('admin.sales.index');

    // ADMIN MEMBERSHIP MANAGEMENT

    Route::post('/admin/memberships/{application}/approve', [
        AdminMembershipController::class,
        'approve',
    ])->name('admin.memberships.approve');

    Route::post('/admin/memberships/{application}/reject', [
        AdminMembershipController::class,
        'reject',
    ])->name('admin.memberships.reject');

    Route::post('/admin/memberships/{application}/verify-payment', [
        AdminMembershipController::class,
        'verifyPayment',
    ])->name('admin.memberships.verify-payment');

    // ADMIN ARTWORK REVIEW

    Route::get('/admin/artworks', [
        AdminArtworkController::class,
        'index',
    ])->name('admin.artworks.index');

    Route::post('/admin/artworks/{artwork}/approve', [
        AdminArtworkController::class,
        'approve',
    ])->name('admin.artworks.approve');

    Route::post('/admin/artworks/{artwork}/reject', [
        AdminArtworkController::class,
        'reject',
    ])->name('admin.artworks.reject');

    // ADMIN ARTISTS

    Route::get('/admin/artists', [
        AdminUserController::class,
        'artists',
    ])->name('admin.artists.index');

    Route::get('/admin/artists/{user}', [
        AdminUserController::class,
        'showArtist',
    ])->name('admin.artists.show');

    // ADMIN ART LOVERS

    Route::get('/admin/art-lovers', [
        AdminUserController::class,
        'artLovers',
    ])->name('admin.art-lovers.index');

    Route::get('/admin/art-lovers/{user}', [
        AdminUserController::class,
        'showArtLover',
    ])->name('admin.art-lovers.show');

    // NOTIFICATIONS

    Route::get('/notifications', [
        NotificationController::class,
        'index',
    ])->name('notifications.index');

    Route::get('/notifications/{notification}/open', [
        NotificationController::class,
        'open',
    ])->name('notifications.open');
});
