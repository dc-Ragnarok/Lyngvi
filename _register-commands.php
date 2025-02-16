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
use React\Promise\PromiseInterface;

use function React\Promise\all;

require './_shared.php';

$log = new Logger('stability-bot-command-registration');
$log->pushHandler(new StreamHandler('php://stdout'));

$devGuild = env('DEV_GUILD');

$discord = (new Discord(env('TOKEN'), $log))
    ->withGateway(new Bitwise())
    ->withRest();

$discord->gateway->events->once(Events::READY, function (Ready $ready) use ($devGuild, $discord) {
    /** @var Discord $discord */

    $applicationId = $ready->application->id;

    $discord->rest->{$devGuild ? 'guildCommand' : 'globalCommand'}->getCommands(
        ...array_filter([$devGuild, $applicationId])
    )->then(function (array $commands) use ($applicationId, $devGuild, $discord) {
        $delete = $devGuild ? function (string $commandId) use ($applicationId, $devGuild, $discord): PromiseInterface {
            return $discord->rest->guildCommand->deleteApplicationCommand($applicationId, $devGuild, $commandId);
        } : function (string $commandId) use ($applicationId, $devGuild, $discord): PromiseInterface {
            return $discord->rest->globalCommand->deleteApplicationCommand($applicationId, $commandId);
        };

        $deletions = array_map(
            fn (ApplicationCommand $command) => $delete($command->id),
            $commands
        );

        return all($deletions);
    })->then(function () use ($applicationId, $devGuild, $discord) {
        $commandRegistrations = [];

        $register = $devGuild ? function (CommandBuilder $commandBuilder) use ($discord, $devGuild, $applicationId, &$commandRegistrations) {
            $commandRegistrations[] = $discord->rest->guildCommand->createApplicationCommand($applicationId, $devGuild, $commandBuilder);
        } : function (CommandBuilder $commandBuilder) use ($discord, $applicationId, &$commandRegistrations) {
            $commandRegistrations[] = $discord->rest->globalCommand->createApplicationCommand($applicationId, $commandBuilder);
        };

        $register(
            CommandBuilder::new()
                ->setName('status')
                ->setDescription('Generate a status report')
                ->setType(ApplicationCommandTypes::CHAT_INPUT),
        );

        $register(
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

        $register(
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
