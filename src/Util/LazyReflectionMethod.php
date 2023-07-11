<?php

namespace ErickComp\BreadcrumbAttributes\Util;

class LazyReflectionMethod
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

    public function get(): \ReflectionMethod
    {
        if (!isset($this->reflMethod)) {
            $this->reflMethod = new \ReflectionMethod($this->class, $this->method);
        }

        return $this->reflMethod;
    }

    public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod): static
    {
        $ret = new static($reflectionMethod->class, $reflectionMethod->name);
        $ret->reflMethod = $reflectionMethod;

        return $ret;
    }
}