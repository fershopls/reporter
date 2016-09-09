# Install SQLServer Drivers for PHP
__Install Composer__
https://getcomposer.org/

__SQLSRV downloads:__
https://www.microsoft.com/en-us/download/details.aspx?id=20098
https://juanalbertogt.wordpress.com/2014/04/12/configurar-conexion-sql-server-con-php-php-sql-server-2008/
(Check PHP version and PHP thread safety)

# Setup settings
Copy `/config.php.txt` into a new folder called support: `/support/config.php`. Then fill it with your credentials.

```php
<?php

return array (
    'SQLSRV' => array(
        'database' => '',
        'username' => '',
        'password' => '',
    ),

    'DIRS' => array(
        'cache' => '%/support/cache',
        'output' => '%/support/output',
        'request' => '%/support/request',
        'logs' => '%/support/logs',
    ),

    'DEFAULT' => array(
        'cache_store_minutes' => 120,
    ),

    'LISTENER' => 'commands',
);
```