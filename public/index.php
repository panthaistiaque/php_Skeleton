<?php

declare(strict_types=1);

/**
 * Front controller - the only entry point of the application.
 */

use App\Core\App;
use App\Core\Config;
use App\Core\Env;
use App\Core\Logger;
use App\Core\Session;

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/Views');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('DATABASE_PATH', ROOT_PATH . '/database');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// 1. Composer autoloader
$autoload = ROOT_PATH . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    exit('Composer vendor/autoload.php is missing. Run: composer install');
}
require $autoload;

// 2. Environment
Env::load(ROOT_PATH . '/.env');

// 3. Global helper functions
require APP_PATH . '/Helpers/functions.php';

// 4. Configuration
Config::set(require CONFIG_PATH . '/config.php');

date_default_timezone_set((string)Config::get('default_settings.general.timezone', 'UTC'));

// 5. Error handling
error_reporting(E_ALL);
ini_set('display_errors', Config::get('app.debug') ? '1' : '0');
ini_set('log_errors', '1');

set_exception_handler(function (\Throwable $e) {
    Logger::exception($e);
    http_response_code(500);
    if ((bool)Config::get('app.debug', false)) {
        echo '<h1>Uncaught Exception</h1><pre style="white-space:pre-wrap;">' . e($e->getMessage()) . "\n\n" . e($e->getTraceAsString()) . '</pre>';
    } else {
        echo 'A server error occurred.';
    }
    exit(1);
});

// 6. Secure session
Session::start();

// 7. Dispatch
$routes = require CONFIG_PATH . '/routes.php';
(new App($routes))->dispatch();