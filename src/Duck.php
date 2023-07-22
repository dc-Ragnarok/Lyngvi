<?php

declare(strict_types=1);

namespace Ragnarok\Lyngvi;

use Ragnarok\Fenrir\Rest\Helpers\Channel\EmbedBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Webhook\EditWebhookBuilder;
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

    public function says(string $text): self
    {
        $filename = str_replace('https://random-d.uk/api/', '', $this->url);

        $this->url = 'https://r-duk.gumlet.io/' . $filename . '?' . http_build_query([
            'text' => $text,
            'text_width_pct' => 0.8
        ]);

        return $this;
    }

    public function toWebhook(): EditWebhookBuilder
    {
        return EditWebhookBuilder::new()
            ->addEmbed(
                EmbedBuilder::new()->setImage($this->url)
            );
    }
}
