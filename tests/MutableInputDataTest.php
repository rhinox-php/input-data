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

        $inputData = new MutableInputData(['str' => ['foo' => 'bar']]);
        $inputData->set('str.foo', 'baz');
        $this->assertSame('baz', $inputData->raw('str.foo'));
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

        $inputData = new MutableInputData(['str' => ['foo' => 'bar']]);
        $inputData->unset('str.foo');
        $this->assertNull($inputData->raw('str.foo'));

        $inputData = new MutableInputData(['str' => 'foo']);
        $inputData->unset('foo');
        $this->assertNotNull($inputData->raw('str'));
    }

    public function testValues(): void
    {
        $inputData = new MutableInputData([
            0 => 'a',
            2 => 'b',
            7 => 'c',
        ]);
        $inputData->values();
        $this->assertSame(['a', 'b', 'c'], $inputData->getData());
    }

    public function testMerge(): void
    {
        $inputData = new MutableInputData(['a', 'b', 'c']);
        $inputData->merge(['d', 'e', 'f']);
        $this->assertSame(['a', 'b', 'c', 'd', 'e', 'f'], $inputData->getData());
    }

    public function testMap(): void
    {
        $inputData = new MutableInputData([1, 2, 3]);
        $inputData->map(fn ($v) => $v->int() * 2);
        $this->assertSame([2, 4, 6], $inputData->getData());
    }

    public function testMapRecursive(): void
    {
        $inputData = new MutableInputData([1, [2, 3]]);
        $inputData->mapRecursive(fn ($v) => $v->int() * 2);
        $this->assertSame([2, [4, 6]], $inputData->getData());
    }

    public function testFilter(): void
    {
        $inputData = new MutableInputData([1, 2, 3, 4]);
        $inputData->filter(fn ($v) => $v->int() % 2 === 0);
        $this->assertSame([1 => 2, 3 => 4], $inputData->getData());

        $inputData = new MutableInputData([0, 1, 'a', null, '']);
        $inputData->filter();
        $this->assertSame([1 => 1, 2 => 'a'], $inputData->getData());

        $inputData = new MutableInputData(['a' => 1, 'b' => 2]);
        $inputData->filter(fn ($v, $k) => $k->string() === 'a');
        $this->assertSame(['a' => 1], $inputData->getData());
    }

    public function testFilterRecursive(): void
    {
        $inputData = new MutableInputData([
            'a' => [
                'even' => 2,
                'odd' => 3,
            ],
            'b' => [
                'c' => [
                    'even' => 4,
                    'odd' => 5,
                ],
            ],
        ]);
        $inputData->filterRecursive(fn ($v, $k) => $v->int() % 2 === 0);
        $this->assertSame([
            'a' => [
                'even' => 2,
            ],
            'b' => [
                'c' => [
                    'even' => 4,
                ],
            ],
        ], $inputData->getData());

        $inputData = new MutableInputData([
            ['a' => 1, 'b' => 2],
            ['a' => 3, 'c' => [
                'b' => 4
            ]],
        ]);
        $inputData->filterRecursive(fn ($v, $k) => $k->string() === 'a');
        $this->assertSame([['a' => 1], ['a' => 3, 'c' => []]], $inputData->getData());

        // Test filterRecursive with default callback (null values)
        $inputData = new MutableInputData([
            'valid' => 'data',
            'nested' => [
                'null_value' => null,
                'valid_nested' => 'value',
                'empty_string' => '',
            ],
            'null_top' => null,
        ]);
        $inputData->filterRecursive(); // No callback provided - should filter out null values
        $this->assertSame([
            'valid' => 'data',
            'nested' => [
                'valid_nested' => 'value',
            ],
        ], $inputData->getData());
    }

    public function testExtend(): void
    {
        $inputData = new MutableInputData((object) [
            'customer' => [
                'id' => '123',
            ],
        ]);
        $inputData->extend([
            'customer' => [
                'name' => 'test',
            ],
        ], [
            'customer' => [
                'email' => 'test@example.com',
            ],
        ]);
        $this->assertSame([
            'customer' => [
                'id' => '123',
                'name' => 'test',
                'email' => 'test@example.com',
            ],
        ], $inputData->getData());

        $inputData = new MutableInputData('test');
        $inputData->extend([
            'customer' => [
                'name' => 'test',
            ],
        ]);
        $this->assertSame([
            'customer' => [
                'name' => 'test',
            ],
        ], $inputData->getData());
    }

    public function testUnsetWithComplexObjectPaths(): void
    {
        // Test unset with objects and nested paths that don't exist
        $inputData = new MutableInputData((object) ['a' => (object) ['b' => 'value']]);
        $inputData->unset('a.nonexistent.path');
        $this->assertSame('value', $inputData->raw('a.b'));

        // Test unset with non-array/object data
        $inputData = new MutableInputData(['a' => 'string']);
        $inputData->unset('a.b.c');
        $this->assertSame('string', $inputData->raw('a'));
    }

    public function testSetWithComplexObjectPaths(): void
    {
        // Test setting deep paths in objects
        $inputData = new MutableInputData((object) []);
        $inputData->set('a.b.c', 'value');
        $this->assertSame('value', $inputData->raw('a.b.c'));

        // Test setting with existing object structure
        $inputData = new MutableInputData((object) ['a' => (object) ['existing' => 'data']]);
        $inputData->set('a.b.c', 'value');
        $this->assertSame('data', $inputData->raw('a.existing'));
        $this->assertSame('value', $inputData->raw('a.b.c'));
    }

    public function testNestedSet() {
        $inputData = new MutableInputData(['foo' => 'bar']);
        $inputData->set('foo', new InputData('baz'));
        $this->assertSame(['foo' => 'baz'], $inputData->getData());
    }

    public function testNestedConstructor()
    {
        $inputData = MutableInputData::unwrap([
            'foo' => [
                'bar' => new InputData(['baz' => 123]),
            ]
        ]);
        $this->assertSame(['foo' => ['bar' => ['baz' => 123]]], $inputData->getData());
        $this->assertInstanceOf(MutableInputData::class, $inputData);
    }

    public function testUnwrapNestedInputData(): void
    {
        // The data held by an InputData can contain further InputData instances
        $inputData = MutableInputData::unwrap(new InputData([
            'foo' => new InputData([
                'bar' => new InputData(['baz' => 123]),
            ]),
        ]));
        $this->assertSame(['foo' => ['bar' => ['baz' => 123]]], $inputData->getData());
    }

    public function testUnwrapObject(): void
    {
        $inputData = MutableInputData::unwrap((object) [
            'foo' => new InputData((object) ['bar' => new InputData(123)]),
            'list' => [new InputData('a')],
            'untouched' => (object) ['baz' => 456],
        ]);
        $this->assertEquals((object) [
            'foo' => (object) ['bar' => 123],
            'list' => ['a'],
            'untouched' => (object) ['baz' => 456],
        ], $inputData->getData());
    }

    public function testUnwrapDoesNotModifyTheInput(): void
    {
        $object = (object) ['foo' => (object) ['bar' => new InputData(123)]];
        MutableInputData::unwrap($object);
        $this->assertInstanceOf(InputData::class, $object->foo->bar);
    }

    public function testUnwrapScalar(): void
    {
        $this->assertSame('foo', MutableInputData::unwrap('foo')->getData());
        $this->assertNull(MutableInputData::unwrap(null)->getData());
        $this->assertSame('foo', MutableInputData::unwrap(new InputData('foo'))->getData());
    }

    public function testConstructorOnlyUnwrapsTopLevel(): void
    {
        // Deep unwrapping is opt in via unwrap() so construction stays cheap
        $nested = new InputData('bar');
        $inputData = new MutableInputData(['foo' => $nested]);
        $this->assertSame(['foo' => $nested], $inputData->getData());
    }

    public function testNestedSetDeep()
    {
        $inputData = new MutableInputData(['foo' => 'bar']);
        $inputData->set('foo', MutableInputData::unwrap(['bar' => new InputData(['baz' => 123])]));
        $this->assertSame(['foo' => ['bar' => ['baz' => 123]]], $inputData->getData());
    }

    public function testPipe(): void
    {
        $inputData = new MutableInputData(['foo' => 1, 'bar' => 2]);
        $inputData2 = $inputData->pipe(fn ($o) => ['baz' => $o->int('foo') + $o->int('bar')]);
        $this->assertSame(['baz' => 3], $inputData->getData());
        $this->assertSame($inputData, $inputData2);
    }
}
