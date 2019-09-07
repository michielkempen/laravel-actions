<?php

use Faker\Generator as Faker;
use MichielKempen\LaravelQueueableActions\Database\QueuedAction;

$factory->define(QueuedAction::class, function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'model_id' => $faker->uuid,
        'model_type' => 'TestModel',
        'name' => 'send email',
        'status' => 'pending',
        'output' => null,
    ];
});