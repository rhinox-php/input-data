<?php

declare(strict_types=1);

namespace Rhino\InputData;

trait MutateData
{
    protected abstract function mutateData($data);

    public function extend(array ...$newData): self
    {
        $data = $this->_data;
        $result = [];
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (!is_array($data)) {
            $data = [];
        }
        $result = array_replace_recursive($result, $data, ...$newData);
        return $this->mutateData($result);
    }

    public function filter(?callable $callback = null): self
    {
        if (!$callback) {
            $callback = fn ($value) => $value->_data != null;
        }
        $result = [];
        foreach ($this as $key => $value) {
            if ($callback($value, $key)) {
                $result[$key->_data] = $value->_data;
            }
        }
        return $this->mutateData($result);
    }

    public function map(callable $callback): self
    {
        $result = [];
        foreach ($this as $key => $value) {
            $result[$key->_data] = $callback($value, $key);
        }
        return $this->mutateData($result);
    }

    public function mapRecursive(callable $callback): self
    {
        $result = [];
        foreach ($this as $key => $value) {
            if (is_iterable($value->_data)) {
                $result[$key->_data] = (new static($value))->mapRecursive($callback)->_data;
            } else {
                $result[$key->_data] = $callback($value, $key);
            }
        }
        return $this->mutateData($result);
    }

    public function merge($data): self
    {
        $array = (new static($data))->arr()->getData();
        return $this->mutateData(array_merge($this->arr()->getData(), $array));
    }

    public function values(): self
    {
        return $this->mutateData(array_values($this->arr()->getData()));
        return $this;
    }

    public function set(string $name, $value): self
    {
        $data = $this->_data;
        if ($value instanceof InputData) {
            $value = $value->_data;
        }
        if (is_object($data)) {
            $data->$name = $value;
        } elseif (!is_array($data)) {
            $data = [];
            $data[$name] = $value;
        } else {
            $data[$name] = $value;
        }
        return $this->mutateData($data);
    }

    public function unset($name): self
    {
        $data = $this->_data;
        if (is_array($data)) {
            unset($data[$name]);
        } else {
            unset($data->$name);
        }
        return $this->mutateData($data);
    }
}
