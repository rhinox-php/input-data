<?php

namespace Rhino\InputData\Tests\Extended;

use Rhino\InputData\InputData;

class ExtendedInputData extends InputData
{
    public function test(?string $name = null, ?bool $default = false): ?bool
    {
        [$data, $name] = $this->extractDataKey($name, $this->_data);
        $result = $this->getValue($data, $name, $default);
        if (!$this->isCastable($result)) {
            return $default;
        }
        return $result === 'test';
    }
}
