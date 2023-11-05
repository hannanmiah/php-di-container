<?php

use App\Events\LogEvent;
use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Http\Controllers\TestController;
use App\Http\Kernel;
use App\Models\User;
use Legend\Container\Container;
use Legend\Exception\BindingResolutionException;

beforeEach(function () {
    $this->container = new Container();
});

test('it preserves the container instance', function () {
    $containerOne = Container::getInstance();
    $containerTwo = Container::getInstance();

    expect($containerOne)->toBeInstanceOf(Container::class)
        ->and($containerTwo)->toBeInstanceOf(Container::class)
        ->and($containerOne)->toBe($containerTwo)
        ->and($containerOne)->toEqual(Container::getInstance());
});

test('container can bind and resolve a binding', function () {
    $this->container->bind('foo', User::class);

    expect($this->container->get('foo'))->toBeInstanceOf(User::class);
});

test('container can bind and resolve a closure', function () {
    $this->container->bind('foo', fn() => 'bar');

    expect($this->container->get('foo'))->toBe('bar');
});

test('container can bind a closure and resolve an object', function () {
    $this->container->bind('foo', fn() => new User());

    expect($this->container->get('foo'))->toBeInstanceOf(User::class);
});

test('container can resolve a class', function () {
    $value = Container::getInstance()->get(User::class);
    expect($value)->toBeInstanceOf(User::class);
});

test('container throws an error while resolving a non-existent class', function () {
    Container::getInstance()->get('Foo');
})->throws(BindingResolutionException::class);

test('container resolves a class with dependencies', function () {
    $userEvent = Container::getInstance()->get(UserRegistered::class);
    expect($userEvent)->toBeInstanceOf(UserRegistered::class);
});

test('it resolves a class with multiple dependencies', function () {
    $loggedInEvent = Container::getInstance()->get(UserLoggedIn::class);
    expect($loggedInEvent)->toBeInstanceOf(UserLoggedIn::class);
});

test('it resolves a class with its nested dependencies', function () {
    $logEvent = $this->container->get(LogEvent::class);
    expect($logEvent)->toBeInstanceOf(LogEvent::class);
});

test('it throws an error while resolving a primitive dependency', function () {
    $this->container->get(TestController::class);
})->throws(Exception::class);

test('it allows to bind a singleton', function () {
    $this->container->singleton('foo', User::class);

    expect($this->container->get('foo'))->toBeInstanceOf(User::class);
});

test('it allows to bind a singleton with closure', function () {
    $this->container->singleton('user', fn() => new User());

    expect($this->container->get('user'))->toBeInstanceOf(User::class);
});

test('it should bind interface', function () {
    $this->container->bind(\Legend\Contracts\Http\Kernel::class, Kernel::class);
    expect($this->container->get(\Legend\Contracts\Http\Kernel::class))->toBeInstanceOf(Kernel::class);
});



