<?php

namespace Exan\StabilityBot;

use ByteUnits\Metric;
use Carbon\Carbon;
use Exan\Dhp\Const\Events;
use Exan\Dhp\Discord;
use Exan\Dhp\Parts\Message;
use Exan\Dhp\Rest\Helpers\Channel\EmbedBuilder;
use Exan\Dhp\Rest\Helpers\Channel\MessageBuilder;
use Exan\Dhp\Websocket\Events\MessageCreate;
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
            $token, [], $logger
        );

        $this->startTime = new Carbon();
    }

    public function register()
    {
        $this->discord->events->on(Events::MESSAGE_CREATE, function (MessageCreate $messageCreate) {
            if (!isset($messageCreate->content)) {
                return;
            }

            if ($messageCreate->content === '!report') {
                $this->report($messageCreate->channel_id);
            }
        });
    }

    public function report(string $channelId)
    {
        $message = (new MessageBuilder())
            ->addEmbed(
                (new EmbedBuilder)
                    ->addField('Memory usage', $this->getMemoryUsage())
                    ->addField('Version', $this->libraryVersion)
                    ->addField('Uptime', $this->getUpTime())
                    ->addField('PHP Version', phpversion())
                    ->setColor(8397467)
            );

        $this->discord->rest->channel->createMessage($channelId, $message)->then(
            function (Message $message) {
                echo 'Message sent', PHP_EOL;
            },
            function (Throwable $e) {
                echo $e->getMessage(), PHP_EOL;
            }
        );
    }

    public function getMemoryUsage($format = null): string
    {
        return Metric::bytes(memory_get_usage())->format($format);
    }

    public function getUpTime(): string
    {
        $now = new Carbon();

        $difference = $now->diff($this->startTime);

        return $difference->format('%d days, %H:%I:%S');
    }
}
