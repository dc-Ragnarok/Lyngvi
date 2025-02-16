<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ragnarok\Lyngvi\StabilityBot;

require './_shared.php';

$token = env('TOKEN');
$devGuild = env('DEV_GUILD');

$log = new Logger('stability-bot');
$log->pushHandler(new StreamHandler('php://stdout'));

$bot = new StabilityBot(
    $token,
    $log,
    $fenrirVersion,
    $devGuild
);

$bot->discord->gateway->open();
