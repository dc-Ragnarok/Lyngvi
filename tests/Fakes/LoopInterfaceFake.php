<?php

declare(strict_types=1);

namespace Tests\Ragnarok\Lyngvi\Fakes;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class LoopInterfaceFake implements LoopInterface
{
    private $timers = [];

    public function addReadStream($stream, $listener)
    {
    }

    public function addWriteStream($stream, $listener)
    {
    }

    public function removeReadStream($stream)
    {
    }

    public function removeWriteStream($stream)
    {
    }

    public function addTimer($interval, $callback)
    {
        $this->timers[] = $callback;
    }

    public function runTimers()
    {
        $timers = $this->timers;
        $this->timers = [];

        foreach ($timers as $timer) {
            $timer();
        }

    }

    public function addPeriodicTimer($interval, $callback)
    {
    }

    public function cancelTimer(TimerInterface $timer)
    {
    }

    public function futureTick($listener)
    {
    }

    public function addSignal($signal, $listener)
    {
    }

    public function removeSignal($signal, $listener)
    {
    }

    public function run()
    {
    }

    public function stop()
    {
    }
}
