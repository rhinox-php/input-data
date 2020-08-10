<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\InputData;
use Rhino\InputData\MutableInputData;

class MutableInputDataTest extends \PHPUnit\Framework\TestCase
{
    public function testSet(): void
    {
        $inputData = new MutableInputData(['str' => 'foo']);

        $inputData->set('str', '1');
        $this->assertSame('1', $inputData->string('str'));

        $inputData->str = '2';
        $this->assertSame('2', $inputData->string('str'));

        $inputData['str'] = '3';
        $this->assertSame('3', $inputData->string('str'));

        $inputData = new MutableInputData((object) ['str' => 'foo']);
        $inputData->set('str', '4');
        $this->assertSame('4', $inputData->string('str'));

        $inputData = new MutableInputData(null);
        $inputData->set('str', new InputData('5'));
        $this->assertSame('5', $inputData->string('str'));
    }

    public function testUnset(): void
    {
        $inputData = new MutableInputData(['str' => 'foo']);
        $inputData->unset('str');
        $this->assertNull($inputData->raw('str'));

        $inputData = new MutableInputData(['str' => 'foo']);
        unset($inputData->str);
        $this->assertNull($inputData->raw('str'));

        $inputData = new MutableInputData(['str' => 'foo']);
        unset($inputData['str']);
        $this->assertNull($inputData->raw('str'));

        $inputData = new MutableInputData((object) ['str' => 'foo']);
        $inputData->unset('str');
        $this->assertNull($inputData->raw('str'));
    }
}
