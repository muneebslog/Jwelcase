<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/shop', 'pages::shop')->name('shop');
Route::livewire('/shop/{category}', 'pages::shop')->name('shop.category');
Route::livewire('/product/{product}', 'pages::product')->name('product.show');
Route::livewire('/cart', 'pages::cart')->name('cart');
Route::livewire('/about', 'pages::about')->name('about');
Route::livewire('/contact', 'pages::contact')->name('contact');
Route::livewire('/page/{page}', 'pages::cms-page')->name('pages.show');
Route::livewire('/inquiry/thanks', 'pages::inquiry-thanks')->name('inquiry.thanks');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');
    Route::livewire('/products', 'pages::admin.products-index')->name('products.index');
    Route::livewire('/products/create', 'pages::admin.product-form')->name('products.create');
    Route::livewire('/products/{product}/edit', 'pages::admin.product-form')->name('products.edit');
    Route::livewire('/categories', 'pages::admin.categories')->name('categories.index');
    Route::livewire('/pages', 'pages::admin.pages')->name('pages.index');
    Route::livewire('/inquiries', 'pages::admin.inquiries-index')->name('inquiries.index');
    Route::livewire('/inquiries/{inquiry}', 'pages::admin.inquiry-show')->name('inquiries.show');
    Route::livewire('/settings', 'pages::admin.settings')->name('settings.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/admin')->name('dashboard');
});

require __DIR__.'/settings.php';
