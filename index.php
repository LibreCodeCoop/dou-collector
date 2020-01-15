<?php

use Locale\DOU;

require 'vendor/autoload.php';

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createMutable(__DIR__);
    $dotenv->load();
}

$DOU = new DOU([
    'baseUrl' => getenv('BASE_URL'),
    'maxRequests' => getenv('MAX_REQUESTS')
]);
$licitacoes = $DOU->collectData($_GET['date'], $_GET['keys']);

header('Content-Type: application/json');

echo json_encode($licitacoes);