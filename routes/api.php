<?php

use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WebhookSubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — todas protegidas por token estático (StaticTokenAuth)
|--------------------------------------------------------------------------
*/

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::middleware('api.token')->group(function () {

    // Customers
    Route::get('/customers',         [CustomerController::class, 'index']);
    Route::post('/customers',        [CustomerController::class, 'store']);
    Route::get('/customers/{id}',    [CustomerController::class, 'show']);
    Route::put('/customers/{id}',    [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

    // Products (instâncias de Host/Talk2)
    Route::get('/products',         [ProductController::class, 'index']);
    Route::post('/products',        [ProductController::class, 'store']);
    Route::get('/products/{id}',    [ProductController::class, 'show']);
    Route::put('/products/{id}',    [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Cards (Kanban de ouvidoria)
    Route::get('/cards',         [CardController::class, 'index']);
    Route::post('/cards',        [CardController::class, 'store']);
    Route::get('/cards/{id}',    [CardController::class, 'show']);
    Route::put('/cards/{id}',    [CardController::class, 'update']);
    Route::delete('/cards/{id}', [CardController::class, 'destroy']);

    // Chats vinculados ao card
    Route::get('/cards/{cardId}/chats',         [ChatController::class, 'index']);
    Route::post('/cards/{cardId}/chats',        [ChatController::class, 'store']);
    Route::delete('/cards/{cardId}/chats/{id}', [ChatController::class, 'destroy']);

    // Webhook subscriptions
    Route::get('/webhooks',         [WebhookSubscriptionController::class, 'index']);
    Route::post('/webhooks',        [WebhookSubscriptionController::class, 'store']);
    Route::put('/webhooks/{id}',    [WebhookSubscriptionController::class, 'update']);
    Route::delete('/webhooks/{id}', [WebhookSubscriptionController::class, 'destroy']);
});
