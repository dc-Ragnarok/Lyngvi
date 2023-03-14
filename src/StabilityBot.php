<?php

namespace Ragnarok\Lyngvi;

use Carbon\Carbon;
use Exan\Fenrir\Bitwise\Bitwise;
use Exan\Fenrir\Command\FiredCommand;
use Exan\Fenrir\Command\Helpers\InteractionCallbackBuilder;
use Exan\Fenrir\Discord;
use Exan\Fenrir\Enums\Command\InteractionCallbackTypes;
use Exan\Fenrir\Enums\Parts\ApplicationCommandOptionTypes;
use Exan\Fenrir\Enums\Parts\ApplicationCommandTypes;
use Exan\Fenrir\Rest\Helpers\Command\CommandBuilder;
use Exan\Fenrir\Rest\Helpers\Command\CommandOptionBuilder;
use Psr\Log\LoggerInterface;

class StabilityBot
{
    public Discord $discord;
    private Carbon $startTime;

    public function __construct(
        private string $token,
        private LoggerInterface $logger,
        private string $libraryVersion,
        private ?string $devGuild = null
    ) {
        $this->discord = (new Discord(
            $token,
            $logger
        ))
            ->withGateway(new Bitwise())
            ->withRest()
            ->withCommandHandler($this->devGuild);

        $this->startTime = new Carbon();
    }

    public function register()
    {
        $this->discord->command->registerCommand(
            CommandBuilder::new()
                ->setName('status')
                ->setDescription('Generate a status report')
                ->setType(ApplicationCommandTypes::CHAT_INPUT),
            function (FiredCommand $command) {
                $report = new Report(
                    $this->libraryVersion,
                    $this->startTime
                );

                $command->createInteractionResponse($report->toInteractionCallback());
            }
        );

        $this->discord->command->registerCommand(
            CommandBuilder::new()
                ->setName('cat')
                ->setDescription('Cat')
                ->setType(ApplicationCommandTypes::CHAT_INPUT)
                ->addOption(
                    CommandOptionBuilder::new()
                        ->setType(ApplicationCommandOptionTypes::STRING)
                        ->setName('says')
                        ->setDescription('hell do I know')
                ),
            function (FiredCommand $command) {
                $command->createInteractionResponse(
                    InteractionCallbackBuilder::new()
                        ->setType(InteractionCallbackTypes::DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE)
                )->then(static function () {
                    return Cat::fetch();
                })->then(static function (Cat $cat) use ($command) {
                    if ($command->hasOption('says')) {
                        $cat->says($command->getOption('says')->value);
                    }

                    $command->editInteractionResponse(
                        $cat->toWebhook()
                    );
                });
            }
        );

        $this->discord->command->registerCommand(
            CommandBuilder::new()
                ->setName('duck')
                ->setDescription('Quack')
                ->setType(ApplicationCommandTypes::CHAT_INPUT)
                ->addOption(
                    CommandOptionBuilder::new()
                        ->setType(ApplicationCommandOptionTypes::STRING)
                        ->setName('says')
                        ->setDescription('Duck can talk too now')
                ),
            function (FiredCommand $command) {
                $command->createInteractionResponse(
                    InteractionCallbackBuilder::new()
                        ->setType(InteractionCallbackTypes::DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE)
                )->then(static function () {
                    return Duck::fetch();
                })->then(static function (Duck $duck) use ($command) {
                    if ($command->hasOption('says')) {
                        $duck->says($command->getOption('says')->value);
                    }

                    $command->editInteractionResponse(
                        $duck->toWebhook()
                    );
                });
            }
        );
    }
}
