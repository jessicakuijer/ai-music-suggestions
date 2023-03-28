<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

// load all the .env files
if (is_array($env = @include dirname(__DIR__).'/.env.local.php') && ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? $env['APP_ENV'] ?? null) === ($env['APP_ENV'] ?? null)) {
    $_SERVER += $env;
    $_ENV += $env;
} else {
    (new Dotenv())->usePutenv()->loadEnv(dirname(__DIR__).'/.env');
}

$debug = ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? (bool) ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) !== 'prod') && !$test;
if ($debug) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

$trustedProxies = $trustedProxies ? explode(',', $trustedProxies) : [];
if ($_SERVER['APP_ENV'] == 'prod') {
    $trustedProxies[] = $_SERVER['REMOTE_ADDR'];
}
if (!empty($trustedProxies)) {
    Request::setTrustedProxies($trustedProxies, Request::HEADER_X_FORWARDED_AWS_ELB);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
