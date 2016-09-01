<?php

namespace lib\Cache;

use lib\Cache\Serializer;
use Phine\Path\Path;

class CacheDriver {

    protected $serialize_dir;
    protected $life_time;

    public function __construct($serialize_dir)
    {
        if (file_exists($serialize_dir) && is_dir($serialize_dir))
            $this->serialize_dir = realpath($serialize_dir);
        $this->life_time = new LifeTime($serialize_dir);
    }

    protected function getRoute ($id)
    {
        return Path::join([$this->serialize_dir,md5($id).'.cache']);
    }

    public function save ($id, $data = [], $lifeTime = 0)
    {
        $route = $this->getRoute($id);
        $serialized = Serializer::serialize($data);
        file_put_contents($route, $serialized);
        $this->life_time->set($route, $lifeTime);
    }

    public function fetch ($id)
    {
        $route = $this->getRoute($id);
        if (file_exists($route))
            return Serializer::unserialize(file_get_contents($route));
        else return False;
    }

    public function contains ($id)
    {
        $route = $this->getRoute($id);
        return file_exists($route);
    }

    public function delete ($id)
    {
        $route = $this->getRoute($id);
        return unlink($route);
    }

    public function fallback ($id, $params = [], $callable, $lifeTime = 0)
    {
        if ($this->contains($id))
            return $this->fetch($id);
        $data = call_user_func_array($callable, $params);
        if (!$data)
            echo "[CACHE] [FALLBACK] [ERROR] Data returned by $id function is empty" . PHP_EOL;
        $this->save($id, $data, $lifeTime);
        return $data;
    }
}