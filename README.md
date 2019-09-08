# Laravel Queueable Actions

This package was inspired by [Spatie](https://spatie.be/)'s [Laravel Queueable Action package](https://github.com/spatie/laravel-queueable-action).

As mentioned in the documentation of Spatie's package, actions are a way of structuring your business logic in Laravel. A detailed blogpost discussing the reasoning behind actions and their asynchronous usage can be found via [this link](https://stitcher.io/blog/laravel-queueable-actions).

This package builds on top of the idea of asynchronous actions, and adds a mechanism to track the state of queued actions in real-time.

## Installation

Add the package to the dependencies of your application

```
composer require michielkempen/laravel-queueable-actions
```

The package will automatically register itself.

You can publish the migration with:

```bash
php artisan vendor:publish --provider="MichielKempen\LaravelQueueableActions\QueueableActionsServiceProvider" --tag="migrations"
```

After the migration has been published you can create the `queued_actions` by running the migrations:

```bash
php artisan migrate
```

## Usage

```php
<?php

use MichielKempen\LaravelQueueableActions\QueueableAction;

class MyAction
{
    use QueueableAction;

    public function __construct()
    {
        $this->otherAction = app(OtherAction::class);
        $this->service = app(ServiceFromTheContainer::class);
    }

    public function execute(MyModel $model, RequestData $requestData)
    {
        // The business logic goes here, this can be executed in an async job.
    }
}
```

```php
<?php

class MyController
{
    public function store(MyRequest $request, MyModel $model, MyAction $action)
    {
        $requestData = RequestData::fromRequest($request);
    
        // Execute the action right now
        $action->execute($model, $requestData);
        
        // Execute the action on the queue
        $action->onQueue()->execute($model, $requestData);
        
        // Execute the action on the queue and track its state
        $action->onQueue($model)->execute($model, $requestData);
    }
}
```

## Security

If you discover any security related issues, please email kempenmichiel@gmail.com instead of using the issue tracker.

## Credits

- [Michiel Kempen](https://github.com/michielkempen)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
