<?php

use Paydemo\Kernel;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

$env = getenv('APP_ENV', true);
if ($env === false) {
    throw new \RuntimeException("An environment variable 'APP_ENV' is not defined.");
}

if (Kernel::isLocal()) {
    $dotenv_file_path = __DIR__ . '/../.env';
    if (!file_exists($dotenv_file_path)) {
        throw new \RuntimeException("A .env file doesn't exist.");
    }

    umask(0000);
}

if (Kernel::isProd() || Kernel::isStaging() || Kernel::isTest()) {
    $vpc_cidr = getenv('VPC_CIDR', true);
    if ($vpc_cidr === false) {
        throw new \RuntimeException("An environment variable 'VPC_CIDR' is not defined.");
    }

    Request::setTrustedProxies([$vpc_cidr], Request::HEADER_X_FORWARDED_FOR);
}

$kernel = new Kernel($env, Kernel::isDev());
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
