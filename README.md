# PHP Filesystem cache
**Filesystem cache** is a quick, simple and secure filesystem cache service builded with [PSR-6](http://www.php-fig.org/psr/psr-6/) rules.

## Installation

You can install it with Composer:

```
composer require minimalfw/filesystem-cache
```

## How to use it
Filesystem cache implement perfectly the *PSR-6* directive. So, you can use it very easily:
See an example:

```PHP
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use \Service\Cache\CacheItemPool;

$itemPool = new CacheItemPool(__DIR__ . '/../cache');
$itemCache = $itemPool->getItem('myArray');

if ($itemCache->isHit()) {
    echo 'Hit, hit! <br>';
    print_r( $itemCache->get() );
} else {
    $value = array(
        'name'      => 'Marco',
        'friends'   => array('Paolo','Luca')
    );

    $itemCache->set($value);
    $itemPool->save($itemCache);

    echo 'All saved! <br>';
    print_r( $value );
}
```

The **CacheItemPool** constructor accept the cache path. If you can't pass it, the default cache path will be:

```PHP
$_SERVER["DOCUMENT_ROOT"] . '/cache/'
```

For detail, you can see [PSR-6 documentation](http://www.php-fig.org/psr/psr-6/)

> Remember that you must create the **cache** directory with permission to write and read!
