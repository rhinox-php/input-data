<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\InputData;
use Rhino\InputData\MutationException;

class ExtendTest extends \PHPUnit\Framework\TestCase
{
    public function testExtendInputData(): void
    {
        $inputData = new Extended\ExtendedInputData([
            'key1' => 'test',
            'key2' => 'not test',
        ]);
        $this->assertSame(true, $inputData->test('key1'));
        $this->assertSame(false, $inputData->test('key2'));
    }

    public function testExtendMutableInputData(): void
    {
        $inputData = new Extended\ExtendedMutableInputData([
            'key1' => 'test',
        ]);
        $inputData->setHex('key1', 'foo');
        $this->assertSame('666f6f', $inputData->string('key1'));
    }

    public function testExtendImmutableInputData(): void
    {
        $inputData = new Extended\ExtendedImmutableInputData([
            'key1' => 'test',
        ]);
        $inputData2 = $inputData->setHex('key1', 'foo');
        $this->assertSame('test', $inputData->string('key1'));
        $this->assertSame('666f6f', $inputData2->string('key1'));
    }
}
