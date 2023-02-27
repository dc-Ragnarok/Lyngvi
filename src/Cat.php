<?php

declare(strict_types=1);

namespace Exan\StabilityBot;

use Exan\Fenrir\Command\Helpers\InteractionCallbackBuilder;
use Exan\Fenrir\Enums\Command\InteractionCallbackTypes;
use Exan\Fenrir\Rest\Helpers\Channel\EmbedBuilder;

class Cat
{
    private const BASE_URL = 'https://cataas.com/';

    public static function new(): static
    {
        return new static();
    }

    private function getUrl(): string
    {
        $url = self::BASE_URL . 'cat';

        /** Discord caches images by URL, prevented by query */
        return $url . '?' . http_build_query(['no-cache' => rand(10000, 99999)]);
    }

    public function toInteractionCallback(): InteractionCallbackBuilder
    {
        $embed = new EmbedBuilder();

        $embed->setImage($this->getUrl());

        return (new InteractionCallbackBuilder())
            ->setType(InteractionCallbackTypes::CHANNEL_MESSAGE_WITH_SOURCE)
            ->addEmbed($embed);
    }
}
