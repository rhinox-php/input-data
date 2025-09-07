<?php

declare(strict_types=1);

namespace Rhino\InputData;

class MutableInputData extends InputData
{
    use MutateData;

    #[\Override]
    protected function mutateData($data)
    {
        $this->_data = $data;
        return $this;
    }

    #[\Override]
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    #[\Override]
    public function __unset($name)
    {
        $this->unset($name);
    }

    #[\Override]
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    #[\Override]
    public function offsetUnset($offset): void
    {
        $this->unset($offset);
    }
}
