<?php

declare(strict_types=1);

namespace GreyPanel\Core;

use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionNamedType;

class Container implements ContainerInterface
{
    /** @var array<string, class-string|Closure> */
    private array $bindings = [];
    /** @var array<string, object|null> */
    private array $instances = [];
    private static int $depth = 0;

    public function bind(string $abstract, string|Closure|null $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    public function singleton(string $abstract, string|Closure|null $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null;
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id): object
    {
        if (array_key_exists($id, $this->instances) && $this->instances[$id] !== null) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $concrete = $this->bindings[$id];
            $object = $concrete instanceof Closure ? $concrete($this) : $this->build($concrete);
        } elseif (class_exists($id)) {
            $object = $this->build($id);
        } else {
            throw new \InvalidArgumentException("Binding not found for '{$id}'. Add it to config/services.php.");
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

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function build(string $class): object
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Class {$class} does not exist");
        }

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
            if (!$type || ($type instanceof ReflectionNamedType && $type->isBuiltin())) {
                if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                    continue;
                }
                throw new InvalidArgumentException("Cannot resolve param {$param->getName()} of {$class}");
            }

            if ($type instanceof ReflectionNamedType) {
                $args[] = $this->get($type->getName());
            } else {
                throw new InvalidArgumentException("Cannot resolve non-named type for param {$param->getName()} of {$class}");
            }
        }
        return $reflection->newInstanceArgs($args);
    }

    public function autowire(string $baseNamespace, string $baseDir): void
    {
        $directory = new RecursiveDirectoryIterator($baseDir);
        $iterator = new RecursiveIteratorIterator($directory);
        $phpFiles = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($phpFiles as $file) {
            if (!isset($file[0]) || !is_string($file[0])) {
                continue;
            }
            $filePath = $file[0];
            $relativePath = str_replace([$baseDir . '/', '/', '.php'], ['', '\\', ''], $filePath);
            $className = $baseNamespace . '\\' . $relativePath;

            if (!class_exists($className)) {
                continue;
            }

            if (!isset($this->bindings[$className])) {
                $this->bind($className);
            }
            $ref = new ReflectionClass($className);
            foreach ($ref->getInterfaces() as $interface) {
                $interfaceName = $interface->getName();

                if (!str_starts_with($interfaceName, 'GreyPanel\\')) {
                    continue;
                }

                if (!isset($this->bindings[$interfaceName])) {
                    $this->bind($interfaceName, $className);
                }
            }
        }
    }
}
