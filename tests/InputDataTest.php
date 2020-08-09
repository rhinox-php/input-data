<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\InputData;

class InputDataTest extends \PHPUnit\Framework\TestCase
{
    public function testString(): void
    {
        $inputData = new InputData([
            'str' => 'foo',
            'int' => 7,
        ]);
        $this->assertSame('foo', $inputData->string('str'));
        $this->assertSame('7', $inputData->string('int'));
        $this->assertSame('', $inputData->string('missing'));
        $this->assertSame('default', $inputData->string('missing', 'default'));

        $inputData = new InputData('foo');
        $this->assertSame('foo', $inputData->string());

        $inputData = new InputData([]);
        $this->assertSame('default', $inputData->string(null, 'default'));

        $inputData = new InputData([
            'wat' => (object) [],
        ]);
        $this->assertSame('default', $inputData->string('wat', 'default'));
    }

    public function testInt(): void
    {
        $inputData = new InputData([
            'str' => 'foo',
            'int' => 7,
        ]);
        $this->assertSame(0, $inputData->int('str'));
        $this->assertSame(7, $inputData->int('int'));
        $this->assertSame(5, $inputData->int('str', 5));

        $inputData = new InputData(8);
        $this->assertSame(8, $inputData->int());

        $inputData = new InputData('8.1');
        $this->assertSame(8, $inputData->int());

        $inputData = new InputData([]);
        $this->assertSame('default', $inputData->int(null, 'default'));

        $inputData = new InputData((object) []);
        $this->assertSame('default', $inputData->int(null, 'default'));
    }

    public function testDecimal(): void
    {
        $inputData = new InputData([
            'str' => 'foo',
            'dec' => 7.1,
        ]);
        $this->assertSame(0, $inputData->decimal('str'));
        $this->assertSame(7.1, $inputData->decimal('dec'));
        $this->assertSame(5, $inputData->decimal('str', 5));

        $inputData = new InputData(8.3);
        $this->assertSame(8.3, $inputData->decimal());

        $inputData = new InputData('9.4');
        $this->assertSame(9.4, $inputData->decimal());

        $inputData = new InputData([]);
        $this->assertSame('default', $inputData->decimal(null, 'default'));

        $inputData = new InputData((object) []);
        $this->assertSame('default', $inputData->decimal(null, 'default'));
    }

    public function testBool(): void
    {
        $inputData = new InputData([
            'str' => 'foo',
            't' => true,
            'f' => false,
        ]);
        $this->assertSame(true, $inputData->bool('str'));
        $this->assertSame(true, $inputData->bool('t'));
        $this->assertSame(false, $inputData->bool('f'));

        $inputData = new InputData(true);
        $this->assertSame(true, $inputData->bool());

        $inputData = new InputData('');
        $this->assertSame(false, $inputData->bool());

        $inputData = new InputData('0');
        $this->assertSame(false, $inputData->bool());
    }
}
