<?php
define('SETUP_TOKEN', 'rh-setup-2024');
define('BASE_PATH', realpath(__DIR__ . '/..'));

if (($_GET['token'] ?? '') !== SETUP_TOKEN) {
    http_response_code(403);
    die('Forbidden.');
}

header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== PHP Info ===\n";
echo "Versao: " . PHP_VERSION . "\n";
echo "SO: " . PHP_OS . "\n";
echo "Base path: " . BASE_PATH . "\n";
echo "Usuario: " . get_current_user() . "\n";
echo "Funcoes desabilitadas: " . ini_get('disable_functions') . "\n";
echo "open_basedir: " . ini_get('open_basedir') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

echo "\n=== Arquivos na raiz ===\n";
foreach (scandir(BASE_PATH) as $f) {
    if ($f !== '.' && $f !== '..') echo "$f\n";
}

echo "\n=== .env existe? ===\n";
echo file_exists(BASE_PATH . '/.env') ? "SIM\n" : "NAO\n";

echo "\n=== vendor existe? ===\n";
echo file_exists(BASE_PATH . '/vendor') ? "SIM\n" : "NAO\n";

echo "\n=== exec() disponivel? ===\n";
$disabled = explode(',', ini_get('disable_functions'));
$disabled = array_map('trim', $disabled);
$execOk = !in_array('exec', $disabled) && function_exists('exec');
echo $execOk ? "SIM\n" : "NAO — exec() desabilitado\n";

if ($execOk) {
    echo "\n=== Composer disponivel? ===\n";
    exec('which composer 2>&1', $out, $code);
    echo implode("\n", $out) . " (exit: $code)\n";

    echo "\n=== PHP no PATH? ===\n";
    exec('which php 2>&1', $out2, $code2);
    echo implode("\n", $out2) . " (exit: $code2)\n";
}

if (isset($_GET['install']) && $_GET['install'] === '1' && $execOk) {
    echo "\n=== Rodando composer install ===\n";
    $cmd = 'cd ' . BASE_PATH . ' && composer install --no-dev --optimize-autoloader --no-interaction 2>&1';
    exec($cmd, $lines, $exitCode);
    echo implode("\n", $lines) . "\n";
    echo "Exit: $exitCode\n";

    echo "\n=== Gerando APP_KEY ===\n";
    exec('cd ' . BASE_PATH . ' && php artisan key:generate --force 2>&1', $lines2, $code3);
    echo implode("\n", $lines2) . "\n";

    exec('cd ' . BASE_PATH . ' && php artisan config:cache 2>&1', $lines3);
    exec('cd ' . BASE_PATH . ' && php artisan route:cache 2>&1', $lines4);
    echo "Caches gerados.\n";
}

if (isset($_GET['migrate']) && $_GET['migrate'] === '1' && $execOk) {
    echo "\n=== Rodando migrations ===\n";
    exec('cd ' . BASE_PATH . ' && php artisan migrate --force 2>&1', $lines5, $code4);
    echo implode("\n", $lines5) . "\n";
    echo "Exit: $code4\n";
}

echo "\n=== Fim ===\n";
