<?php

namespace lib\Data;

use lib\Data\StringKey;
use lib\Data\AttributesManager;

class DataHandler {

    const KEY_CONCEPT_PREFIX = 'F_';

    protected $concepts = [];
    protected $headers = [];
    protected $database = [];

    public function getConcepts()
    {
        return $this->concepts;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setConceptHeader ($concept_id, $string) {
        $this->headers[self::KEY_CONCEPT_PREFIX . $concept_id] = $string;
    }
    
    public function getConceptId($string) {
        $key_string = StringKey::get($string);
        $key_concept = array_search($key_string, $this->getConcepts());

        if ($key_concept || $key_concept === 0)
        {
            return self::KEY_CONCEPT_PREFIX.$key_concept;
        }

        array_push($this->concepts, $key_string);
        $concept_id = max(array_keys($this->getConcepts()));
        $this->setConceptHeader($concept_id, $string);
        return self::KEY_CONCEPT_PREFIX.$concept_id;
    }

    public function enterprise ($callable) {
        if (is_callable($callable))
        {
            $attribute_manager = new AttributesManager();
            call_user_func_array($callable, [$attribute_manager]);

            $enterprise = array();
            foreach ($attribute_manager->getAttributes() as $attr => $value)
            {
                $concept_id = $this->getConceptId($attr);
                $enterprise[$concept_id] = $value;
            }

            return $enterprise;
        }
    }

}