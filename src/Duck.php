<?php

declare(strict_types=1);

namespace Ragnarok\Lyngvi;

use Exan\Fenrir\Command\Helpers\InteractionCallbackBuilder;
use Exan\Fenrir\Enums\Command\InteractionCallbackTypes;
use Exan\Fenrir\Rest\Helpers\Channel\EmbedBuilder;
use Psr\Http\Message\ResponseInterface;
use Ragnarok\Lyngvi\Exceptions\DuckNotFetchedException;
use React\EventLoop\Loop;
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

    public function toInteractionCallback(): InteractionCallbackBuilder
    {
        return InteractionCallbackBuilder::new()
            ->setType(InteractionCallbackTypes::CHANNEL_MESSAGE_WITH_SOURCE)
            ->addEmbed(
                EmbedBuilder::new()->setImage($this->url)
            );
    }
}
