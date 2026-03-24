<?php

namespace App\Application\BizDocs\Services;

/**
 * Wrapper class that allows accessing properties as both properties and methods
 * This enables templates to use both $obj->property and $obj->property() syntax
 * Also supports array access like $obj['property']
 */
class TemplateDataWrapper implements \ArrayAccess
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __call($name, $arguments)
    {
        // Allow calling properties as methods
        return $this->data[$name] ?? null;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __toString()
    {
        // Allow string conversion for empty() checks
        return (string) ($this->data['value'] ?? '');
    }

    // ArrayAccess implementation
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
