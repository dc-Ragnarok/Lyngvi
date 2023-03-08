<?php

declare(strict_types=1);

namespace Ragnarok\Lyngvi;

use Exan\Fenrir\Rest\Helpers\Channel\EmbedBuilder;
use Exan\Fenrir\Rest\Helpers\Webhook\WebhookBuilder;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use React\Promise\ExtendedPromiseInterface;

class Duck
{
    private const BASE_URL = 'https://random-d.uk/api/';

    private function __construct(private string $url)
    {
    }

    public static function fetch(?LoopInterface $loop = null): ExtendedPromiseInterface
    {
        $url = self::BASE_URL . 'random';

        $http = new Browser(loop: $loop);
        return $http->get($url)->then(function (ResponseInterface $response) {
            $body = json_decode((string) $response->getBody());

            return new Duck($body->url);
        });
    }

    public function toWebhook(): WebhookBuilder
    {
        return WebhookBuilder::new()
            ->addEmbed(
                EmbedBuilder::new()->setImage($this->url)
            );
    }
}
