<?php

use Locale\DOU;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DOUTest extends TestCase
{
    public function setUp(): void
    {
        $this->DOU = new DOU();
    }
    public function testCollectDataReturnArray()
    {
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse('TEST')
        ]));
        $this->assertIsArray($this->DOU->collectData(''));
    }

    public function testCollectDataReturnArrayList()
    {
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse('TEST')
        ]));
        $this->assertArrayHasKey('list', $this->DOU->collectData(''));
    }

    public function testCollectDataReturnEmptyListWhenDateIsInvalid()
    {
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse('TEST')
        ]));
        $this->assertEmpty($this->DOU->collectData('2020-01-30')['list']);        
    }

    public function testCollectDataReturnEmptyListWhenDateIsValid()
    {
        $html = <<<HTML
            <script id="params">
            {
                "jsonArray":["asdfsd"]
            }
            </script>
            HTML;
        $this->DOU->client = new HttpBrowser(new MockHttpClient([
            new MockResponse($html)
        ]));
        $this->assertNotEmpty($this->DOU->collectData('2020-01-29')['list']);        
    }    
}