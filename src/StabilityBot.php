<?php

namespace Ragnarok\Lyngvi;

use Carbon\Carbon;
use Ragnarok\Fenrir\Bitwise\Bitwise;
use Ragnarok\Fenrir\Discord;
use Ragnarok\Fenrir\Interaction\CommandInteraction;
use Ragnarok\Fenrir\Interaction\Helpers\InteractionCallbackBuilder;
use Psr\Log\LoggerInterface;
use Ragnarok\Fenrir\Command\AllCommandExtension;
use Ragnarok\Fenrir\Command\CommandExtension;
use Ragnarok\Fenrir\Constants\Events;
use Ragnarok\Fenrir\Enums\InteractionCallbackType;
use Ragnarok\Fenrir\Gateway\Events\Ready;

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
            ->withRest();

        $this->discord->gateway->events->once(Events::READY, function (Ready $ready) {
            $commandExtension = new AllCommandExtension($ready->application->id);

            $this->discord->registerExtension($commandExtension);

            $this->registerCommandListeners($commandExtension);
        });

        $this->startTime = new Carbon();
    }

    private function registerCommandListeners(CommandExtension $commandExtension)
    {
        $commandExtension->on(
            'status',
            function (CommandInteraction $command) {
                $report = new Report(
                    $this->libraryVersion,
                    $this->startTime,
                    $this->discord->getDebugInfo()
                );

                $command->createInteractionResponse($report->toInteractionCallback());
            }
        );

        $commandExtension->on(
            'cat',
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

        $commandExtension->on(
            'duck',
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
