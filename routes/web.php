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
use App\Http\Controllers\ArtistMembershipPaymentController;
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
// LOGIN / REGISTRATION
// ========================================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.submit');

Route::view('/choose-role', 'auth.choose-role')
    ->name('auth.choose-role');

// ========================================
// ARTWORK SSLCOMMERZ CALLBACKS
// Keep these unchanged and outside auth.
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
// MEMBERSHIP SSLCOMMERZ CALLBACKS
// Separate from artwork callbacks.
// Keep outside auth.
// ========================================

Route::post('/membership/payment/success', [
    ArtistMembershipPaymentController::class,
    'success',
])->name('membership.payment.success');

Route::post('/membership/payment/fail', [
    ArtistMembershipPaymentController::class,
    'fail',
])->name('membership.payment.fail');

Route::post('/membership/payment/cancel', [
    ArtistMembershipPaymentController::class,
    'cancel',
])->name('membership.payment.cancel');

Route::post('/membership/payment/ipn', [
    ArtistMembershipPaymentController::class,
    'ipn',
])->name('membership.payment.ipn');

// ========================================
// AUTHENTICATED ROUTES
// ========================================

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // ------------------------------------
    // ARTWORK PURCHASE / CHECKOUT
    // Existing artwork purchase flow retained.
    // ------------------------------------

    Route::get('/artworks/{slug}/buy', [
        ArtworkPurchaseController::class,
        'create',
    ])->name('artworks.purchase.create');

    Route::post('/artworks/{slug}/buy', [
        ArtworkPurchaseController::class,
        'store',
    ])->name('artworks.purchase.store');

    Route::post('/artworks/delivery/geocode', [
        ArtworkPurchaseController::class,
        'geocodeDestination',
    ])->name('artworks.delivery.geocode');

    // ------------------------------------
    // ARTIST APPLICATION AND MEMBERSHIP
    // Pending artists must retain access.
    // ------------------------------------

    Route::get('/artist/application', [
        AuthController::class,
        'showArtistApplication',
    ])->name('artist.application');

    Route::post('/artist/application', [
        AuthController::class,
        'submitArtistApplication',
    ])->name('artist.application.submit');

    Route::post('/artist/application/payment', [
        ArtistMembershipPaymentController::class,
        'store',
    ])->name('artist.application.payment.store');

    // ------------------------------------
    // GENERAL PROFILE
    // Keep separate from artist-only profile.
    // ------------------------------------

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

    // ------------------------------------
    // ACTIVE ARTIST MEMBERS ONLY
    // Requires artist.active middleware.
    // ------------------------------------

    Route::middleware('artist.active')->group(function () {

        Route::get('/artist/my-profile', [
            ArtistProfileController::class,
            'edit',
        ])->name('artist-profile.edit');

        Route::put('/artist/my-profile', [
            ArtistProfileController::class,
            'update',
        ])->name('artist-profile.update');

        Route::get('/artist/studio', [
            ArtistArtworkController::class,
            'index',
        ])->name('artist.studio');

        Route::get('/artist/studio/artworks/create', [
            ArtistArtworkController::class,
            'create',
        ])->name('artist.artworks.create');

        Route::post('/artist/studio/artworks', [
            ArtistArtworkController::class,
            'store',
        ])->name('artist.artworks.store');

        Route::get('/artist/my-artworks/{artwork}/edit', [
            ArtistArtworkController::class,
            'edit',
        ])->name('artist.artworks.edit');

        Route::put('/artist/my-artworks/{artwork}', [
            ArtistArtworkController::class,
            'update',
        ])->name('artist.artworks.update');

        Route::delete('/artist/studio/artworks/{artwork}', [
            ArtistArtworkController::class,
            'destroy',
        ])->name('artist.artworks.destroy');
    });

    // ------------------------------------
    // ADMIN ONLY
    // Every admin route requires role:admin.
    // ------------------------------------

    Route::middleware('role:admin')->group(function () {

        Route::get('/admin', [
            AdminMembershipController::class,
            'index',
        ])->name('admin.dashboard');

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

        Route::post('/admin/memberships/{application}/approve', [
            AdminMembershipController::class,
            'approve',
        ])->name('admin.memberships.approve');

        Route::post('/admin/memberships/{application}/reject', [
            AdminMembershipController::class,
            'reject',
        ])->name('admin.memberships.reject');

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

        Route::get('/admin/artists', [
            AdminUserController::class,
            'artists',
        ])->name('admin.artists.index');

        Route::get('/admin/artists/{user}', [
            AdminUserController::class,
            'showArtist',
        ])->name('admin.artists.show');

        Route::get('/admin/art-lovers', [
            AdminUserController::class,
            'artLovers',
        ])->name('admin.art-lovers.index');

        Route::get('/admin/art-lovers/{user}', [
            AdminUserController::class,
            'showArtLover',
        ])->name('admin.art-lovers.show');
    });

    // ------------------------------------
    // NOTIFICATIONS
    // ------------------------------------

    Route::get('/notifications', [
        NotificationController::class,
        'index',
    ])->name('notifications.index');

    Route::get('/notifications/{notification}/open', [
        NotificationController::class,
        'open',
    ])->name('notifications.open');
});
