<?php

namespace Rhino\InputData;

class MutableInputData extends InputData
{
    use MutateData;

    public function mutateData($data)
    {
        $this->_data = $data;
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
