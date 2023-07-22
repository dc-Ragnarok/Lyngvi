<?php

namespace Ragnarok\Lyngvi;

use Carbon\Carbon;
use Ragnarok\Fenrir\Bitwise\Bitwise;
use Ragnarok\Fenrir\Discord;
use Ragnarok\Fenrir\Interaction\CommandInteraction;
use Ragnarok\Fenrir\Interaction\Helpers\InteractionCallbackBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Command\CommandBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Command\CommandOptionBuilder;
use Psr\Log\LoggerInterface;
use Ragnarok\Fenrir\Enums\ApplicationCommandOptionType;
use Ragnarok\Fenrir\Enums\ApplicationCommandTypes;
use Ragnarok\Fenrir\Enums\InteractionCallbackType;
use Ragnarok\Fenrir\InteractionHandler;

class StabilityBot
{
    public InteractionHandler $interactionHandler;
    public Discord $discord;
    private Carbon $startTime;

    public function __construct(
        private string $token,
        private LoggerInterface $logger,
        private string $libraryVersion,
        private ?string $devGuild = null
    ) {
        $this->interactionHandler = new InteractionHandler($this->devGuild);

        $this->discord = (new Discord(
            $token,
            $logger
        ))
            ->withGateway(new Bitwise())
            ->withRest();

        $this->discord->registerExtension($this->interactionHandler);

        $this->startTime = new Carbon();
    }

    public function register()
    {
        $this->interactionHandler->registerCommand(
            CommandBuilder::new()
                ->setName('status')
                ->setDescription('Generate a status report')
                ->setType(ApplicationCommandTypes::CHAT_INPUT),
            function (CommandInteraction $command) {
                $report = new Report(
                    $this->libraryVersion,
                    $this->startTime
                );

                $command->createInteractionResponse($report->toInteractionCallback());
            }
        );

        $this->interactionHandler->registerCommand(
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
            function (CommandInteraction $command) {
                $command->createInteractionResponse(
                    InteractionCallbackBuilder::new()
                        ->setType(InteractionCallbackType::DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE)
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

        $this->interactionHandler->registerCommand(
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
            function (CommandInteraction $command) {
                $command->createInteractionResponse(
                    InteractionCallbackBuilder::new()
                        ->setType(InteractionCallbackType::DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE)
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
