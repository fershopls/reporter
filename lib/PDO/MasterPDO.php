<?php

namespace lib\PDO;

use lib\PDO\Connection;

class MasterPDO {
    protected $credentials;
    protected $connections_stack = array();

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
    }

    public function using ($database_slug, $stackable = True)
    {
        if (isset($this->connections_stack[$database_slug]))
        {
            $connection = $this->connections_stack[$database_slug];
        } else {
            $connection = $this->createConnection($database_slug);
            if ($connection && $stackable)
                $this->connections_stack[$database_slug] = $connection;
        }
        return $connection->get();
    }

    protected function createConnection ($database_slug)
    {
        return new Connection($this->credentials, $database_slug);
    }

    public function testConnection ($database_slug)
    {
        try {
            return $this->using($database_slug);
        } catch(\PDOException $e) {
            return False;
        }
    }

    public function setCredentials ($hosting, $username, $password)
    {
        $this->credentials = array(
            'hosting' => $hosting,
            'username' => $username,
            'password' => $password,
        );
    }
}