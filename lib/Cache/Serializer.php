<?php

namespace lib\Cache;

class Serializer {

    public static function serialize ($array = [], $options = 0)
    {
        $serialized = json_encode($array, $options);
        if (empty($serialized))
            $serialized = json_encode(self::utf8ize($array), $options);
        return $serialized;
    }

    public static function unserialize ($plaintext = "", $assoc = true)
    {
        return json_decode($plaintext, $assoc);
    }

    public static function utf8ize ($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = self::utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }
    
}