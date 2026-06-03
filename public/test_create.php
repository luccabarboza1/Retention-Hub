<?php
if (($_GET['token'] ?? '') !== 'rh-setup-2024') { http_response_code(403); die('Unauthorized'); }
ini_set('display_errors', '1'); error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');
$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
$app    = require_once $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    $controller = new \App\Http\Controllers\Web\CustomerWebController();
    $reflection = new ReflectionMethod($controller, 'formOptions');
    $reflection->setAccessible(true);
    $opts = $reflection->invoke($controller);
    echo "formOptions OK\n";
    foreach ($opts as $k => $v) {
        $count = is_countable($v) ? count($v) : 'n/a';
        echo "  $k: $count items\n";
    }
    echo "\nTentando view customers.create...\n";
    $view = view('customers.create', $opts);
    $html = $view->render();
    echo "View renderizada OK (" . strlen($html) . " bytes)\n";
} catch (\Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . " linha " . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
