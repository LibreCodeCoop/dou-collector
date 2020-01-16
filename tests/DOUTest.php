<?php

use DouCollector\DOU;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DOUTest extends TestCase
{
    /**
     * @var DOU
     */
    public $DOU;
    public function setUp(): void
    {
        $this->DOU = new DOU([
            'baseUrl' => 'http://localhost',
            'maxRequests' => 1
        ]);
    }
    public function testCollectDataReturnArray()
    {
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse('TEST')
        ]));
        $this->assertIsArray($this->DOU->collectData('', []));
    }

    public function testCollectDataReturnArrayList()
    {
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse('TEST')
        ]));
        $this->assertArrayHasKey('list', $this->DOU->collectData('', []));
    }

    public function testCollectDataReturnEmptyListWhenDateIsInvalid()
    {
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse('TEST')
        ]));
        $this->assertEmpty($this->DOU->collectData('2020-01-30', [])['list']);
    }

    public function testCollectDataReturnValidData()
    {
        $list = file_get_contents(__DIR__.'/Fixtures/list.html');
        $detail = file_get_contents(__DIR__.'/Fixtures/detail.html');
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse($list),
            new MockResponse($detail)
        ]));
        $list = $this->DOU->collectData('2020-01-29', ['aviso de licita'])['list'];
        $this->assertNotEmpty($list);

        $this->assertObjectHasAttribute('publicado_dou_data', $list[0]);
        $this->assertObjectHasAttribute('edicao_dou_data', $list[0]);
        $this->assertObjectHasAttribute('identifica', $list[0]);
        $this->assertObjectHasAttribute('dou_paragraph', $list[0]);
        $this->assertObjectHasAttribute('assina', $list[0]);
        $this->assertObjectHasAttribute('cargo', $list[0]);
        $this->assertObjectHasAttribute('informacao_conteudo_dou', $list[0]);
        $this->assertObjectHasAttribute('texto_dou', $list[0]);

        $this->assertEquals('7', $list[0]->edicao_dou_data);
        $this->assertEquals('10/01/2020', $list[0]->publicado_dou_data);
        $this->assertIsInt(strpos($list[0]->identifica, 'AVISO'));
        $this->assertIsInt(strpos($list[0]->dou_paragraph, 'PROCESSO'));
        $this->assertIsInt(strpos($list[0]->assina, 'JOHN'));
        $this->assertIsInt(strpos($list[0]->cargo, 'Pregoeiro'));
        $this->assertIsInt(strpos($list[0]->informacao_conteudo_dou, 'Este conteúdo'));
        $this->assertIsInt(strpos($list[0]->texto_dou, 'class="informacao-conteudo-dou"'));
    }
}
