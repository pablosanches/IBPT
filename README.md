# IBPT
## _Simple SDK to consume IBPT API_

[![Build Status](https://travis-ci.org/pablosanches/ibpt.svg?branch=master)](https://travis-ci.org/pablosanches/ibpt)

## Features

    - Consume Products API;
    - Consume Services API;

## Installation

This SDK requires the composer installed.

Install the dependencies and devDependencies and start the server.

```json
    "require": {
        "pablosanches/ibpt": "dev-master"
    },
```

Then on you project directory...

```sh
    composer install
    composer update
```

## Usage

This SDK is very simple to use.

```php

    use PabloSanches\IBPT\IBPTClient;

    $config = array(
        'token' => '--your token here--'
    );

    self::$IBPT = IBPTClient::config($config);

    $response = static::$IBPT->Produto()->get(array(
        "cnpj" => "",
        "codigo" => "",
        "uf" => "",
        "descricao" => "",
        "unidadeMedida" => "",
        "valor" => 0
    ));

    $response = static::$IBPT->Servico()->get(array(
        "cnpj" => "",
        "codigo" => "",
        "uf" => "",
        "descricao" => "",
        "unidadeMedida" => "",
        "valor" => 0
    ));
```

### That's it! Now enjoy it! ;)
