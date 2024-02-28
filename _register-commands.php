<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ragnarok\Fenrir\Bitwise\Bitwise;
use Ragnarok\Fenrir\Constants\Events;
use Ragnarok\Fenrir\Discord;
use Ragnarok\Fenrir\Enums\ApplicationCommandOptionType;
use Ragnarok\Fenrir\Enums\ApplicationCommandTypes;
use Ragnarok\Fenrir\Gateway\Events\Ready;
use Ragnarok\Fenrir\Parts\ApplicationCommand;
use Ragnarok\Fenrir\Rest\Helpers\Command\CommandBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Command\CommandOptionBuilder;

use function React\Promise\all;

require './_shared.php';

$log = new Logger('stability-bot-command-registration');
$log->pushHandler(new StreamHandler('php://stdout'));

$discord = (new Discord(env('TOKEN'), $log))
    ->withGateway(new Bitwise())
    ->withRest();

$discord->gateway->events->once(Events::READY, function (Ready $ready) use ($discord) {
    $applicationId = $ready->application->id;

    $discord->rest->globalCommand->getCommands($applicationId)->then(function (array $commands) use ($applicationId, $discord) {
        $deletions = array_map(
            fn (ApplicationCommand $command) => $discord->rest->globalCommand->deleteApplicationCommand($applicationId, $command->id),
            $commands
        );

        return all($deletions);
    })->then(function () use ($applicationId, $discord) {
        $commandRegistrations = [];
        $commandRegistrations[] = $discord->rest->globalCommand->createApplicationCommand(
            $applicationId,
            CommandBuilder::new()
                ->setName('status')
                ->setDescription('Generate a status report')
                ->setType(ApplicationCommandTypes::CHAT_INPUT),
        );

        $commandRegistrations[] = $discord->rest->globalCommand->createApplicationCommand(
            $applicationId,
            CommandBuilder::new()
                ->setName('cat')
                ->setDescription('Cat')
                ->setType(ApplicationCommandTypes::CHAT_INPUT)
                ->addOption(
                    CommandOptionBuilder::new()
                        ->setType(ApplicationCommandOptionType::STRING)
                        ->setName('says')
                        ->setDescription('hell do I know')
                ),
        );

        $commandRegistrations[] = $discord->rest->globalCommand->createApplicationCommand(
            $applicationId,
            CommandBuilder::new()
                ->setName('duck')
                ->setDescription('Quack')
                ->setType(ApplicationCommandTypes::CHAT_INPUT)
                ->addOption(
                    CommandOptionBuilder::new()
                        ->setType(ApplicationCommandOptionType::STRING)
                        ->setName('says')
                        ->setDescription('Duck can talk too now')
                ),
        );

        return all($commandRegistrations);
    })->then(function () {
        echo 'Commands registered succesfully', PHP_EOL;
        exit(0);
    });
});

$discord->gateway->open();
