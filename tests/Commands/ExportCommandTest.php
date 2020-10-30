<?php

use Generator;
use DouCollector\DOU;
use DouCollector\Commands\ExportCommand;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ExportCommandTest extends TestCase
{

    /**
     * @var DOU|PHPUnit_Framework_MockObject_MockObject
     */
    private $douMock;

    /**
     * @var CommandTester
     */
    private $commandTester;

    protected function setUp(): void
    {
        $this->douMock = $this->getMockBuilder(DOU::class)
            ->disableOriginalConstructor()
            ->getMock();

        $application = new Application();
        $application->add(new ExportCommand($this->douMock));
        $command = $application->find('export');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->commandTester = null;
    }

    public function testWithoutDate()
    {
        $this->commandTester->execute([]);

        $this->assertEquals('A opção "date" é obrigatória.', trim($this->commandTester->getDisplay()));
    }

    public function testWithoutKeywords()
    {
        $this->commandTester->execute([
            '--date' => '30-12-2019'
        ]);

        $this->assertEquals('A opção "keywords" é obrigatória.', trim($this->commandTester->getDisplay()));
    }


    public function testIncorrectFormat()
    {
        $this->commandTester->execute([
            '--date' => '30-12-2019',
            '--keywords' => 'aviso de licita',
            '--format' => 'foo'
        ]);

        $this->assertEquals("Formato \"foo\" é inválido!\nOs formatos válidos são: json, xml", trim($this->commandTester->getDisplay()));
    }

    public function testJSON()
    {
        $this->douMock
            ->expects($this->any())
            ->method('collectData')
            ->willReturn($this->mockResult());

        $this->commandTester->execute([
            '--date' => '30-12-2019',
            '--keywords' => 'aviso de licita',
            '--format' => 'json',
            '--maxRequests' => 1
        ]);

        $this->assertStringStartsWith("[{", trim($this->commandTester->getDisplay()));
        $this->assertStringEndsWith("}]", trim($this->commandTester->getDisplay()));
    }

    public function testXML()
    {
        $this->douMock
            ->expects($this->any())
            ->method('collectData')
            ->willReturn($this->mockResult());

        $this->commandTester->execute([
            '--date' => '30-12-2019',
            '--keywords' => 'aviso de licita',
            '--format' => 'xml',
            '--maxRequests' => 1
        ]);

        $this->assertStringStartsWith("<?xml", trim($this->commandTester->getDisplay()));
        $this->assertStringEndsWith("</results>", trim($this->commandTester->getDisplay()));
    }

    private function mockResult()
    {
        yield ['urlTitle' => 'title'];
    }
}
