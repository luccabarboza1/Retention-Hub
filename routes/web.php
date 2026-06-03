<?php

use App\Http\Controllers\Web\BoardController;
use App\Http\Controllers\Web\CardWebController;
use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\CustomerLookupController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\GeneralSettingsController;
use App\Http\Controllers\Web\ProductSettingsController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\WebhookSettingsController;
use Illuminate\Support\Facades\Route;

// Dashboard (home operacional + busca universal)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Documentação
Route::get('/docs', fn () => view('docs'))->name('docs');

// Busca universal (mantida para compatibilidade)
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

// Lookup de cliente via n8n
Route::post('/customers/lookup-email', [CustomerLookupController::class, 'lookup'])->name('customers.lookup');

// Clientes
Route::get('/customers',                    [CustomerWebController::class, 'index'])->name('customers.index');
Route::get('/customers/create',             [CustomerWebController::class, 'create'])->name('customers.create');
Route::post('/customers',                   [CustomerWebController::class, 'store'])->name('customers.store');
Route::get('/customers/{customer}',         [CustomerWebController::class, 'show'])->name('customers.show');
Route::patch('/customers/{customer}',       [CustomerWebController::class, 'update'])->name('customers.update');
Route::delete('/customers/{customer}',      [CustomerWebController::class, 'destroy'])->name('customers.destroy');
Route::get('/customers/{customer}/cards',   [CustomerWebController::class, 'cards'])->name('customers.cards');

// Opções de campos gerenciadas inline
Route::post('/settings/card-options/{type}',     [GeneralSettingsController::class, 'saveCardOptions'])->name('settings.card-options');
Route::post('/settings/customer-options/{type}', [GeneralSettingsController::class, 'saveCustomerOptions'])->name('settings.customer-options');
Route::post('/settings/options/check-usage',        [GeneralSettingsController::class, 'checkUsage'])->name('settings.options.check-usage');
Route::post('/settings/options/delete-and-replace', [GeneralSettingsController::class, 'deleteAndReplace'])->name('settings.options.delete-and-replace');

// Configurações de webhooks
Route::get('/settings/webhooks',                [WebhookSettingsController::class, 'index'])->name('settings.webhooks');
Route::post('/settings/webhooks',               [WebhookSettingsController::class, 'store'])->name('settings.webhooks.store');
Route::patch('/settings/webhooks/{webhook}',    [WebhookSettingsController::class, 'update'])->name('settings.webhooks.update');
Route::delete('/settings/webhooks/{webhook}',   [WebhookSettingsController::class, 'destroy'])->name('settings.webhooks.destroy');

// Hub de configurações
Route::get('/settings',          fn () => view('settings.index'))->name('settings.index');

// Configurações gerais
Route::get('/settings/general',  [GeneralSettingsController::class, 'index'])->name('settings.general');
Route::post('/settings/general', [GeneralSettingsController::class, 'update'])->name('settings.general.update');

// Configurações de planos de produto
Route::get('/settings/products',                        [ProductSettingsController::class, 'index'])->name('settings.products');
Route::post('/settings/products',                       [ProductSettingsController::class, 'store'])->name('settings.products.store');
Route::patch('/settings/products/{plan}',               [ProductSettingsController::class, 'update'])->name('settings.products.update');
Route::delete('/settings/products/{plan}',              [ProductSettingsController::class, 'destroy'])->name('settings.products.destroy');

// Produtos (vinculados a clientes)
Route::post('/customers/{customer}/products', [ProductWebController::class, 'store'])->name('products.store');
Route::patch('/products/{product}',           [ProductWebController::class, 'update'])->name('products.update');
Route::delete('/products/{product}',          [ProductWebController::class, 'destroy'])->name('products.destroy');
