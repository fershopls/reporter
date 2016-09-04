<?php

/*
 * Import Composer Autoload
 * */

define("MASTER_DIR", realpath(__DIR__));
require MASTER_DIR . '/vendor/autoload.php';


/*
 * Load Main Classes
 * */

use lib\Cache\CacheDriver;
use lib\Data\OutputManager;
use lib\Settings\SettingsManager;
use Phine\Path\Path;


/*
 * Set Timezone to Mexico
 * */
date_default_timezone_set("America/Mexico_City");


/*
 * Instance helper classes
 * */

$settings = new SettingsManager(include(Path::join([MASTER_DIR, 'support', 'config.php'])));

$output = new OutputManager($settings->get('DIRS'));
$output->setAlias('/^(\%[\\\\\/]?)/', MASTER_DIR . DIRECTORY_SEPARATOR);

$cache = new CacheDriver($output->get('cache'));