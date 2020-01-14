# Laravel Actions

This package was inspired by [Spatie](https://spatie.be/)'s [Laravel Queueable Action package](https://github.com/spatie/laravel-queueable-action).

As mentioned in the documentation of Spatie's package, actions are a way of structuring your business logic in Laravel. A detailed blogpost discussing the reasoning behind actions and their asynchronous usage can be found via [this link](https://stitcher.io/blog/laravel-queueable-actions).

This package builds on top of the idea of asynchronous actions, and adds a mechanism to track the state of queued actions in real-time.

## Installation

Add the package to the dependencies of your application

```
composer require michielkempen/laravel-actions
```

The package will automatically register itself.

You can publish the migration with:

```bash
php artisan vendor:publish --provider="MichielKempen\LaravelActions\ActionsServiceProvider" --tag="migrations"
```

After the migration has been published you can create the `queued_action_chains` and `queued_actions` database tables
by running:

```bash
php artisan migrate
```

## Usage

Synchronous action chain execution:

```php
$uuid = (string) Uuid::generate(4);

(new QueueableActionChain)
    ->addAction(ReturnTheFirstParameterAsOutputAction::class, ['hello', 'world'], "Greetings!", $uuid)
    ->addAction(ReturnTheParametersAsOutputAction::class, ['john', 'doe'])
    ->addAction(ReturnTheParametersAsOutputAction::class, [new ActionOutput($uuid), 'joe'], "Test action output")
    ->withCallback(ReturnStatusCallback::class)
    ->execute();
```

Asynchronous action chain execution:

```php
$uuid = (string) Uuid::generate(4);
$model = TestModel::create();

(new QueueableActionChain)
    ->queue()
    ->onModel($model)
    ->withName("Test action")
    ->addAction(ReturnTheFirstParameterAsOutputAction::class, ['hello', 'world'], "Greetings!", $uuid)
    ->addAction(ReturnTheParametersAsOutputAction::class, ['john', 'doe'])
    ->addAction(ReturnTheParametersAsOutputAction::class, [new ActionOutput($uuid), 'joe'], "Test action output")
    ->withCallback(ReturnStatusCallback::class)
    ->execute();
```

## Security

If you discover any security related issues, please email kempenmichiel@gmail.com instead of using the issue tracker.

## Credits

- [Michiel Kempen](https://github.com/michielkempen)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
