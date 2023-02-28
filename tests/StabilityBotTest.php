<?php

declare(strict_types=1);

namespace Exan\StabilityBot;

use Exan\Fenrir\CommandHandler;
use Exan\Fenrir\Discord;
use Exan\Fenrir\Rest\Helpers\Command\CommandBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use Ragnarok\Lyngvi\StabilityBot;

class StabilityBotTest extends MockeryTestCase
{
    public function testItRegistersCommands()
    {
        $discordMock = Mockery::mock(Discord::class);

        $stabilityBot = new StabilityBot(
            '::token::',
            new NullLogger(),
            '::version::',
            '::dev guild id::'
        );

        $stabilityBot->discord = $discordMock;
        $stabilityBot->discord->command = Mockery::mock(CommandHandler::class);

        $stabilityBot->discord->command->shouldReceive('registerCommand')->with(
            Mockery::on(function ($commandBuilder) {
                if (!$commandBuilder instanceof CommandBuilder) {
                    return false;
                }

                return $commandBuilder->get()['name'] === 'status';
            }),
            Mockery::on(fn ($v) => true)
        )->once();

        $stabilityBot->discord->command->shouldReceive('registerCommand')->with(
            Mockery::on(function ($commandBuilder) {
                if (!$commandBuilder instanceof CommandBuilder) {
                    return false;
                }

                return $commandBuilder->get()['name'] === 'cat';
            }),
            Mockery::on(fn ($v) => true)
        )->once();

        $stabilityBot->register();
    }
}
