<?php

use DouCollector\DOU;

require 'vendor/autoload.php';

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createMutable(getcwd());
    $dotenv->load();
}
$DOU = new DOU([
    'maxRequests' => $_ENV['MAX_REQUESTS']
]);
$keys = ['aviso de licita'];
foreach ($DOU->collectData('30-12-2019', $keys) as $licitacao) {
    $licitacoes[] = $licitacao;
}

header('Content-Type: application/json');

echo json_encode($licitacoes);