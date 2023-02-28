<?php

declare(strict_types=1);

namespace Exan\StabilityBot;

use Exan\Fenrir\Command\Helpers\InteractionCallbackBuilder;
use PHPUnit\Framework\TestCase;
use Ragnarok\Lyngvi\Cat;

class CatTest extends TestCase
{
    private function getCatUrlFromBuilder(InteractionCallbackBuilder $builder)
    {
        return $builder->get()['data']['embeds'][0]['image'];
    }

    public function testItDoesNotUseTheSameUrlTwice()
    {
        $cat1 = new Cat();
        $cat2 = new Cat();

        $this->assertNotEquals(
            $this->getCatUrlFromBuilder($cat1->toInteractionCallback()),
            $this->getCatUrlFromBuilder($cat2->toInteractionCallback())
        );
    }

    public function testItCanCreateNewInstance()
    {
        $cat = Cat::new();

        $this->assertInstanceOf(Cat::class, $cat);
    }
}
