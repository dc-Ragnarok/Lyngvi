<?php

declare(strict_types=1);

namespace Ragnarok\Lyngvi;

use Exan\Fenrir\Command\Helpers\InteractionCallbackBuilder;
use Exan\Fenrir\Enums\Command\InteractionCallbackTypes;
use Exan\Fenrir\Rest\Helpers\Channel\EmbedBuilder;

class Cat
{
    private const BASE_URL = 'https://cataas.com/';

    private string $says;

    public static function new(): static
    {
        return new static();
    }

    public function says(string $text): self
    {
        $this->says = $text;

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

    private function getUrl(): string
    {
        $url = self::BASE_URL . 'cat';

        if (isset($this->says)) {
            $url .= '/says/' . $this->percentEncode($this->says);
        }

        /** Discord caches images by URL, prevented by query */
        return $url . '?' . http_build_query(['no-cache' => rand(10000, 99999)]);
    }

    public function toInteractionCallback(): InteractionCallbackBuilder
    {
        return InteractionCallbackBuilder::new()
            ->setType(InteractionCallbackTypes::CHANNEL_MESSAGE_WITH_SOURCE)
            ->addEmbed(
                EmbedBuilder::new()->setImage($this->getUrl())
            );
    }
}
