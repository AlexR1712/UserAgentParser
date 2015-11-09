# UserAgentParser
[![Build Status](https://travis-ci.org/ThaDafinser/UserAgentParser.svg)](https://travis-ci.org/ThaDafinser/UserAgentParser)
[![Code Coverage](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/?branch=master)

Different UA parse provider

## Installation
```
composer require thadafinser/user-agent-parser
```

## Example
```php
require 'vendor/autoload.php';

use UserAgentParser\Provider;

$userAgent = 'Mozilla/5 (X11; Linux x86_64) AppleWebKit/537.4 (KHTML like Gecko) Arch Linux Firefox/23.0 Xfce';

$dd = new Provider\YzalisUAParser();

/* @var $result \UserAgentParser\Model\UserAgent */
$result = $dd->parse($userAgent);
var_dump($result->toArray());
```

## Providers

### Overview

| Provider | Browser | RenderingEngine | Operating system | Device | Bot |
| --- | --- | --- | --- | --- | --- |
| [BrowscapPhp](https://github.com/browscap/browscap-php) | yes | yes | yes | yes | yes |
| [DonatjUAParser](https://github.com/donatj/PhpUserAgent) | yes | jiein | no | jiein | no |
| [PiwikDeviceDetector](https://github.com/piwik/device-detector) | yes | yes | yes | yes | yes |
| [UAParser](https://github.com/ua-parser/uap-php) | yes | no | yes | yes | yes |
| [WhichBrowser](https://github.com/WhichBrowser/WhichBrowser) | yes | yes | yes | yes | yes |
| [Woothee](https://github.com/woothee/woothee-php) | yes | no | jiein | jiein | yes |
| [YzalisUAParser](https://github.com/yzalis/UAParser) | yes | yes | yes | yes | no |

## How to build
`composer install -o`

`php vendor\browscap\browscap\bin\browscap build 6009`

`php bin\initCache.php`

`php bin\generateMatrixAll.php`
