<?php

namespace DouCollector\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DouCollector\DOU;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Comando para exportar em JSON ou XML via linha de comando
 */
class ExportCommand extends Command
{
    /**
     * Classe DOU
     *
     * @var DOU
     */
    private $DOU;

    /**
     * Lista de formatos válidos
     *
     * @var array<int, string>
     */
    private $formatWhitelist;

    public function __construct(DOU $DOU)
    {
        parent::__construct();
        $this->DOU = $DOU;
        $this->formatWhitelist = ["json", "xml"];
    }

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
        $results = [];
        $options = $input->getOptions();
        $date = $options['date'];
        $keywords = explode(",", $options['keywords']);
        $format = $options['format'] ?? 'json';
        $maxRequests = $options['maxRequests'] ?? 3;

        if (!$date) {
            $output->writeln('<error>A opção "date" é obrigatória.</error>');
            return Command::FAILURE;
        }

        if (empty($keywords) || $keywords[0] == "") {
            $output->writeln('<error>A opção "keywords" é obrigatória.</error>');
            return Command::FAILURE;
        }

        if (!in_array($format, $this->formatWhitelist)) {
            $output->writeln([
                "<error>Formato \"{$format}\" é inválido!</error>",
                'Os formatos válidos são: <comment>' .
                implode(", ", $this->formatWhitelist) .
                '</comment>'
            ]);
            return Command::FAILURE;
        }

        foreach ($this->DOU->collectData($date, $keywords) as $result) {
            $results[] = $result;
        }

        $formattedResult = self::{'to' . strtoupper($format)}($results);

        $output->writeln($formattedResult);
        return Command::SUCCESS;
    }

    /**
     * Converte array em JSON
     *
     * @param array<string> $results
     * @return string|false
     */
    private static function toJSON(array $results)
    {
        $json = json_encode($results);
        return $json;
    }

    /**
     * Converte array em XML
     *
     * @param array<string> $results
     */
    private static function toXML(array $results): string
    {
        $json = json_encode((array) $results);

        $array = json_decode((string) $json, true);
        $arrayWithValidKey = [];
        foreach ($array as $item) {
            $arrayWithValidKey[$item['urlTitle']] = $item;
        }
        $xml = ArrayToXml::convert($arrayWithValidKey, 'results');
        return $xml;
    }
}
