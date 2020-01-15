<?php
namespace DouCollector;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class DOU
{
    public $client;
    private $baseUrl;
    private $maxRequests;
    public function __construct(array $settings)
    {
        $this->baseUrl = $settings['baseUrl'];
        $this->maxRequests = $settings['maxRequests'];
        $this->client = new HttpBrowser(HttpClient::create());
    }
    public function collectData(string $date, array $monitoringKeys):array
    {
        $this->client->setServerParameter('HTTP_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');

        $crawler = $this->client->request('GET', $this->baseUrl . '/leiturajornal?secao=do3&data=' . $date);// 11-12-2019

        $json = $crawler->filter('#params');
        if (!$json->count()) {
            return ['list' => []];
        }
        $json = $json->text();
        $obj = json_decode($json);
        
        $licitacoes = [];
        $requestCount = 0;
        if (!empty($obj->jsonArray)) {
            $licitacoes = array_filter($obj->jsonArray, function($v) use ($monitoringKeys, &$requestCount) {
                if ($this->maxRequests && $requestCount >= $this->maxRequests) {
                    return false;
                }
                $exists = false;
                if (isset($v->artType)) {
                    foreach ($monitoringKeys as $key) {
                        $exists = strpos(strtolower($v->artType), $key) !== false;
                    }
                }
                if ($this->maxRequests && $exists) {
                    $requestCount++;
                }
                return $exists;
            });
        }
        foreach ($licitacoes as $key => $data) {
            $licitacoes[$key] = $this->populateDetails($data);
        }
        return ['list' => $licitacoes];
    }

    private function populateDetails(\stdClass $data)
    {
        $crawler = $this->client->request('GET', $this->baseUrl . '/web/dou/-/' . $data->urlTitle);// 11-12-2019
        $a = $crawler->filter('.botao-materia a');
        if ($a->count()) {
            parse_str(parse_url($a->attr('href'))['query'], $query);
            $data->jornal = $query['jornal'];
        }

        $this->populateElement($crawler, $data, '.publicado-dou-data');
        $this->populateElement($crawler, $data, '.edicao-dou-data');
        $this->populateElement($crawler, $data, '.identifica');
        $this->populateElement($crawler, $data, '.dou-paragraph');
        $this->populateElement($crawler, $data, '.assina');
        $this->populateElement($crawler, $data, '.cargo');
        $this->populateElement($crawler, $data, '.informacao-conteudo-dou');
        $this->populateElement($crawler, $data, '.texto-dou');

        return $data;
    }

    private function populateElement($crawler, &$data, $selector)
    {
        $elements = $crawler->filter($selector);
        if ($elements->count()) {
            $property = str_replace('.', '', $selector);
            $property = str_replace('-', '_', $property);
            $children = $elements->getNode(0)->childNodes;
            $data->{$property} = '';
            foreach ($children as $child) {
                $data->{$property}.= $elements->getNode(0)->ownerDocument->saveHTML($child);
            }
        }
    }
}
