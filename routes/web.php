<?php

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\AdminMembershipController;
use App\Http\Controllers\ArtistArtworkController;
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
// LOGOUT
// ========================================

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ========================================
// PROTECTED ROUTES
// ========================================

Route::middleware('auth')->group(function () {

    // ------------------------------------
    // ARTIST MEMBERSHIP APPLICATION
    // ------------------------------------

    Route::get('/artist/application', [
        AuthController::class,
        'showArtistApplication',
    ])->name('artist.application');

    Route::post('/artist/application', [
        AuthController::class,
        'submitArtistApplication',
    ])->name('artist.application.submit');

    // ------------------------------------
    // ARTIST MEMBERSHIP PAYMENT
    // ------------------------------------

    Route::post('/artist/application/payment', [
        AuthController::class,
        'submitMembershipPayment',
    ])->name('artist.application.payment');

    // ------------------------------------
    // ARTIST PROFILE EDITING
    // ------------------------------------

    Route::get('/artist/my-profile', [
        ArtistProfileController::class,
        'edit',
    ])->name('artist-profile.edit');

    Route::put('/artist/my-profile', [
        ArtistProfileController::class,
        'update',
    ])->name('artist-profile.update');

    
    // ------------------------------------
    // MY PROFILE — VIEW
    // ------------------------------------

    Route::get('/my-profile', [
        ArtistProfileController::class,
        'show',
    ])->name('my-profile');

    // ------------------------------------
    // ARTIST PROFILE — EDIT
    // ------------------------------------

    Route::get('/artist/my-profile', [
        ArtistProfileController::class,
        'edit',
    ])->name('artist-profile.edit');

    Route::put('/artist/my-profile', [
        ArtistProfileController::class,
        'update',
    ])->name('artist-profile.update');


    // ------------------------------------
    // ARTIST STUDIO
    // ------------------------------------

    Route::get('/artist/studio', [
        ArtistArtworkController::class,
        'index',
    ])->name('artist.studio');

    // ------------------------------------
    // CREATE / UPLOAD ARTWORK
    // ------------------------------------

    Route::get('/artist/studio/artworks/create', [
        ArtistArtworkController::class,
        'create',
    ])->name('artist.artworks.create');

    Route::post('/artist/studio/artworks', [
        ArtistArtworkController::class,
        'store',
    ])->name('artist.artworks.store');

    // ------------------------------------
    // DELETE OWN ARTWORK
    // ------------------------------------

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

    // ------------------------------------
    // APPROVE MEMBERSHIP
    // ------------------------------------

    Route::post('/admin/memberships/{application}/approve', [
        AdminMembershipController::class,
        'approve',
    ])->name('admin.memberships.approve');

    // ------------------------------------
    // REJECT MEMBERSHIP
    // ------------------------------------

    Route::post('/admin/memberships/{application}/reject', [
        AdminMembershipController::class,
        'reject',
    ])->name('admin.memberships.reject');

    // ------------------------------------
    // VERIFY ARTIST PAYMENT
    // ------------------------------------

    Route::post('/admin/memberships/{application}/verify-payment', [
        AdminMembershipController::class,
        'verifyPayment',
    ])->name('admin.memberships.verify-payment');
});
