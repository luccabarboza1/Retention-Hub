<?php
if (($_GET['token'] ?? '') !== 'rh-setup-2024') { http_response_code(403); die('Unauthorized'); }
header('Content-Type: text/plain; charset=UTF-8');

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
$app    = require_once $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdo = DB::connection()->getPdo();

echo "=== jobs pendentes ===\n";
$jobs = $pdo->query("SELECT id, queue, attempts, created_at, payload FROM jobs ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
if (!$jobs) { echo "Nenhum.\n"; }
foreach ($jobs as $j) {
    $payload = json_decode($j['payload'], true);
    echo "id={$j['id']} queue={$j['queue']} attempts={$j['attempts']} created={$j['created_at']}\n";
    echo "  job=" . ($payload['displayName'] ?? '?') . "\n";
}

echo "\n=== failed_jobs (ultimos 5) ===\n";
$failed = $pdo->query("SELECT id, queue, failed_at, exception FROM failed_jobs ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
if (!$failed) { echo "Nenhum.\n"; }
foreach ($failed as $f) {
    echo "id={$f['id']} queue={$f['queue']} failed_at={$f['failed_at']}\n";
    echo "  " . substr($f['exception'], 0, 200) . "\n";
}

echo "\n=== webhook_subscriptions ativas ===\n";
$subs = $pdo->query("SELECT id, name, trigger_types, is_active, url FROM webhook_subscriptions WHERE deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);
foreach ($subs as $s) {
    echo "id={$s['id']} active={$s['is_active']} triggers={$s['trigger_types']}\n  url={$s['url']}\n";
}
