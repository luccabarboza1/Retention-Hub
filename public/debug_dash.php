<?php
if (($_GET['token'] ?? '') !== 'rh-setup-2024') { http_response_code(403); die('Unauthorized'); }
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
$app    = require_once $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $total   = \App\Models\Customer::count();
    echo "customers: $total\n";
    $cards   = \App\Models\Card::count();
    echo "cards: $cards\n";
    $mrr     = \App\Models\Customer::whereNotNull('monthly_fee')->sum('monthly_fee');
    echo "mrr: $mrr\n";
    $att     = \App\Models\Product::where('product_type', 'Talk2')->sum('attendants_count');
    echo "talk2 attendants: $att\n";
    echo "OK — todas queries passaram\n";
} catch (\Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . " linha " . $e->getLine() . "\n";
}
