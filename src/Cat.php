<?php

declare(strict_types=1);

namespace Ragnarok\Lyngvi;

use Ragnarok\Fenrir\Rest\Helpers\Channel\EmbedBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Webhook\EditWebhookBuilder;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use React\Promise\PromiseInterface;

class Cat
{
    private const BASE_URL = 'https://cataas.com/';

    private function __construct(private string $url)
    {
    }

    /**
     * @return PromiseInterface<\Ragnarok\Lyngvi\Cat>
     */
    public static function fetch(?LoopInterface $loop = null): PromiseInterface
    {
        $url = self::BASE_URL . 'cat';

        $http = new Browser(loop: $loop);

        return $http->get($url, ['Accept' => 'application/json'])->then(function (ResponseInterface $response) {
            $body = json_decode((string) $response->getBody());

            return new Cat(self::BASE_URL . 'cat/' . $body->id);
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

    public function toWebhook(): EditWebhookBuilder
    {
        return EditWebhookBuilder::new()
            ->addEmbed(
                EmbedBuilder::new()->setImage($this->url)
            );
    }
}
