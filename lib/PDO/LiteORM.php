<?php

namespace lib\PDO;

class LiteORM {

    const AUTH_HOST = '';
    const AUTH_USER = '';
    const AUTH_PASS = '';

    public $conn;
    public $last_query;
    public $debug;

    public function __construct ($database, $debug = false)
    {
        $this->conn = $this->getConnection(self::AUTH_HOST, self::AUTH_USER, self::AUTH_PASS, $database);
        $this->debug = $debug;
    }

    public function getConnection ($host, $user, $pass, $database)
    {
        $conn = sqlsrv_connect($host, array(
            'Database' => $database,
            'UID' => $user,
            'PWD' => $pass,
        ));
        if ($conn)
            return $conn;
        elseif ($this->debug)
        {
            echo "Error connection" . PHP_EOL;
            print_r(sqlsrv_errors());
        }
    }

    public function query ($string, $params = [])
    {
        $this->last_query = sqlsrv_query($this->conn, $string, $params);
        return $this->last_query;
    }

    public function get ($q = null)
    {
        $q = $q?$q:$this->last_query;
        return sqlsrv_fetch_array($q);
    }

    public function getAll ($q = null)
    {
        $q = $q?$q:$this->last_query;
        if (!$q && $this->debug)
            echo "Query is null".PHP_EOL;
        if (!$q)
            return False;
        $rows = [];
        while ($r = sqlsrv_fetch_array($q))
            array_push($rows, $r);
        return $rows;
    }

}