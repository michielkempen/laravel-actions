# Laravel Queueable Actions

Actions are a way of structuring your business logic in Laravel. This package adds support to make them queueable and follow their execution in real-time.

## Installation

Add the package to the dependencies of your application

```
composer require michielkempen/laravel-queueable-actions
```

## Usage

```php
<?php

use MichielKempen\LaravelQueueableActions\QueueableAction;

class MyAction
{
    use QueueableAction;

    public function __construct(
        OtherAction $otherAction,
        ServiceFromTheContainer $service
    ) {
        // Constructor arguments can come from the container.
    
        $this->otherAction = $otherAction;
        $this->service = $service;
    }

    public function execute(
        MyModel $model,
        RequestData $requestData
    ) {
        // The business logic goes here, this can be executed in an async job.
    }
}
```

```php
<?php

class MyController
{
    public function store(
        MyRequest $request,
        MyModel $model,
        MyAction $action
    ) {
        $requestData = RequestData::fromRequest($myRequest);
        
        // Execute the action on the queue:
        $action->onQueue()->execute($model, $requestData);
        
        // Or right now:
        $action->execute($model, $requestData);
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
