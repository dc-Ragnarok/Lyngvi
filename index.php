<?php

use Dotenv\Dotenv;
use Ragnarok\Lyngvi\StabilityBot;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once './vendor/autoload.php';

$lockFile = json_decode(file_get_contents('composer.lock'), true);

$dhpVersion = array_values(array_filter(
    $lockFile['packages'], fn ($package) => $package['name'] === 'exan/fenrir'
))[0]['version'];

if (file_exists('.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

function env(string $key, mixed $default = null) {
    return $_ENV[$key] ?? getenv($key) ?? $default;
}

$log = new Logger('stability-bot');
$log->pushHandler(new StreamHandler('php://stdout'));

$bot = new StabilityBot(
    env('TOKEN'),
    $log,
    $dhpVersion,
    env('DEV_GUILD')
);

$bot->register();

$bot->discord->gateway->connect();
