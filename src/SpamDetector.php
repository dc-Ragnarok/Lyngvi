<?php

declare(strict_types=1);

namespace Ragnarok\Lyngvi;

use Evenement\EventEmitterTrait;
use Ragnarok\Fenrir\Gateway\Events\MessageCreate;
use React\EventLoop\LoopInterface;

class SpamDetector
{
    use EventEmitterTrait;

    public const SPAM_EVENT = 'spam';

    /** @var array<string, array> */
    private array $potentialSpammers = [];

    /** @var string[] */
    private array $cooldownUserIds = [];

    public function __construct(
        private readonly LoopInterface $loopInterface,
        private readonly int $spamTreshold = 5
    ) {
    }

    public function scanMessage(MessageCreate $messageCreate)
    {
        if (!$messageCreate->content || $messageCreate->content === '') {
            return;
        }

        if ($this->containsLinkOrInvite($messageCreate->content)) {
            $this->markPotentialSpam($messageCreate);
        }
    }

    private function containsLinkOrInvite(string $content)
    {
        return str_contains($content, 'https://')
            || str_contains($content, 'http://')
            || str_contains($content, 'discord.gg/');
    }

    private function markPotentialSpam(MessageCreate $messageCreate)
    {
        $userId = $messageCreate->author->id;

        if (in_array($userId, $this->cooldownUserIds)) {
            return;
        }

        if (isset($this->potentialSpammers[$userId])) {
            $this->potentialSpammers[$userId][] = $this->getMessageLink($messageCreate);

            return;
        }

        $this->potentialSpammers[$userId] = [$this->getMessageLink($messageCreate)];

        $this->loopInterface->addTimer(10, function () use ($userId) {
            if (count($this->potentialSpammers[$userId]) >= $this->spamTreshold) {
                $this->emit(self::SPAM_EVENT, [$userId, $this->potentialSpammers[$userId]]);
                unset($this->potentialSpammers[$userId]);

                $this->cooldown($userId);
            }
        });
    }

    private function getMessageLink(MessageCreate $message): string
    {
        return 'https://discord.com/channels/' . $message->guild_id . '/' . $message->channel_id . '/' . $message->id;
    }

    private function cooldown(string $userId)
    {
        $this->cooldownUserIds[] = $userId;

        $this->loopInterface->addTimer(30, function () use ($userId) {
            $this->cooldownUserIds = array_filter($this->cooldownUserIds, fn ($id) => $id !== $userId);
        });
    }
}
