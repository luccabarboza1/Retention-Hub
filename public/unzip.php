<?php
if (($_GET['token'] ?? '') !== 'rh-setup-2024') { http_response_code(403); die('Unauthorized'); }

header('Content-Type: text/plain; charset=UTF-8');

$zipPath   = dirname(__DIR__) . '/vendor_flat.zip';
$extractTo = dirname(__DIR__) . '/vendor';

if (!file_exists($zipPath)) { die("ERRO: vendor_flat.zip nao encontrado"); }
if (!is_dir($extractTo))    { mkdir($extractTo, 0755, true); }

$zip = new ZipArchive();
if ($zip->open($zipPath) !== true) { die("ERRO: nao foi possivel abrir o zip"); }

echo "Extraindo {$zip->numFiles} arquivos para vendor/...\n";
flush();

$zip->extractTo($extractTo);
$zip->close();
unlink($zipPath);

echo "OK — vendor/ extraido e zip removido.\n";
