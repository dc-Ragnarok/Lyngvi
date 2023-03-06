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

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->required('TOKEN');
$dotenv->safeLoad();

$token = getenv('TOKEN');
$devGuild = getenv('DEV_GUILD');
$devGuild = $devGuild === false ? null : $devGuild;

$log = new Logger('stability-bot');
$log->pushHandler(new StreamHandler('php://stdout'));

$bot = new StabilityBot(
    $token,
    $log,
    $dhpVersion,
    $devGuild
);

$bot->register();

$bot->discord->gateway->connect();
