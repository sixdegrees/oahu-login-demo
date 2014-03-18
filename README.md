# Oahu Login demo

## Installation

Install dependencies with [composer](https://getcomposer.org/doc/00-intro.md)

```
composer install
```

## Configuration

#### With Apache

```
SetEnv OAHU_CLIENT_ID   xxxxxxxx
SetEnv OAHU_APP_ID      yyyyyyyy
SetEnv OAHU_APP_SECRET  zzzzzzzz
```

### or replace config values in config.php

```
$config = array(
  "oahu" => array(
    "host"      => "app-staging.oahu.fr",
    "clientId"  => "xxxxxxxx",
    "appId"     => "yyyyyyyy",
    "appSecret" => "zzzzzzzz",
    ...
```