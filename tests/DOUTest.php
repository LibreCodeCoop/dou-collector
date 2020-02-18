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
    public function testCollectDataReturnGenerator()
    {
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse('TEST')
        ]));
        $actual = $this->DOU->collectData('', []);
        $this->assertInstanceOf(Generator::class, $actual);
    }

    public function testCollectDataReturnEmptyListWhenDateIsInvalid()
    {
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse('TEST')
        ]));
        $actual = $this->DOU->collectData('2020-01-30', []);
        $actual = $this->DOU->collectData('', []);
        $data = $actual->current();
        $this->assertNull($data);
    }

    public function testCollectDataReturnValidData()
    {
        $list = file_get_contents(__DIR__.'/Fixtures/list.html');
        $detail = file_get_contents(__DIR__.'/Fixtures/detail.html');
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse($list),
            new MockResponse($detail)
        ]));
        $current = $this->DOU->collectData('2020-01-29', ['aviso de licita'])->current();
        $this->assertIsObject($current);

        $this->assertObjectHasAttribute('publicado_dou_data', $current);
        $this->assertObjectHasAttribute('edicao_dou_data', $current);
        $this->assertObjectHasAttribute('identifica', $current);
        $this->assertObjectHasAttribute('dou_paragraph', $current);
        $this->assertObjectHasAttribute('assina', $current);
        $this->assertObjectHasAttribute('cargo', $current);
        $this->assertObjectHasAttribute('informacao_conteudo_dou', $current);
        $this->assertObjectHasAttribute('texto_dou', $current);

        $this->assertEquals('7', $current->edicao_dou_data);
        $this->assertEquals('10/01/2020', $current->publicado_dou_data);
        $this->assertIsInt(strpos($current->identifica, 'AVISO'));
        $this->assertIsInt(strpos($current->dou_paragraph, 'PROCESSO'));
        $this->assertIsInt(strpos($current->assina, 'JOHN'));
        $this->assertIsInt(strpos($current->cargo, 'Pregoeiro'));
        $this->assertIsInt(strpos($current->informacao_conteudo_dou, 'Este conteúdo'));
        $this->assertIsInt(strpos($current->texto_dou, 'class="informacao-conteudo-dou"'));
    }

    public function testCollectMultipleKeys()
    {
        $this->DOU = new DOU([
            'baseUrl' => 'http://localhost',
            'maxRequests' => 2
        ]);
        $list = file_get_contents(__DIR__.'/Fixtures/list.html');
        $detail = file_get_contents(__DIR__.'/Fixtures/detail.html');
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse($list),
            new MockResponse($detail),
            new MockResponse($list),
            new MockResponse($detail),
            new MockResponse($detail)
        ]));
        foreach($this->DOU->collectData('2020-01-29', ['aviso de licita']) as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
        $items = [];
        foreach($this->DOU->collectData('2020-01-29', ['aviso de licita', 'aviso de cancelamento']) as $item) {
            $items[] = $item;
        }
        $this->assertCount(2, $items);
    }
}
