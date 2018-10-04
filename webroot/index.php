<?php
defined('SST') or define('SST', microtime(true));
defined('SR') or define('SR', '/');
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config.php';
new App\Core($config, (isset($_REQUEST['db']) && !empty($_REQUEST['db']) ? $_REQUEST['db'] : 'db'));