<?php

use Dotenv\Dotenv;
use Exan\StabilityBot\StabilityBot;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once './vendor/autoload.php';


$lockFile = json_decode(file_get_contents('composer.lock'), true);

$dhpVersion = array_values(array_filter(
    $lockFile['packages'], fn ($package) => $package['name'] === 'exan/fenrir'
))[0]['version'];

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$log = new Logger('stability-bot');

$log->pushHandler(new StreamHandler('./discord.log'));

$bot = new StabilityBot($_ENV['TOKEN'], $log, $dhpVersion);

$bot->register();

$bot->discord->connect();
