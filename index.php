<?php
//docker-compose exec php7 php -S 0.0.0.0:8080

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

require 'vendor/autoload.php';

$client = new HttpBrowser(HttpClient::create());
$client->setServerParameter('HTTP_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');

$crawler = $client->request('GET', 'http://www.in.gov.br/leiturajornal?secao=do3&data=' . $_GET['data']);// 11-12-2019

$fullPageHtml = $crawler->html();

$json = $crawler->filter('#params')->text();
$obj = json_decode($json);

$licitacoes = array_filter($obj->jsonArray, function($v) {
    $pos = strpos(strtolower($v->artType), 'aviso de licita');
    return strpos(strtolower($v->artType), 'aviso de licita') !== false;
});

array_walk($licitacoes, function(&$v, $k) {
    $v = [
        'orgao'  => $v->hierarchyStr,
        'titulo' => $v->title,
        'texto'  => $v->content
    ];
});

header('Content-Type: application/json');

// echo $script."\n\n";

echo json_encode($licitacoes);