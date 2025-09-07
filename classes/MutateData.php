<?php

declare(strict_types=1);

namespace Rhino\InputData;

trait MutateData
{
    abstract protected function mutateData($data);

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

    /**
     * Removes elements from data if the callback doesn't return a truthy value. If no callback is supplied it check if the value is truthy.
     */
    public function filter(?callable $callback = null): self
    {
        if (!$callback) {
            $callback = fn ($value): bool => $value->_data != null;
        }
        $result = [];
        foreach ($this as $key => $value) {
            if ($callback($value, $key)) {
                $result[$key->_data] = $value->_data;
            }
        }
        return $this->mutateData($result);
    }

    public function filterRecursive(?callable $callback = null): self
    {
        if (!$callback) {
            $callback = fn ($value): bool => $value->_data != null;
        }
        $result = [];
        foreach ($this as $key => $value) {
            if (is_iterable($value->_data)) {
                $result[$key->_data] = (new static($value))->filterRecursive($callback)->_data;
            } else {
                if ($callback($value, $key)) {
                    $result[$key->_data] = $value->_data;
                }
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
    }

    public function set(string $name, $value): InputData
    {
        if ($value instanceof InputData) {
            $value = $value->_data;
        }

        $data = $this->_data;
        $d = &$data;

        foreach (explode('.', $name) as $part) {
            if (is_object($d)) {
                if (!isset($d->$part)) {
                    $d->$part = [];
                }
                $d = &$d->$part;
            } else {
                if (!isset($d[$part]) || !is_array($d[$part])) {
                    $d[$part] = [];
                }
                $d = &$d[$part];
            }
        }

        $d = $value;
        return $this->mutateData($data);
    }

    public function unset($name): self
    {
        $data = $this->_data;
        $d = &$data;
        $parts  = explode('.', $name);
        $finalPart = count($parts) - 1;
        foreach ($parts as $i => $part) {
            if (is_object($d)) {
                if (!isset($d->$part)) {
                    break;
                }
                if ($i === $finalPart) {
                    unset($d->$part);
                    break;
                }
                $d = &$d->$part;
            } elseif (is_array($d)) {
                if (!isset($d[$part])) {
                    break;
                }
                if ($i === $finalPart) {
                    unset($d[$part]);
                    break;
                }
                $d = &$d[$part];
            } else {
                break;
            }
        }
        return $this->mutateData($data);
    }
}
