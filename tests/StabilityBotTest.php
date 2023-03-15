<?php

declare(strict_types=1);

namespace Tests\Ragnarok\Lyngvi;

use Exan\Fenrir\Rest\Helpers\Command\CommandBuilder;
use Fakes\Exan\Fenrir\DiscordFake;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ragnarok\Lyngvi\StabilityBot;

class StabilityBotTest extends TestCase
{
    public function testItRegistersCommands()
    {
        $stabilityBot = new StabilityBot(
            '::token::',
            new NullLogger(),
            '::version::',
            '::dev guild id::'
        );

        $discordFake = DiscordFake::get();
        $stabilityBot->discord = $discordFake;

        $stabilityBot->register();

        $stabilityBot->discord->command
            ->assertHasDynamicCommand(fn (CommandBuilder $command) => $command->getName() === 'status');

        $stabilityBot->discord->command
            ->assertHasDynamicCommand(fn (CommandBuilder $command) => $command->getName() === 'cat');

        $stabilityBot->discord->command
            ->assertHasDynamicCommand(fn (CommandBuilder $command) => $command->getName() === 'duck');
    }
}
