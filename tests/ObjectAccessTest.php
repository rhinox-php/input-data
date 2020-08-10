<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\InputData;
use Rhino\InputData\MutationException;

class ObjectAccessTest extends \PHPUnit\Framework\TestCase
{
    public function testIterate(): void
    {
        $inputData = new InputData((object) [1, 2, 3]);
        foreach ($inputData as $key => $value) {
            $this->assertInstanceOf(InputData::class, $key);
            $this->assertInstanceOf(InputData::class, $value);
            $this->assertSame($key->int() + 1, $value->int());
        }
    }

    public function testOffset(): void
    {
        $inputData = new InputData((object) ['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertInstanceOf(InputData::class, $inputData->a);
        $this->assertSame(1, $inputData->a->int());
        $this->assertTrue(isset($inputData->a));
    }

    public function testSet(): void
    {
        $inputData = new InputData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->expectException(MutationException::class);
        $inputData->a = 4;
    }

    public function testUnset(): void
    {
        $inputData = new InputData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->expectException(MutationException::class);
        unset($inputData->a);
    }

}
