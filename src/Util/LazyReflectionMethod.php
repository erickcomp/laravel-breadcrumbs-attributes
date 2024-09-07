<?php

namespace ErickComp\BreadcrumbAttributes\Util;

class LazyReflectionMethod implements LazyReflectionMethodInterface
{
    public readonly string $class;
    public readonly string $method;
    private \ReflectionMethod $reflMethod;

    public function __construct(string $class, string $method)
    {
        if (!\str_starts_with($class, '\\')) {
            $class = "\\$class";
        }

        $this->class = $class;
        $this->method = $method;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function __serialize()
    {
        return [
            'class' => $this->class,
            'method' => $this->method
        ];
    }

    public function __unserialize(array $data)
    {
        $this->__construct($data['class'], $data['method']);
    }

    public function isInitialized(): bool
    {
        return isset($this->reflMethod);
    }

    public function get(): \ReflectionMethod
    {
        if (!$this->isInitialized()) {
            $this->reflMethod = new \ReflectionMethod($this->class, $this->method);
        }

        return $this->reflMethod;
    }

    //public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod): static
    //{
    //    $ret = new static($reflectionMethod->class, $reflectionMethod->name);
    //    $ret->reflMethod = $reflectionMethod;
    //
    //    return $ret;
    //}
}
