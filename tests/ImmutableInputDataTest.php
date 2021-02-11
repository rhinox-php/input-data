<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\ImmutableInputData;

class ImmutableInputDataTest extends \PHPUnit\Framework\TestCase
{
    public function testValues(): void
    {
        $inputData = new ImmutableInputData([
            0 => 'a',
            2 => 'b',
            7 => 'c',
        ]);
        $inputData2 = $inputData->values();
        $this->assertSame([
            0 => 'a',
            2 => 'b',
            7 => 'c',
        ], $inputData->getData());
        $this->assertSame(['a', 'b', 'c'], $inputData2->getData());
    }

    public function testMerge(): void
    {
        $inputData = new ImmutableInputData(['a', 'b', 'c']);
        $inputData2 = $inputData->merge(['d', 'e', 'f']);
        $this->assertSame(['a', 'b', 'c'], $inputData->getData());
        $this->assertSame(['a', 'b', 'c', 'd', 'e', 'f'], $inputData2->getData());
    }

    public function testMap(): void
    {
        $inputData = new ImmutableInputData([1, 2, 3]);
        $inputData2 = $inputData->map(fn ($v) => $v->int() * 2);
        $this->assertSame([1, 2, 3], $inputData->getData());
        $this->assertSame([2, 4, 6], $inputData2->getData());
    }

    public function testMapRecursive(): void
    {
        $inputData = new ImmutableInputData([1, [2, 3]]);
        $inputData2 = $inputData->mapRecursive(fn ($v) => $v->int() * 2);
        $this->assertSame([1, [2, 3]], $inputData->getData());
        $this->assertSame([2, [4, 6]], $inputData2->getData());
    }

    public function testFilter(): void
    {
        $inputData = new ImmutableInputData([1, 2, 3, 4]);
        $inputData2 = $inputData->filter(fn ($v) => $v->int() % 2 === 0);
        $this->assertSame([1, 2, 3, 4], $inputData->getData());
        $this->assertSame([1 => 2, 3 => 4], $inputData2->getData());

        $inputData = new ImmutableInputData([0, 1, 'a', null, '']);
        $inputData2 = $inputData->filter();
        $this->assertSame([0, 1, 'a', null, ''], $inputData->getData());
        $this->assertSame([1 => 1, 2 => 'a'], $inputData2->getData());
    }

    public function testExtend(): void
    {
        $object = (object) [
            'customer' => [
                'id' => '123',
            ],
        ];
        $inputData = new ImmutableInputData($object);
        $inputData2 = $inputData->extend([
            'customer' => [
                'name' => 'test',
            ]
        ]);
        $this->assertSame($object, $inputData->getData());
        $this->assertSame([
            'customer' => [
                'id' => '123',
                'name' => 'test',
            ],
        ], $inputData2->getData());

        $inputData = new ImmutableInputData('test');
        $inputData2 = $inputData->extend([
            'customer' => [
                'name' => 'test',
            ]
        ]);
        $this->assertSame('test', $inputData->getData());
        $this->assertSame([
            'customer' => [
                'name' => 'test',
            ],
        ], $inputData2->getData());
    }
}
