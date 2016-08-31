<?php

namespace lib\PDO;

use lib\PDO\MasterPDO;
use lib\Cache\Cache;

// Todo: Bug on cache

class DatabaseInterface {

    protected $pdo;
    protected $databases_array;
    protected $cache_driver;

    protected $callbacks = array();

    protected $string_query;
    protected $array_query;
    protected $cache_id;
    protected $cache_lifetime;

    public function __construct(MasterPDO $master, $databases_array, Cache $cache)
    {
        $this->pdo = $master;
        $this->databases_array = $databases_array;
        $this->cache_driver = $cache;
    }

    public function callback ($id, $callable)
    {
        $this->callbacks[$id] = $callable;
    }

    public function getCallback ($string_callback)
    {
        if (isset($this->callbacks[$string_callback]) && is_callable($this->callbacks[$string_callback]))
            return $this->callbacks[$string_callback];
        else return False;
    }

    public function setDatabases ($databases_array)
    {
        $this->databases_array = $databases_array;
    }

    public function set ($string_query)
    {
        $this->string_query = $string_query;
        $this->cache(false);
        $this->fill([]);
        return $this;
    }

    public function cache ($string_id = null, $lifeTime = 0)
    {
        $this->cache_id = $string_id;
        $this->cache_lifetime = $lifeTime;
        return $this;
    }

    public function fill ($query_parameters = [])
    {
        $this->array_query = $query_parameters;
        return $this;
    }

    public function execute($callable, $query_params = [])
    {
        if ($this->cache_id && $this->cache_driver->contains($this->cache_id))
            return $this->cache_driver->fetch($this->cache_id);

        $request = array('query'=>$this->string_query);
        $result = array();

        foreach ($this->databases_array as $database)
        {
            $request['database'] = $database;
            $con = $this->pdo->using($database)
                ->prepare($request['query']);
            $con->execute($this->array_query);
            $rows = $con->fetchAll();
            foreach ($rows as $row)
            {
                $request['row'] = $row;
                if (!is_callable($callable) && is_string($callable))
                    $callable = $this->getCallback($callable);
                $result = call_user_func_array($callable, [$request, $result]);
            }
        }

        if ($this->cache_id)
            $this->cache_driver->save($this->cache_id, $result, $this->cache_lifetime);

        return $result;
    }
}