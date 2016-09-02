<?php

namespace lib\Data;

class OutputManager {

    protected $stack;
    protected $alias;

    public function __construct($output_routes = [])
    {
        $this->stack = $output_routes;
        $this->setAlias('/([\\\\\/])/', DIRECTORY_SEPARATOR);
    }

    public function setAlias ($regex, $value)
    {
        $this->alias[] = array('regex' => $regex, 'value' => $value);
    }
    
    public function get ($key)
    {
        if (isset($this->stack[$key]))
            return $this->solve($this->stack[$key]);
        else Throw new \Exception();
    }
    
    public function solve ($route)
    {
        foreach ($this->alias as $alias)
            $route = preg_replace($alias['regex'], $alias['value'], $route);
        if (!file_exists($route))
            mkdir($route, 0777, true);
        return $route;
    }

}