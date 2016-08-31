<?php

namespace lib\PDO;

use PDO;

class Connection {

    protected $pdo_object;

    public function __construct($credentials, $database_slug)
    {
        $this->pdo_object = new PDO("sqlsrv:Server=".$credentials['hosting'].";Database=".$database_slug, $credentials['username'], $credentials['password']);
    }

    public function get ()
    {
        return $this->pdo_object;
    }

}