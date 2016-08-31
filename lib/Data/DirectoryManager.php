<?php

namespace lib\Data;

class DirectoryManager {

    protected $stack;

    public function __construct($directories = [])
    {
        foreach ($directories as $dir_key => $dir_route)
        {
            $this->set($dir_key, $dir_route);
        }
    }
    
    public function set ($key, $value)
    {
        $this->stack[$key] = $value;
    }
    
    public function get ($key)
    {
        if (isset($this->stack[$key]))
            return $this->solve($this->stack[$key]);
        else Throw new \Exception();
    }
    
    public function solve ($route)
    {
        
    }

}