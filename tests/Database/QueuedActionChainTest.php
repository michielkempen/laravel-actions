<?php

namespace MichielKempen\LaravelActions\Tests\Database;

use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;

class QueuedActionChainTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_attributes_from_the_database()
    {
        /** @var QueuedActionChain $queuedActionChain */
        $queuedActionChain = factory(QueuedActionChain::class)->create();

        $queuedActionA = factory(QueuedAction::class)->create([
            'chain_id' => $queuedActionChain->getId(),
            'order' => 3,
        ]);

        $queuedActionB = factory(QueuedAction::class)->create([
            'chain_id' => $queuedActionChain->getId(),
            'order' => 1
        ]);

        factory(QueuedAction::class)->create([
            'order' => 1
        ]);

        $queuedActionD = factory(QueuedAction::class)->create([
            'chain_id' => $queuedActionChain->getId(),
            'order' => 2
        ]);

        $actions = $queuedActionChain->getActions();
        $this->assertInstanceOf(Collection::class, $actions);
        $this->assertEquals(3, $actions->count());
        $this->assertEquals($queuedActionB->getId(), $actions->get(0)->getId());
        $this->assertEquals($queuedActionD->getId(), $actions->get(1)->getId());
        $this->assertEquals($queuedActionA->getId(), $actions->get(2)->getId());
    }
}