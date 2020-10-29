<?php

use DouCollector\Commands\ExportCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ExportCommandTest extends TestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;
 
    protected function setUp(): void
    {
        $application = new Application();
        $application->add(new ExportCommand());
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
        $this->commandTester->execute([
            '--date' => '30-12-2019',
            '--keywords' => 'aviso de licita',
            '--format' => 'xml',
            '--maxRequests' => 1
        ]);
 
        $this->assertStringStartsWith("<?xml", trim($this->commandTester->getDisplay()));
        $this->assertStringEndsWith("</results>", trim($this->commandTester->getDisplay()));
    }

    public function testXMLWithoutResults()
    {
        $this->commandTester->execute([
            '--date' => '30-12-2019',
            '--keywords' => 'foo,bar',
            '--format' => 'xml',
            '--maxRequests' => 1
        ]);

        print_r($this->commandTester->getDisplay());
 
        $this->assertEmpty(trim($this->commandTester->getDisplay()));
    }
}