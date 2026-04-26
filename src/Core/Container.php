<?php

declare(strict_types=1);

namespace GreyPanel\Core;

use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null;
    }

    public function get(string $id)
    {
        if (array_key_exists($id, $this->instances) && $this->instances[$id] !== null) {
            return $this->instances[$id];
        }

        $concrete = $this->bindings[$id] ?? $id;

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        if (array_key_exists($id, $this->instances)) {
            $this->instances[$id] = $object;
        }

        return $object;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || class_exists($id);
    }

    private function build(string $class): object
    {
        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException("Class {$class} is not instantiable");
        }

        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            return new $class();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if (!$type || $type->isBuiltin()) {
                throw new InvalidArgumentException("Cannot resolve param {$param->getName()} of {$class}");
            }
            $args[] = $this->get($type->getName());
        }
        return $reflection->newInstanceArgs($args);
    }
}
