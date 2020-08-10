<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\InputData;
use Rhino\InputData\MutationException;

class ArrayAccessTest extends \PHPUnit\Framework\TestCase
{
    public function testIterate(): void
    {
        $inputData = new InputData([1, 2, 3]);
        foreach ($inputData as $key => $value) {
            $this->assertInstanceOf(InputData::class, $key);
            $this->assertInstanceOf(InputData::class, $value);
            $this->assertSame($key->int() + 1, $value->int());
        }
    }

    public function testOffset(): void
    {
        $inputData = new InputData([1, 2, 3]);
        $this->assertInstanceOf(InputData::class, $inputData[0]);
        $this->assertSame(1, $inputData[0]->int());
        $this->assertTrue(isset($inputData[0]));
    }

    public function testSet(): void
    {
        $inputData = new InputData([1, 2, 3]);
        $this->expectException(MutationException::class);
        $inputData[0] = 4;
    }

    public function testUnset(): void
    {
        $inputData = new InputData([1, 2, 3]);
        $this->expectException(MutationException::class);
        unset($inputData[0]);
    }

}
