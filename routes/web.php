<?php

use App\Http\Controllers\GalleryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GalleryController::class, 'home'])->name('home');

Route::get('/artworks', [GalleryController::class, 'artworks'])->name('artworks.index');
Route::get('/artworks/{slug}', [GalleryController::class, 'artwork'])->name('artworks.show');

Route::get('/artists', [GalleryController::class, 'artists'])->name('artists.index');
Route::get('/artists/{slug}', [GalleryController::class, 'artist'])->name('artists.show');

Route::get('/exhibitions', [GalleryController::class, 'exhibitions'])->name('exhibitions.index');
Route::get('/exhibitions/{slug}', [GalleryController::class, 'exhibition'])->name('exhibitions.show');

Route::view('/favorites', 'artfy.page', [
    'title' => 'Your favorites',
    'eyebrow' => 'A personal collection',
    'description' => 'Save the artworks that speak to you and keep your favorite discoveries close.',
    'pageType' => 'favorites',
])->name('favorites.index');

Route::view('/artist/studio', 'artfy.page', [
    'title' => 'Artist studio',
    'eyebrow' => 'For artists',
    'description' => 'Your creative space to build a portfolio, share new work, and take part in exhibitions.',
    'pageType' => 'studio',
])->name('artist.studio');

Route::view('/admin', 'artfy.page', [
    'title' => 'Admin dashboard',
    'eyebrow' => 'Platform management',
    'description' => 'A central workspace for managing artists, artworks, categories, and exhibitions.',
    'pageType' => 'admin',
])->name('admin.dashboard');

Route::view('/login', 'artfy.page', [
    'title' => 'Welcome back',
    'eyebrow' => 'Sign in to Artfy',
    'description' => 'Sign-in is not connected yet. You can continue exploring the gallery while authentication is being built.',
    'pageType' => 'login',
])->name('login');

Route::view('/register', 'artfy.page', [
    'title' => 'Join the Artfy community',
    'eyebrow' => 'Create an account',
    'description' => 'Registration is not connected yet. Discover the gallery now and come back to create your account soon.',
    'pageType' => 'register',
])->name('register');
