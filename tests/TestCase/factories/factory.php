<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Log;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use Opis\Closure\SerializableClosure;

$factory->define(QueuedAction::class, function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'chain_id' => function() {
            return factory(QueuedActionChain::class)->create()->id;
        },
        'order' => 1,
        'status' => ActionStatus::PENDING,
        'action' => (new Action(ReturnTheParametersAsOutputAction::class, [], 'send email', ActionStatus::PENDING))->toArray(),
        'callbacks' => [
            new SerializableClosure(function(Action $action) {
                Log::debug($action->getStatus());
            })
        ],
    ];
});

$factory->define(QueuedActionChain::class, function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'model_id' => $faker->uuid,
        'model_type' => 'TestModel',
        'created_at' => now(),
    ];
});