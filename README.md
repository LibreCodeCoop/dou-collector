[![Build Status](https://travis-ci.org/LyseonTech/dou-collector.svg?branch=master)](https://travis-ci.org/LyseonTech/dou-collector)
[![Coverage Status](https://coveralls.io/repos/github/LyseonTech/dou-collector/badge.svg?branch=master)](https://coveralls.io/github/LyseonTech/dou-collector?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-blue.svg)](https://php.net/)

# Coletor de dados

Colete dados do DOU - Diário Oficial da União

## Composer

```bash
composer require lyseontech/dou-collector
```

Exemplo de uso:

```php
$DOU = new DouCollector\DOU([
    // Caso não queira colocar um limit, passe zero
    'maxRequests' => 0
]);

// Data para a busca
$data = '30-12-2019';
// Irá buscar no título da publicação
$palavrasChave = ['aviso de licita'];

foreach ($DOU->collectData($data, $palavrasChave) as $licitacao) {
    $licitacoes[] = $licitacao;
}
```

> OBS: Este exemplo encontra-se implementado na pasta `example`

## Testes

Testes unitários implementados com PHPUnit, para executar os testes:
```bash
vendor/bin/phpunit
```
