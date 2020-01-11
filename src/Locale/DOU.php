<?php
namespace Locale;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class DOU
{
    public $client;
    public function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }
    public function collectData(string $date):array
    {
        $this->client->setServerParameter('HTTP_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');

        $crawler = $this->client->request('GET', 'http://www.in.gov.br/leiturajornal?secao=do3&data=' . $_GET['data']);// 11-12-2019
        
        $fullPageHtml = $crawler->html();
        
        $json = $crawler->filter('#params');
        if (!$json->count()) {
            return ['list' => []];
        }
        $json = $json->text();
        $obj = json_decode($json);
        
        $licitacoes = [];
        if (!empty($obj->jsonArray)) {
            $licitacoes = array_filter($obj->jsonArray, function($v) {
                $return = false;
                if (isset($v->artType)) {
                    $return = strpos(strtolower($v->artType), 'aviso de licita') !== false;
                }
                return $return;
            });
        }
        return ['list' => $licitacoes];
    }
}