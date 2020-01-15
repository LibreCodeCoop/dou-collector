# Coletor de dados

Colete dados do DOU

## Composer

```bash
composer require lyseontech/dou-collector
```

Exemplo de uso:

```php
$DOU = new DouCollector\DOU([
    'baseUrl' => 'http://www.baseurldosite.com.br',
    // Caso não queira colocar um limit, passe zero
    'maxRequests' => 0
]);

// Data para a busca
$data = '30-12-2019';
// Irá buscar no título da publicação
$palavrasChave = ['edital'];

$licitacoes = $DOU->collectData($data, $palavrasChave);
```

> OBS: Este exemplo encontra-se implementado na pasta `example`

## Testes

Testes unitários implementados com PHPUnit, para executar os testes:
```bash
vendor/bin/phpunit
```