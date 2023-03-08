<?php

declare(strict_types=1);

namespace Ragnarok\Lyngvi;

use Exan\Fenrir\Command\Helpers\InteractionCallbackBuilder;
use Exan\Fenrir\Enums\Command\InteractionCallbackTypes;
use Exan\Fenrir\Rest\Helpers\Channel\EmbedBuilder;
use Exan\Fenrir\Rest\Helpers\Webhook\WebhookBuilder;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use React\Promise\ExtendedPromiseInterface;

class Cat
{
    private const BASE_URL = 'https://cataas.com/';

    private function __construct(private string $url)
    {
    }

    /**
     * @return ExtendedPromiseInterface<\Ragnarok\Lyngvi\Cat>
     */
    public static function fetch(?LoopInterface $loop = null): ExtendedPromiseInterface
    {
        $url = self::BASE_URL . 'cat?json=true';

        $http = new Browser(loop: $loop);

        return $http->get($url)->then(function (ResponseInterface $response) {
            $body = json_decode((string) $response->getBody());

            return new Cat(self::BASE_URL . $body->url);
        });
    }

    public function says(string $text): self
    {
        $this->url .= '/says/' . $this->percentEncode($text);

        return $this;
    }

    private function percentEncode($plain): string
    {
        $res = array_map(
            fn (string $char) =>
                !ctype_alnum($char) && !in_array($char, ['-', '_', '.', '~'])
                    ? '%' . strtoupper(dechex(ord($char)))
                    : $char,
            str_split($plain)
        );

        return implode('', $res);
    }

    public function toWebhook(): WebhookBuilder
    {
        return WebhookBuilder::new()
            ->addEmbed(
                EmbedBuilder::new()->setImage($this->url)
            );
    }
}
