<?php

declare(strict_types=1);

namespace Tests\Ragnarok\Lyngvi;

use PHPUnit\Framework\TestCase;
use Ragnarok\Fenrir\Gateway\Events\MessageCreate;
use Ragnarok\Fenrir\Parts\User;
use Ragnarok\Lyngvi\SpamDetector;
use Tests\Ragnarok\Lyngvi\Fakes\LoopInterfaceFake;

class SpamDetectorTest extends TestCase
{
    protected readonly SpamDetector $spamDetector;
    protected readonly LoopInterfaceFake $loopInterfaceFake;

    protected function setUp(): void
    {
        $this->loopInterfaceFake = new LoopInterfaceFake();
        $this->spamDetector = new SpamDetector($this->loopInterfaceFake, 3);
    }

    public function test_it_detects_spam_and_emits_event_with_author_info()
    {
        $messages = [
            self::getMessageCreateEvent(
                '::author id::',
                '::guild id::',
                '::channel id::',
                '::message id 1::',
                'https://some-sketchy-site.com'
            ),
            self::getMessageCreateEvent(
                '::author id::',
                '::guild id::',
                '::channel id::',
                '::message id 2::',
                'https://some-sketchy-site.com'
            ),
            self::getMessageCreateEvent(
                '::author id::',
                '::guild id::',
                '::channel id::',
                '::message id 3::',
                'https://some-sketchy-site.com'
            ),
        ];

        $hasDetectedSpam = false;
        $this->spamDetector->on(SpamDetector::SPAM_EVENT, function (string $authorId, array $messageLinks) use (&$hasDetectedSpam) {
            $this->assertEquals('::author id::', $authorId);
            $this->assertEquals([
                'https://discord.com/channels/::guild id::/::channel id::/::message id 1::',
                'https://discord.com/channels/::guild id::/::channel id::/::message id 2::',
                'https://discord.com/channels/::guild id::/::channel id::/::message id 3::',
            ], $messageLinks);

            $hasDetectedSpam = true;
        });

        $this->spamDetector->scanMessage($messages[0]);
        $this->spamDetector->scanMessage($messages[1]);
        $this->spamDetector->scanMessage($messages[2]);

        $this->loopInterfaceFake->runTimers();

        $this->assertTrue($hasDetectedSpam);
    }

    /**
     * @dataProvider scenarioProvider
     */
    public function test_it_detects_spam(array $messages, bool $isSpam, int $messageCount = null)
    {
        /**
         * @var MessageCreate $message
         */
        foreach ($messages as $message) {
            $this->spamDetector->scanMessage($message);
        }

        $hasDetectedSpam = false;
        $this->spamDetector->on(SpamDetector::SPAM_EVENT, function (string $authorId, array $messageLinks) use (&$hasDetectedSpam, $messageCount) {
            $hasDetectedSpam = true;

            $this->assertCount($messageCount, $messageLinks);
        });

        $this->loopInterfaceFake->runTimers();

        $this->assertEquals($isSpam, $hasDetectedSpam);
    }

