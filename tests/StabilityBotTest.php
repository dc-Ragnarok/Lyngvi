<?php

declare(strict_types=1);

namespace Tests\Ragnarok\Lyngvi;

use Ragnarok\Fenrir\Rest\Helpers\Command\CommandBuilder;
use Fakes\Ragnarok\Fenrir\DiscordFake;
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

        $stabilityBot->discord = DiscordFake::get();

        $stabilityBot->register();

        /** @var CommandBuilderFake */
        $commandHandler = $stabilityBot->discord->interaction;

        $commandHandler
            ->assertHasDynamicCommand(fn (CommandBuilder $command) => $command->getName() === 'status');

        $commandHandler
            ->assertHasDynamicCommand(fn (CommandBuilder $command) => $command->getName() === 'cat');

        $commandHandler
            ->assertHasDynamicCommand(fn (CommandBuilder $command) => $command->getName() === 'duck');
    }
}
