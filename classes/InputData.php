<?php

declare(strict_types=1);

namespace Rhino\InputData;

/**
 * Helper class for dealing with input data (e.g. data sent over HTTP, API responses,
 * deserialized data from storage etc). When accessing items this class automatically
 * checks that it exists and casts the value to the expected type. If it does not exist a default value is returned.
 */
class InputData implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * @var mixed[]|object
     */
    protected $_data;

    /**
     * @param mixed[]|null|string|float|int $_data Input data
     */
    public function __construct($_data = [])
    {
        if ($_data instanceof static) {
            $_data = $_data->_data;
        }
        $this->_data = $_data;
    }

    /**
     * Cast to boolean.
     *
     * @param string $name    The name/key of input item
     * @param bool   $default The default value if the item doesn't exist
     *
     * @return bool
     */
    public function bool(?string $name = null, ?bool $default = false): ?bool
    {
        [$data, $name] = $this->extractDataKey($name, $this->_data);
        $result = $this->getValue($data, $name, $default);
        if (!$this->isCastable($result)) {
            return $default;
        }
        return (bool) $result;
    }

    /**
     * Cast to an integer.
     *
     * @param string $name    The name/key of input item
     * @param int    $default The default value if the item doesn't exist
     *
     * @return int
     */
    public function int(?string $name = null, ?int $default = 0): ?int
    {
        [$data, $name] = $this->extractDataKey($name, $this->_data);
        $result = $this->getValue($data, $name, $default);
        if (!$this->isCastable($result) || !is_numeric($result)) {
            return $default;
        }
        return (int) $result;
    }

    /**
     * Cast to a float.
     *
     * @param string $name    The name/key of input item
     * @param float  $default The default value if the item doesn't exist
     *
     * @return float
     */
    public function decimal(?string $name = null, ?float $default = 0): ?float
    {
        [$data, $name] = $this->extractDataKey($name, $this->_data);
        $result = $this->getValue($data, $name, $default);
        if (!$this->isCastable($result) || !is_numeric($result)) {
            return $default;
        }
        return (float) $result;
    }

    // @todo support big number types

    /**
     * Cast to an string.
     *
     * @param string $name    The name/key of input item
     * @param string|null|mixed $default The default value if the item doesn't exist
     *
     * @return string
     */
    public function string(?string $name = null, ?string $default = ''): ?string
    {
        [$data, $name] = $this->extractDataKey($name, $this->_data);
        $result = $this->getValue($data, $name, $default);
        if (!$this->isCastable($result)) {
            return $default;
        }
        return (string) $result;
    }

    /**
     * Parse a DateTime.
     *
     * @param string $name     The name/key of input item
     * @param string $timezone The timezone identifier to use for the result, if null the default or input is used
     * @param string $default  The default date/time string to use if the item doesn't exist or is invalid
     *
     * @return \DateTimeImmutable
     */
    public function dateTime(?string $name, ?string $timezone = null, ?string $default = null): ?\DateTimeImmutable
    {
        [$data, $name] = $this->extractDataKey($name, $this->_data);
        $value = $this->getValue($data, $name, $default);

        if ($default === null && !$value) {
            return null;
        }

        try {
            if ($timezone) {
                return new \DateTimeImmutable($value ?: $default, new \DateTimezone($timezone));
            } else {
                return new \DateTimeImmutable($value ?: $default);
            }
        } catch (\Exception $exception) {
            if ($timezone) {
                return new \DateTimeImmutable($default, new \DateTimezone($timezone));
            } else {
                return new \DateTimeImmutable($default);
            }
        }
    }

    /**
     * Returns a subarray of input data.
     *
     * @param string $name    The name/key of input subarray
     * @param array  $default The default value if the item doesn't exist or is not an array
     */
    public function arr(?string $name = null, ?array $default = []): InputData
    {
        if ($name === null) {
            $value = $this->_data;
        } else {
            [$data, $name] = $this->extractDataKey($name, $this->_data);
            $value = $this->getValue($data, $name, $default);
        }
        if (is_object($value)) {
            return new static((array) $value);
        }
        if (is_array($value)) {
            return new static($value);
        }
        return new static($default);
    }

    /**
     * Returns a sub-object of input data.
     *
     * @todo consider removing this
     * @param string $name    The name/key of input item
     * @param mixed  $default The default value if the item doesn't exist
     */
    public function object(?string $name = null, $default = null): InputData
    {
        if ($name === null) {
            $value = $this->_data;
        } else {
            [$data, $name] = $this->extractDataKey($name, $this->_data);
            $value = $this->getValue($data, $name, $default);
        }
        if (is_object($value)) {
            return new static($value);
        }
        if (is_array($value)) {
            return new static((object) $value);
        }
        return new static($default);
    }

    /**
     * JSON decode a value from the input data.
     *
     * @param string $name    The name/key of input item
     * @param array  $default The default value if the item doesn't exist
     *
     * @return \Rhino\InputData\InputData
     */
    public function json(?string $name = null, $default = []): InputData
    {
        $value = $this->string($name);
        if (!$value) {
            $value = $default;
        } else {
            $value = json_decode($value, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                $value = $default;
            }
        }
        return new static($value);
    }

    /**
     * Gets the raw value (unwraps the class) of data.
     *
     * @param string $name    The name/key of input item
     * @param mixed  $default The default value if the item doesn't exist
     */
    public function raw(string $name, $default = null)
    {
        [$data, $name] = $this->extractDataKey($name, $this->_data);

        return $this->getValue($data, $name, $default);
    }

    /**
     * Converts dot delimited notations to access sub items.
     *
     * @param string  $name The name/key of input item
     * @param mixed[] $data The default value if the item doesn't exist
     *
     * @return mixed[]
     */
    protected static function extractDataKey(?string $name, $data)
    {
        $parts = explode('.', $name ?: '');
        while (count($parts) > 1) {
            $part = array_shift($parts);
            if (is_array($data)) {
                $data = isset($data[$part]) ? $data[$part] : [];
            } elseif (is_object($data)) {
                $data = isset($data->$part) ? $data->$part : [];
            } else {
                $data = [];
            }
            $name = $parts[0];
        }
        return [
            $data,
            $name,
        ];
    }

    protected function isCastable($value)
    {
        if (is_scalar($value)) {
            return true;
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            return true;
        }
        return false;
    }

    /**
     * @return bool True if the input data is empty
     */
    public function isEmpty()
    {
        return empty($this->_data);
    }

    /**
     * @return bool True if the input data is an array
     */
    public function isArray(): bool
    {
        return is_array($this->_data);
    }

    /**
     * @return mixed[]|null|string|float|int
     */
    public function getData()
    {
        return $this->_data;
    }

    protected static function getValue($data, ?string $name, $default)
    {
        if ($name === null) {
            return $data;
        }
        if (is_array($data)) {
            if (!array_key_exists($name, $data)) {
                return $default;
            }
            return $data[$name];
        }
        if (is_object($data)) {
            if (!isset($data->$name)) {
                return $default;
            }
            return $data->$name;
        }
        return $default;
    }

    public function exists($name)
    {
        [$data, $name] = $this->extractDataKey($name, $this->_data);

        if (is_array($data)) {
            return array_key_exists($name, $data);
        }
        if (is_object($data)) {
            return property_exists($data, $name);
        }
        return false;
    }

    public function __get($name): InputData
    {
        if (is_array($this->_data)) {
            return isset($this->_data[$name]) ? new static($this->_data[$name]) : new static(null);
        }
        return isset($this->_data->$name) ? new static($this->_data->$name) : new static(null);
    }

    public function __set($name, $value)
    {
        throw new MutationException('Cannot set property of non mutable input data');
    }

    public function __isset($name): bool
    {
        if (is_array($this->_data)) {
            return isset($this->_data[$name]);
        }
        return isset($this->_data->$name);
    }

    public function __unset($name)
    {
        throw new MutationException('Cannot unset property of non mutable input data');
    }

    public function __toString(): string
    {
        return $this->string();
    }

    /**
     * @return \Generator<InputData, InputData>
     */
    public function getIterator(): \Generator
    {
        if (is_array($this->_data) || is_object($this->_data)) {
            foreach ($this->_data as $key => $value) {
                yield new static($key) => new static($value);
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new MutationException('Cannot set offset of non mutable input data');
    }

    public function offsetUnset($offset): void
    {
        throw new MutationException('Cannot unset offset of non mutable input data');
    }

    public function count(): int
    {
        if (!$this->_data) {
            return 0;
        }
        if (is_array($this->_data) || $this->_data instanceof \Countable) {
            return count($this->_data);
        }
        return 0;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getData();
    }

    public static function jsonDecode(string $jsonString, bool $assoc = true): static
    {
        $json = json_decode($jsonString, $assoc);
        $errorCode = json_last_error();
        if ($errorCode) {
            $message = json_last_error_msg();
            throw new ParseException('Error decoding JSON #' . $errorCode . ' ' . $message);
        }
        return new static($json);
    }

    public static function tryJsonDecode(string $jsonString, bool $assoc = true): static
    {
        try {
            return static::jsonDecode($jsonString, $assoc);
        } catch (ParseException $exception) {
            return new static(null);
        }
    }

    public function find($callback): InputData
    {
        foreach ($this as $key => $value) {
            if ($callback($value, $key)) {
                return new static($value);
            }
        }
        return new static(null);
    }
}
