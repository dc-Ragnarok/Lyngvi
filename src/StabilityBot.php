<?php

namespace Ragnarok\Lyngvi;

use Carbon\Carbon;
use Exan\Fenrir\Bitwise\Bitwise;
use Exan\Fenrir\Command\FiredCommand;
use Exan\Fenrir\Discord;
use Exan\Fenrir\Enums\Parts\ApplicationCommandTypes;
use Exan\Fenrir\Rest\Helpers\Command\CommandBuilder;
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
            new Bitwise(),
            $logger
        ))
            ->withGateway()
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

                $command->sendFollowUpMessage($report->toInteractionCallback());
            }
        );

        $this->discord->command->registerCommand(
            CommandBuilder::new()
                ->setName('cat')
                ->setDescription('Cat')
                ->setType(ApplicationCommandTypes::CHAT_INPUT),
            function (FiredCommand $command) {
                $command->sendFollowUpMessage(Cat::new()->toInteractionCallback());
            }
        );
    }
}
