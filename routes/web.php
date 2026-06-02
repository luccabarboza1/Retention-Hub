<?php

use App\Http\Controllers\Web\BoardController;
use App\Http\Controllers\Web\CardWebController;
use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\SearchController;
use Illuminate\Support\Facades\Route;

// Documentação
Route::get('/docs', fn () => view('docs'))->name('docs');

// Busca universal
Route::get('/search', SearchController::class)->name('search');

// Board (Kanban)
Route::get('/',                             [BoardController::class,       'index'])->name('board');
Route::post('/columns',                     [BoardController::class,       'storeColumn'])->name('columns.store');
Route::patch('/columns/{column}',           [BoardController::class,       'updateColumn'])->name('columns.update');
Route::delete('/columns/{column}',          [BoardController::class,       'destroyColumn'])->name('columns.destroy');

// Cards
Route::patch('/cards/{card}/status',                   [CardWebController::class, 'updateStatus'])->name('cards.update-status');
Route::post('/cards/{card}/move',                      [CardWebController::class, 'moveStatus'])->name('cards.move');
Route::get('/cards/create',                            [CardWebController::class, 'create'])->name('cards.create');
Route::post('/cards',                                  [CardWebController::class, 'store'])->name('cards.store');
Route::get('/cards/{card}',                            [CardWebController::class, 'show'])->name('cards.show');
Route::patch('/cards/{card}',                          [CardWebController::class, 'update'])->name('cards.update');
Route::post('/cards/{card}/comments',                  [CardWebController::class, 'storeComment'])->name('cards.comments.store');
Route::delete('/cards/{card}/comments/{comment}',      [CardWebController::class, 'destroyComment'])->name('cards.comments.destroy');
Route::post('/cards/{card}/chats',                     [CardWebController::class, 'storeChat'])->name('cards.chats.store');
Route::patch('/cards/{card}/chats/{chat}/close',       [CardWebController::class, 'closeChat'])->name('cards.chats.close');

// Clientes
Route::get('/customers',                    [CustomerWebController::class, 'index'])->name('customers.index');
Route::get('/customers/create',             [CustomerWebController::class, 'create'])->name('customers.create');
Route::post('/customers',                   [CustomerWebController::class, 'store'])->name('customers.store');
Route::get('/customers/{customer}',         [CustomerWebController::class, 'show'])->name('customers.show');
Route::patch('/customers/{customer}',       [CustomerWebController::class, 'update'])->name('customers.update');
Route::get('/customers/{customer}/cards',   [CustomerWebController::class, 'cards'])->name('customers.cards');

// Produtos (vinculados a clientes)
Route::post('/customers/{customer}/products', [ProductWebController::class, 'store'])->name('products.store');
Route::patch('/products/{product}',           [ProductWebController::class, 'update'])->name('products.update');
Route::delete('/products/{product}',          [ProductWebController::class, 'destroy'])->name('products.destroy');