    public static function scenarioProvider()
    {
        return [
            'User sends link in 2 channels' => [
                'messages' => [
                    self::getMessageCreateEvent(
                        '::author id::',
                        '::guild id::',
                        '::channel id::',
                        '::message id 1::',
                        'https://some-sketchy-site.com'
                    ),
                    self::getMessageCreateEvent(
                        '::author id::',
                        '::guild id::',
                        '::channel id::',
                        '::message id 2::',
                        'https://some-sketchy-site.com'
                    ),
                ],

                'isSpam' => false,
            ],
            'User sends link in 4 channels' => [
                'messages' => array_map(
                    fn (int $i) => self::getMessageCreateEvent(
                        '::author id::',
                        '::guild id::',
                        '::channel id ' . $i . '::',
                        '::message id ' . $i . '::',
                        'hello click https://some-sketchy-site.com to not get scammed'
                    ),
                    range(1, 4)
                ),

                'isSpam' => true,

                'messageCount' => 4,
            ],
            'User sends 3 non-link messages' => [
                'messages' => array_map(
                    fn (int $i) => self::getMessageCreateEvent(
                        '::author id::',
                        '::guild id::',
                        '::channel id ' . $i . '::',
                        '::message id ' . $i . '::',
                        'Regular old message'
                    ),
                    range(1, 4)
                ),

                'isSpam' => false,
            ],
            'User sends 3 non-link messages and 1 link' => [
                'messages' => [...array_map(
                    fn (int $i) => self::getMessageCreateEvent(
                        '::author id::',
                        '::guild id::',
                        '::channel id::',
                        '::message id ' . $i . '::',
                        'Regular old message'
                    ),
                    range(1, 3)
                ), self::getMessageCreateEvent(
                    '::author id::',
                    '::guild id::',
                    '::channel id::',
                    '::message id link::',
                    'https://some-sketchy-site.com'
                )],

                'isSpam' => false,
            ],
            '3x Discord.gg invite links' => [
                'messages' => array_map(
                    fn (int $i) => self::getMessageCreateEvent(
                        '::author id::',
                        '::guild id::',
                        '::channel id ' . $i . '::',
                        '::message id ' . $i . '::',
                        'hey join this server discord.gg/somethingsketchy it is fun and not a scam!'
                    ),
                    range(1, 3)
                ),

                'isSpam' => true,

                'messageCount' => 3,
            ],
        ];
    }

    public function test_it_has_a_cooldown_after_emitting_spam_for_a_user()
    {
        $messages = [
            self::getMessageCreateEvent(
                '::author id::',
                '::guild id::',
                '::channel id::',
                '::message id 1::',
                'https://some-sketchy-site.com'
            ),
            self::getMessageCreateEvent(
                '::author id::',
                '::guild id::',
                '::channel id::',
                '::message id 2::',
                'https://some-sketchy-site.com'
            ),
            self::getMessageCreateEvent(
                '::author id::',
                '::guild id::',
                '::channel id::',
                '::message id 3::',
                'https://some-sketchy-site.com'
            ),
        ];

        $hasDetectedSpam = false;
        $this->spamDetector->on(SpamDetector::SPAM_EVENT, function (string $authorId, array $messageLinks) use (&$hasDetectedSpam) {
            $hasDetectedSpam = true;
        });

        $this->spamDetector->scanMessage($messages[0]);
        $this->spamDetector->scanMessage($messages[1]);
        $this->spamDetector->scanMessage($messages[2]);

        $this->loopInterfaceFake->runTimers();

        $this->assertTrue($hasDetectedSpam);

        $hasDetectedSpam = false;

        $this->spamDetector->scanMessage($messages[0]);
        $this->spamDetector->scanMessage($messages[1]);
        $this->spamDetector->scanMessage($messages[2]);

        $this->loopInterfaceFake->runTimers(); // Should now clear cooldown, and be able to mark user as spamming again

        $this->assertFalse($hasDetectedSpam); // Should be false as user was under cooldown to prevent double spam mark

        $this->spamDetector->scanMessage($messages[0]);
        $this->spamDetector->scanMessage($messages[1]);
        $this->spamDetector->scanMessage($messages[2]);

        $this->loopInterfaceFake->runTimers(); // Cooldown should have been removed, thus now once again mark as spam

        $this->assertTrue($hasDetectedSpam);
    }

    private static function getMessageCreateEvent(
        string $authorId,
        string $guildId,
        string $channelId,
        string $messageId,
        string $content
    ): MessageCreate {
        $messageCreate = new MessageCreate();

        $messageCreate->author = new User();
        $messageCreate->author->id = $authorId;

        $messageCreate->guild_id = $guildId;
        $messageCreate->channel_id = $channelId;
        $messageCreate->id = $messageId;

        $messageCreate->content = $content;

        return $messageCreate;
    }
}
