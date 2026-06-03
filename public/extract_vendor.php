<?php
if (($_GET['token'] ?? '') !== 'rh-setup-2024') { http_response_code(403); die('Unauthorized'); }
header('Content-Type: text/plain; charset=UTF-8');

$zip  = dirname(__DIR__) . '/scramble_vendor.zip';
$dest = dirname(__DIR__) . '/vendor';

if (!file_exists($zip)) { die("ERRO: zip nao encontrado"); }

$z = new ZipArchive();
if ($z->open($zip) !== true) { die("ERRO: nao abriu o zip"); }

echo "Extraindo {$z->numFiles} arquivos...\n"; flush();
$z->extractTo($dest);
$z->close();
unlink($zip);
echo "OK — vendor atualizado, zip removido.\n";
