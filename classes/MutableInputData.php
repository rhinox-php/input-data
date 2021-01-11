<?php

namespace Rhino\InputData;

class MutableInputData extends InputData
{

    public function extend(array $data): MutableInputData
    {
        $newData = [];
        if (is_object($this->_data)) {
            $this->_data = (array) $this->_data;
        }
        if (!is_array($this->_data)) {
            $this->_data = [];
        }
        foreach ($this->_data as $key => $type) {
            $newData[$key] = $this->_data[$key];
        }
        $newData = array_replace_recursive($newData, $data);
        return new static($newData);
    }

    public function filter(?callable $callback = null): MutableInputData
    {
        if (!$callback) {
            $callback = fn($value) => $value->_data != null;
        }
        $result = [];
        foreach ($this as $key => $value) {
            if ($callback($value, $key)) {
                $result[$key->_data] = $value->_data;
            }
        }
        return new static($result);
    }

    public function map(callable $callback): MutableInputData
    {
        $result = [];
        foreach ($this as $key => $value) {
            $result[$key->_data] = $callback($value, $key);
        }
        return new static($result);
    }

    public function mapRecursive(callable $callback): MutableInputData
    {
        $result = [];
        foreach ($this as $key => $value) {
            if (is_iterable($value->_data)) {
                $result[$key->_data] = (new static($value))->mapRecursive($callback)->_data;
            } else {
                $result[$key->_data] = $callback($value, $key);
            }
        }
        return new static($result);
    }

    public function merge($data): MutableInputData
    {
        $array = (new static($data))->arr()->getData();
        return new static(array_merge($this->arr()->getData(), $array));
    }

    public function values(): MutableInputData
    {
        return new static(array_values($this->arr()->getData()));
    }

    public function set(string $name, $value): MutableInputData
    {
        if ($value instanceof InputData) {
            $value = $value->_data;
        }
        if (is_object($this->_data)) {
            $this->_data->$name = $value;
        } elseif (!is_array($this->_data)) {
            $this->_data = [];
            $this->_data[$name] = $value;
        } else {
            $this->_data[$name] = $value;
        }
        return $this;
    }

    function unset($name): MutableInputData
    {
        if (is_array($this->_data)) {
            unset($this->_data[$name]);
        } else {
            unset($this->_data->$name);
        }
        return $this;
    }

    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    public function __unset(string $name)
    {
        $this->unset($name);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }
}
