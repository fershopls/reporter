<?php

namespace lib\Data;

class AttributesManager {

    protected $attributes = [];

    public function set ($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function get ($key)
    {
        if (isset($this->attributes[$key]))
            return $key;
        else return null;
    }

    public function getAttributes ()
    {
        return $this->attributes;
    }

    public function setAttributes ($array)
    {
        $this->attributes = $array;
    }

}