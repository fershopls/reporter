<?php

namespace lib\Cache;

use Phine\Path\Path;
use lib\Cache\Serializer;

class LifeTime {

    const FILENAME_SETTINGS = 'settings.json';

    protected $lifetime_dir;
    protected $settings;
    protected $settings_route;

    public function __construct($lifetime_dir)
    {
        $this->lifetime_dir = $lifetime_dir;
        $this->settings_route = Path::join([realpath($lifetime_dir), self::FILENAME_SETTINGS]);
        $this->settings = $this->retrieve();

        $this->refresh();
    }

    public function retrieve ()
    {
        if (file_exists($this->settings_route))
            return Serializer::unserialize(file_get_contents($this->settings_route));
        return [];
    }

    public function dump ()
    {
        file_put_contents($this->settings_route, Serializer::serialize($this->settings, JSON_PRETTY_PRINT));
    }

    public function set ($route, $lifeTime = 0)
    {
        $this->settings[md5($route)] = array(
            'route' => $route,
            'lifeTime' => $lifeTime,
            'microtime' => microtime(true)
        );
        $this->dump();
    }

    public function getLifeTime ($int = 0)
    {
        return $int*60; # Conversion 1 minute
    }

    public function refresh ()
    {
        foreach ($this->settings as $md5 => $properties)
        {
            if (!file_exists($properties['route']))
            {
                unset($this->settings[$md5]);
                continue;
            }
            $expiration = $properties['microtime'] + $this->getLifeTime($properties['lifeTime']);
            if (microtime(true) - $expiration > 0 && $properties['lifeTime']!=0)
            {
                unset($this->settings[$md5]);
                if (file_exists($properties['route']))
                    unlink($properties['route']);
            }
        }
        $this->dump();
    }
}