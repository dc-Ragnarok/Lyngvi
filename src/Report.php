<?php

declare(strict_types=1);

namespace Ragnarok\Lyngvi;

use ByteUnits\Metric;
use Carbon\Carbon;
use DateInterval;
use Ragnarok\Fenrir\Enums\Command\InteractionCallbackTypes;
use Ragnarok\Fenrir\Interaction\Helpers\InteractionCallbackBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\EmbedBuilder;

class Report
{
    private int $memory;
    private string $phpVersion;

    public function __construct(
        private readonly string $libraryVersion,
        private readonly Carbon $startTime,
    ) {
        $this->memory = memory_get_usage();
        $this->phpVersion = phpversion();
    }

    public function toInteractionCallback(): InteractionCallbackBuilder
    {
        $embed = new EmbedBuilder();

        foreach ($this->getEmbedFields() as $name => $value) {
            $embed->addField($name, $value);
        }

        return InteractionCallbackBuilder::new()
            ->addEmbed($embed)
            ->setType(InteractionCallbackTypes::CHANNEL_MESSAGE_WITH_SOURCE);
    }

    /**
     * @return array<string, string>
     */
    private function getEmbedFields(): array
    {
        return [
            'Fenrir version' => $this->libraryVersion,
            'PHP version' => $this->phpVersion,
            'Start time' => $this->startTime->longRelativeToNowDiffForHumans(parts: 3),
            'Memory usage' => Metric::bytes($this->memory)->format(),
        ];
    }
}
