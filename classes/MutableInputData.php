<?php

declare(strict_types=1);

namespace Rhino\InputData;

class MutableInputData extends InputData
{
    use MutateData;

    protected function mutateData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __unset($name)
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
