<?php

use Dotenv\Dotenv;
use Exan\StabilityBot\StabilityBot;
use Psr\Log\NullLogger;

require_once './vendor/autoload.php';


$lockFile = json_decode(file_get_contents('composer.lock'), true);

$dhpVersion = array_values(array_filter(
    $lockFile['packages'], fn ($package) => $package['name'] === 'exan/fenrir'
))[0]['version'];

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$bot = new StabilityBot($_ENV['TOKEN'], new NullLogger(), $dhpVersion);
$bot->register();

$bot->discord->gateway->connect();
