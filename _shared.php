<?php

use Dotenv\Dotenv;

require_once './vendor/autoload.php';

$lockFile = json_decode(file_get_contents('composer.lock'), true);

$fenrirVersion = array_values(array_filter(
    $lockFile['packages'], fn ($package) => $package['name'] === 'exan/fenrir'
))[0]['version'];

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$dotenv->required('TOKEN');

function env(string $key, mixed $default = null) {
    $var = isset($_ENV[$key]) ? $_ENV[$key] : getenv($key);

    return $var === false ? $default : $var;
}
