<?php
namespace DouCollector;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Coletor de dados do DOU
 *
 * @see README.md
 */
class DOU
{
    /**
     * Cliente que fará as requisições
     *
     * @var HttpBrowser
     */
    public $client;
    /**
     * URL base para as requisições
     *
     * @var string
     */
    private $baseUrl;
    /**
     * Máximo de requisições que serão feitas, se 0 fará quantas for necessário
     *
     * @var integer
     */
    private $maxRequests = 0;
    /**
     * @param array{baseUrl:string, maxRequests:int} $settings
     */
    public function __construct(array $settings)
    {
        $this->baseUrl = $settings['baseUrl'];
        $this->maxRequests = $settings['maxRequests'];
        $this->client = new HttpBrowser(HttpClient::create());
    }
    /**
     * Search by date using a list of search strings
     *
     * @param string $date Format d-m-Y
     * @param array{string} $monitoringKeys Array with search strings
     * @return array{list:array} List containing sets of results
     */
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
            $licitacoes = array_filter($obj->jsonArray, function ($v) use ($monitoringKeys, &$requestCount) {
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
            array_walk($licitacoes, [$this, 'populateDetails']);
        }
        return ['list' => $licitacoes];
    }

    /**
     * Coleta mais detalhes sobre a licitação a partir da página
     *
     * @param \stdClass $data
     */
    private function populateDetails(\stdClass $data): \stdClass
    {
        $crawler = $this->client->request('GET', $this->baseUrl . '/web/dou/-/' . $data->urlTitle);// 11-12-2019
        $html = $crawler->html();
        preg_match('/jornal=(?P<jornal>\d+)/', $html, $matches);
        if ($matches) {
            $data->jornal = $matches['jornal'];
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

    /**
     * Pega um elemento a partir de um seletor css caso ele exista e cria em $data
     * este elemento, usando o seletor como nome da propriedade
     *
     * @param Crawler $crawler
     * @param \stdClass $data
     * @param string $selector
     */
    private static function populateElement(Crawler $crawler, \stdClass &$data, string $selector): void
    {
        $elements = $crawler->filter($selector);
        if ($elements->count()) {
            $property = str_replace('.', '', $selector);
            $property = str_replace('-', '_', $property);
            $node = $elements->getNode(0);
            if ($node) {
                $children = $node->childNodes;
                $data->{$property} = '';
                foreach ($children as $child) {
                    if ($node->ownerDocument) {
                        $data->{$property}.= $node->ownerDocument->saveHTML($child);
                    }
                }
            }
        }
    }
}
