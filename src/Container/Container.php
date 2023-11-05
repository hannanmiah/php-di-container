<?php

namespace Legend\Container;

use Closure;
use Exception;
use Legend\Exception\BindingResolutionException;
use Legend\Exception\ClassNotFound;
use LogicException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;

class Container implements ContainerInterface
{
    protected static Container $instance;
    protected array $bindings = [];

    protected array $instances = [];

    protected array $resolved = [];

    protected array $aliases = [];

    public function get(string $id)
    {
        try {
            return $this->resolve($id);
        } catch (Exception $exception) {
            throw new BindingResolutionException("Binding {$id} not found: " . $exception->getMessage());
        }
    }

    public function resolve(string $abstract)
    {
        if ($this->has($abstract)) {
            if (isset($this->instances[$abstract]) && !is_null($this->instances[$abstract])) {
                return $this->instances[$abstract];
            }

            if ($this->isShared($abstract)) {
                $this->instances[$abstract] = $this->build($abstract);
                $this->resolved[$abstract] = true;
                return $this->instances[$abstract];
            }

            $concreate = $this->getConcreate($abstract);

            $bindingResolved = $concreate instanceof Closure ? $this->resolveClosure($concreate) : $concreate;

            $this->resolved[$abstract] = true;

            if (array_key_exists($abstract, $this->instances)) {
                $this->instances[$abstract] = $bindingResolved;
            }

            return $bindingResolved;
        }

        if (class_exists($abstract)) {
            $resolvedValue = $this->build($abstract);
            $this->resolved[$abstract] = true;
            return $resolvedValue;
        }

        throw new ClassNotFound("Class {$abstract} not found");
    }

    public function has(string $id): bool
    {
        return $this->bound($id);
    }

    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) ||
            isset($this->instances[$abstract]);
    }

    public function isShared($abstract): bool
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]['shared']) &&
                $this->bindings[$abstract]['shared'] === true);
    }

    protected function build(string $abstract)
    {
        try {
            $reflection = new ReflectionClass($abstract);
        } catch (ReflectionException $e) {
            throw new ClassNotFound("Class {$abstract} not found: {$e->getMessage()}");
        }

        if (!$reflection->isInstantiable() && $reflection->isInterface()) {
            if ($this->isAlias($abstract)) {
                return $this->resolve($this->aliases[$abstract]);
            }
            if ($this->has($abstract)) {
                return $this->resolveClosure($this->getConcreate($abstract));
            }
            throw new BindingResolutionException("Interface {$abstract} cannot be resolved");
        }

        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $abstract;
        }

        $parameters = $constructor->getParameters();

        $instances = $this->resolveDependencies($parameters);

        return $reflection->newInstanceArgs($instances);
    }

    public function isAlias($name): bool
    {
        return isset($this->aliases[$name]);
    }

    protected function resolveClosure(Closure $closure)
    {
        try {
            $reflection = new ReflectionFunction($closure);
        } catch (ReflectionException $e) {
            throw new BindingResolutionException("Unable to resolve closure: {$e->getMessage()}");
        }

        return $reflection->invokeArgs([static::getInstance()]);
    }

    public static function getInstance(): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected function getConcreate(string $abstract): Closure|string|null
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    protected function resolveDependencies(array $dependencies): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $result = is_null(Util::getParameterClassName($dependency))
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);

            if ($dependency->isVariadic()) {
                $results = array_merge($results, $result);
            } else {
                $results[] = $result;
            }
        }

        return $results;
    }

    protected function resolvePrimitive(ReflectionParameter $dependency)
    {
        if ($dependency->isDefaultValueAvailable()) {
            return $dependency->getDefaultValue();
        }

        if ($dependency->isVariadic()) {
            return [];
        }

        throw new BindingResolutionException("Unable to resolve primitive dependency {$dependency->getName()} in class {$dependency->getDeclaringClass()->getName()}");
    }

    protected function resolveClass(ReflectionParameter $dependency)
    {
        try {
            return $dependency->isVariadic() ? $this->resolveVariadic($dependency) : $this->resolve(Util::getParameterClassName($dependency));
        } catch (Exception $exception) {
            throw new BindingResolutionException("Unable to resolve dependency {$dependency->getName()} in class {$dependency->getDeclaringClass()->getName()}");
        }
    }

    protected function resolveVariadic(ReflectionParameter $dependency): array
    {
        $className = Util::getParameterClassName($dependency);

        $instances = [];

        foreach ($this->bindings[$className] as $binding) {
            $instances[] = $this->resolve($binding);
        }

        return $instances;
    }

    public function singleton(string $key, mixed $value): void
    {
        $this->bind($key, $value, true);
    }

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        $this->dropStaleInstances($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof Closure) {
            if (!is_string($concrete)) {
                throw new BindingResolutionException("Concrete must be a string or a closure");
            }
            $concrete = $this->getClosure($abstract, $concrete);
        }
        $this->bindings[$abstract] = compact('concrete', 'shared');

        if ($shared) {
            $concrete = $this->getConcreate($abstract);
            $this->instances[$abstract] = $concrete instanceof Closure ? $this->resolveClosure($concrete) : $this->make($concrete);
        }
    }

    protected function dropStaleInstances($abstract): void
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }

    protected function getClosure(string $abstract, string $concrete): Closure
    {
        return function ($container) use ($abstract, $concrete) {
            if ($abstract === $concrete) {
                return $container->build($concrete);
            }
            return $container->resolve($concrete);
        };
    }

    public function make(string $abstract)
    {
        return $this->resolve($abstract);
    }

    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new LogicException("Abstract and alias must be different");
        }
        $this->aliases[$alias] = $abstract;
    }

    protected function rebound(string $key, mixed $value): void
    {

    }
}