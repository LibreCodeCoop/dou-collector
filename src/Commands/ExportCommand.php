<?php

namespace DouCollector\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DouCollector\DOU;
use Spatie\ArrayToXml\ArrayToXml;

class ExportCommand extends Command
{
    const FORMAT_WHITELIST = ["json", "xml"];

    protected function configure(): void
    {
        $this
            ->setName('export')
            ->setDescription('Exporta dados do DOU')
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_REQUIRED,
                'Data para realizar importação, exemplo: 30-12-2019'
            )
            ->addOption(
                'keywords',
                'k',
                InputOption::VALUE_REQUIRED,
                'Lista de palavras-chave para importar, separadas por vírgula.'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'O formato de saída (json, xml). [default: "json"]'
            )
            ->addOption(
                'maxRequests',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Máximo de requisições que serão feitas, se 0 fará quantas for necessário. [default: 3]'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = $input->getOption('date');
        $keywords = explode(",", $input->getOption('keywords'));
        $format = $input->getOption('format') ?? 'json';
        $maxRequests = $input->getOption('maxRequests') ?? 3;

        if (!$date) {
            $output->writeln('A opção "date" é obrigatória.');
            return Command::FAILURE;            
        }
        
        if (empty($keywords) || $keywords[0] == "") {
            $output->writeln('A opção "keywords" é obrigatória.');
            return Command::FAILURE;            
        }

        if (!in_array($format, self::FORMAT_WHITELIST)) {
            $output->writeln([
                "Formato \"{$format}\" é inválido!",
                'Os formatos válidos são: ' . implode(", ", self::FORMAT_WHITELIST)
            ]);
            return Command::FAILURE;
        }

        $DOU = new DOU([
            'maxRequests' => $maxRequests
        ]);
        foreach ($DOU->collectData($date, $keywords) as $result) {
            $results[] = $result;
        }
        
        
        if ($format == 'json') {
            $json = $this->toJSON($results);
            $output->writeln($json);
        }
        
        if ($format == 'xml') {
            $xml = $this->toXML($results);
            $output->writeln($xml);
        }

        return Command::SUCCESS;
    }

    private function toJSON (array $results)
    {
        $json = json_encode($results);
        return $json;
    }

    private function toXML (array $results)
    {
        $json = json_encode($results);
        $array = json_decode($json, true);
        $arrayWithValidKey = [];
        foreach ($array as $item) {
            $arrayWithValidKey[$item['urlTitle']] = $item;
        }
        $xml = ArrayToXml::convert($arrayWithValidKey, 'results');
        return $xml;
    }
}