<?php

namespace Rhino\InputData\Tests\Extended;

use Rhino\InputData\MutableInputData;

class ExtendedMutableInputData extends MutableInputData
{
    public function setHex(?string $name, $value): self
    {
        $data = $this->_data;
        $data[$name] = bin2hex($value);
        return $this->mutateData($data);
    }
}
