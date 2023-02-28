<?php

declare(strict_types=1);

namespace Exan\StabilityBot;

use DateInterval;
use Exan\Fenrir\Enums\Command\InteractionCallbackTypes;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ragnarok\Lyngvi\Report;

class ReportTest extends TestCase
{
    public function testToInteractionCallback()
    {
        $carbonMock = Mockery::mock('overload:Carbon\Carbon');

        $differenceMock = Mockery::mock(DateInterval::class);
        $differenceMock->shouldReceive('format')->andReturn('::formatted uptime::');

        $carbonMock->shouldReceive('diff')->andReturn(
            $differenceMock
        );

        $startTimeMock = new \Carbon\Carbon();

        $report = new Report(
            '::version::',
            $startTimeMock
        );

        $interactionCallback = $report->toInteractionCallback();

        $embedData = $interactionCallback->get()['data']['embeds'][0]['fields'];

        $this->assertContains(
            [
                'name' => 'Fenrir version',
                'value' => '::version::',
                'inline' => false,
            ],
            $embedData
        );

        $this->assertContains(
            [
                'name' => 'PHP version',
                'value' => phpversion(),
                'inline' => false,
            ],
            $embedData
        );

        $this->assertContains(
            [
                'name' => 'Uptime',
                'value' => '::formatted uptime::',
                'inline' => false,
            ],
            $embedData
        );

        $this->assertEquals(
            InteractionCallbackTypes::CHANNEL_MESSAGE_WITH_SOURCE->value,
            $interactionCallback->get()['type']
        );
    }
}
