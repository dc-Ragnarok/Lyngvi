<?php

namespace Exan\StabilityBot;

use ByteUnits\Metric;
use Carbon\Carbon;
use Exan\Fenrir\Bitwise\Bitwise;
use Exan\Fenrir\Const\Events;
use Exan\Fenrir\Discord;
use Exan\Fenrir\Enums\Gateway\Intents;
use Exan\Fenrir\FilteredEventEmitter;
use Exan\Fenrir\Parts\Message;
use Exan\Fenrir\Rest\Helpers\Channel\EmbedBuilder;
use Exan\Fenrir\Rest\Helpers\Channel\MessageBuilder;
use Exan\Fenrir\Websocket\Events\MessageCreate;
use Psr\Log\LoggerInterface;
use Throwable;

class StabilityBot
{
    public Discord $discord;
    private Carbon $startTime;

    public function __construct(
        private string $token,
        private LoggerInterface $logger,
        private string $libraryVersion
    ) {
        $this->discord = new Discord(
            $token,
            Bitwise::from(...$this->getIntents()),
        );

        $this->startTime = new Carbon();
    }

    public function register()
    {
        $reportListener = (new FilteredEventEmitter(
            $this->discord->events,
            Events::MESSAGE_CREATE,
            fn (MessageCreate $messageCreate) => $messageCreate->content === '!report'
        ));

        $reportListener->on(Events::MESSAGE_CREATE, function (MessageCreate $messageCreate) {
            $report = new Report($this->libraryVersion, $this->startTime);

            $this->discord->rest->channel->createMessage(
                $messageCreate->channel_id,
                $report->toMessageBuilder()
            );
        });
    }

    public function getIntents(): array
    {
        return [
            Intents::GUILD_MESSAGES,
            Intents::DIRECT_MESSAGES,
            Intents::MESSAGE_CONTENT,
        ];
    }
}
